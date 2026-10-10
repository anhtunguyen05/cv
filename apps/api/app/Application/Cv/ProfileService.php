<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Cv\Contracts\IdempotencyStore;
use App\Application\Cv\Contracts\ProfileRepository;
use App\Application\Cv\Data\ProfileRecord;
use App\Application\Cv\Exceptions\ProfileTitleUnavailable;
use App\Shared\Application\Contracts\AdvisoryLock;
use App\Shared\Application\Contracts\TransactionManager;

final class ProfileService
{
    public function __construct(
        private readonly TransactionManager $transactions,
        private readonly AdvisoryLock $advisoryLocks,
        private readonly ProfileRepository $profiles,
        private readonly IdempotencyStore $idempotency,
    ) {}

    /** @return array{body: array<string, mixed>, status: int, replayed: bool} */
    public function create(AuthenticatedUser $user, array $payload, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $payload = ProfileDocumentValidator::canonicalize($payload);
        $hash = hash('sha256', CanonicalJson::encode($payload)."\n".$route);

        return $this->transactions->run(function () use ($user, $payload, $idempotencyKey, $hash): array {
            $this->advisoryLocks->forUser($user->id);
            $existing = CvIdempotency::existing($this->idempotency, $user->id, 'create-profile', $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 201, 'replayed' => true];
            }
            if ($this->profiles->countOwned($user->id) >= 10) {
                throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                    'profiles' => [['code' => 'LIMIT_REACHED', 'message' => 'The profile limit has been reached.']],
                ]);
            }

            $title = (string) ($payload['title'] ?? '');
            $document = ProfileDocument::empty((string) ($payload['personal_information']['full_name'] ?? ''));
            $document['personal_information'] = $payload['personal_information'];
            try {
                $profile = $this->profiles->create(
                    userId: $user->id,
                    id: ProfileDocument::id(),
                    title: $title,
                    normalizedTitle: $this->normalizedTitle($title),
                    document: $document,
                );
            } catch (ProfileTitleUnavailable) {
                throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                    'title' => [['code' => 'INVALID', 'message' => 'The title is unavailable.']],
                ]);
            }
            $body = ['data' => ProfilePresenter::data($profile)];
            CvIdempotency::record($this->idempotency, $user->id, 'create-profile', $idempotencyKey, $hash, $body, 201);

            return ['body' => $body, 'status' => 201, 'replayed' => false];
        });
    }

    public function findOwned(AuthenticatedUser $user, string $id): ProfileRecord
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }

        $profile = $this->profiles->findOwned($user->id, $id);
        if ($profile === null) {
            throw $this->notFound();
        }

        return $profile;
    }

    /** @return array{body: array<string, mixed>, status: int, etag: string} */
    public function updatePersonal(AuthenticatedUser $user, string $id, array $value, string $ifMatch): array
    {
        return $this->updateSection($user, $id, 'personal_information', $value, $ifMatch);
    }

    /** @return array{body: array<string, mixed>, status: int, etag: string} */
    public function updateTitle(AuthenticatedUser $user, string $id, string $title, string $ifMatch): array
    {
        return $this->transactions->run(function () use ($user, $id, $title, $ifMatch): array {
            $profile = $this->lockOwned($user, $id);
            $this->assertRevision($profile, $ifMatch);
            $title = ProfileDocumentValidator::canonicalize(['title' => $title])['title'];
            try {
                $profile = $this->profiles->update($profile, [
                    'title' => $title,
                    'normalized_title' => $this->normalizedTitle($title),
                    'revision' => $profile->revision + 1,
                ]);
            } catch (ProfileTitleUnavailable) {
                throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                    'title' => [['code' => 'INVALID', 'message' => 'The title is unavailable.']],
                ]);
            }

            return $this->updated($profile);
        });
    }

    /** @return array{body: array<string, mixed>, status: int, etag: string} */
    public function updateSection(AuthenticatedUser $user, string $id, string $section, mixed $value, string $ifMatch): array
    {
        return $this->transactions->run(function () use ($user, $id, $section, $value, $ifMatch): array {
            $profile = $this->lockOwned($user, $id);
            $this->assertRevision($profile, $ifMatch);
            $errors = $section === 'personal_information'
                ? ProfileDocumentValidator::validatePersonal($value)
                : ProfileDocumentValidator::validateSection($section, $value);
            if ($errors !== []) {
                throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, $this->details($errors));
            }
            $document = $profile->document;
            $canonicalValue = ProfileDocumentValidator::canonicalize(is_array($value) ? $value : ['value' => $value]);
            $canonicalValue = $section === 'summary' ? ($value === null ? null : $canonicalValue['value']) : $canonicalValue;
            if ($section === 'personal_information') {
                $canonicalValue = $canonicalValue;
            }
            $this->assertExistingItemOwnership($profile, $section, $canonicalValue);
            $document[$section] = $this->assignIds($section, $canonicalValue);
            $profile = $this->profiles->update($profile, ['document' => $document, 'revision' => $profile->revision + 1]);

            return $this->updated($profile);
        });
    }

    /** @return array{body: array<string, mixed>, status: int, etag: string} */
    private function updated(ProfileRecord $profile): array
    {
        return [
            'body' => ['data' => ProfilePresenter::data($profile)],
            'status' => 200,
            'etag' => '"'.$profile->revision.'"',
        ];
    }

    private function lockOwned(AuthenticatedUser $user, string $id): ProfileRecord
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }
        $profile = $this->profiles->lockOwned($user->id, $id);
        if ($profile === null) {
            throw $this->notFound();
        }

        return $profile;
    }

    private function assertRevision(ProfileRecord $profile, string $ifMatch): void
    {
        if (preg_match('/^"([1-9][0-9]*)"$/', $ifMatch, $matches) !== 1) {
            throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                'if_match' => [['code' => 'INVALID', 'message' => 'The If-Match header must contain one strong revision value.']],
            ]);
        }
        if ((int) $matches[1] !== (int) $profile->revision) {
            throw new ApiProblem('PROFILE_UPDATE_CONFLICT', 'The Profile has changed. Reload it before saving.', 409, [
                'revision' => [['code' => 'STALE', 'message' => 'The supplied revision is stale.']],
            ]);
        }
    }

    private function assertExistingItemOwnership(ProfileRecord $profile, string $section, mixed $value): void
    {
        if ($section === 'personal_information' || $section === 'summary') {
            return;
        }
        $existing = is_array($profile->document[$section] ?? null) ? $profile->document[$section] : [];
        $valid = [];
        foreach ($existing as $item) {
            if (is_array($item) && isset($item['id'])) {
                $valid[(string) $item['id']] = true;
                if ($section === 'skills' && is_array($item['items'] ?? null)) {
                    foreach ($item['items'] as $skill) {
                        if (is_array($skill) && isset($skill['id'])) {
                            $valid[(string) $skill['id']] = true;
                        }
                    }
                }
            }
        }
        foreach (is_array($value) ? $value : [] as $item) {
            if (! is_array($item)) {
                continue;
            }
            foreach ([$item, ...($section === 'skills' && is_array($item['items'] ?? null) ? $item['items'] : [])] as $candidate) {
                if (is_array($candidate) && isset($candidate['id']) && $candidate['id'] !== null && ! isset($valid[(string) $candidate['id']])) {
                    throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                        $section.'.id' => [['code' => 'INVALID', 'message' => 'The item does not belong to this Profile.']],
                    ]);
                }
            }
        }
    }

    private function assignIds(string $section, mixed $value): mixed
    {
        if ($section === 'summary' || $section === 'personal_information') {
            return $value;
        }
        if (! is_array($value)) {
            return $value;
        }
        foreach ($value as $index => $item) {
            if (! is_array($item)) {
                continue;
            }
            $value[$index]['id'] ??= ProfileDocument::id();
            if ($section === 'skills' && is_array($item['items'] ?? null)) {
                foreach ($item['items'] as $skillIndex => $skill) {
                    if (is_array($skill)) {
                        $value[$index]['items'][$skillIndex]['id'] ??= ProfileDocument::id();
                    }
                }
            }
        }

        return $value;
    }

    private function normalizedTitle(string $title): string
    {
        return trim($title);
    }

    private function notFound(): ApiProblem
    {
        return new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
    }

    private function details(array $errors): array
    {
        $details = [];
        foreach ($errors as $field => $messages) {
            $details[$field] = array_map(static fn (string $message): array => ['code' => 'INVALID', 'message' => $message], $messages);
        }

        return $details;
    }
}
