<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Models\CvProfile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class ProfileService
{
    /** @return array{body: array<string, mixed>, status: int, replayed: bool} */
    public function create(User $user, array $payload, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $payload = ProfileDocumentValidator::canonicalize($payload);
        $hash = hash('sha256', CanonicalJson::encode($payload)."\n".$route);

        return DB::transaction(function () use ($user, $payload, $idempotencyKey, $hash): array {
            $this->advisoryLock($user);
            $existing = CvIdempotency::existing($user, 'create-profile', $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 201, 'replayed' => true];
            }
            if (CvProfile::query()->where('user_id', $user->getKey())->count() >= 10) {
                throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                    'profiles' => [['code' => 'LIMIT_REACHED', 'message' => 'The profile limit has been reached.']],
                ]);
            }

            $title = (string) ($payload['title'] ?? '');
            $document = ProfileDocument::empty((string) ($payload['personal_information']['full_name'] ?? ''));
            $document['personal_information'] = $payload['personal_information'];
            $profile = new CvProfile([
                'id' => ProfileDocument::id(),
                'user_id' => $user->getKey(),
                'title' => $title,
                'normalized_title' => $this->normalizedTitle($title),
                'revision' => 1,
                'schema_version' => '1.0',
                'document' => $document,
            ]);

            try {
                $profile->save();
            } catch (QueryException $exception) {
                if ($this->isConstraint($exception)) {
                    throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                        'title' => [['code' => 'INVALID', 'message' => 'The title is unavailable.']],
                    ]);
                }
                throw $exception;
            }
            $body = ['data' => ProfilePresenter::data($profile->fresh())];
            CvIdempotency::record($user, 'create-profile', $idempotencyKey, $hash, $body, 201);

            return ['body' => $body, 'status' => 201, 'replayed' => false];
        });
    }

    public function findOwned(User $user, string $id): CvProfile
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }

        $profile = CvProfile::query()->where('id', $id)->where('user_id', $user->getKey())->first();
        if (! $profile instanceof CvProfile) {
            throw $this->notFound();
        }

        return $profile;
    }

    /** @return array{body: array<string, mixed>, status: int, etag: string} */
    public function updatePersonal(User $user, string $id, array $value, string $ifMatch): array
    {
        return $this->updateSection($user, $id, 'personal_information', $value, $ifMatch);
    }

    /** @return array{body: array<string, mixed>, status: int, etag: string} */
    public function updateTitle(User $user, string $id, string $title, string $ifMatch): array
    {
        return DB::transaction(function () use ($user, $id, $title, $ifMatch): array {
            $profile = $this->lockOwned($user, $id);
            $this->assertRevision($profile, $ifMatch);
            $title = ProfileDocumentValidator::canonicalize(['title' => $title])['title'];
            try {
                $profile->forceFill([
                    'title' => $title,
                    'normalized_title' => $this->normalizedTitle($title),
                    'revision' => $profile->revision + 1,
                ])->save();
            } catch (QueryException $exception) {
                if ($this->isConstraint($exception)) {
                    throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                        'title' => [['code' => 'INVALID', 'message' => 'The title is unavailable.']],
                    ]);
                }
                throw $exception;
            }

            return $this->updated($profile->fresh());
        });
    }

    /** @return array{body: array<string, mixed>, status: int, etag: string} */
    public function updateSection(User $user, string $id, string $section, mixed $value, string $ifMatch): array
    {
        return DB::transaction(function () use ($user, $id, $section, $value, $ifMatch): array {
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
            $profile->forceFill(['document' => $document, 'revision' => $profile->revision + 1])->save();

            return $this->updated($profile->fresh());
        });
    }

    /** @return array{body: array<string, mixed>, status: int, etag: string} */
    private function updated(CvProfile $profile): array
    {
        return [
            'body' => ['data' => ProfilePresenter::data($profile)],
            'status' => 200,
            'etag' => '"'.$profile->revision.'"',
        ];
    }

    private function lockOwned(User $user, string $id): CvProfile
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }
        $profile = CvProfile::query()->where('id', $id)->where('user_id', $user->getKey())->lockForUpdate()->first();
        if (! $profile instanceof CvProfile) {
            throw $this->notFound();
        }

        return $profile;
    }

    private function assertRevision(CvProfile $profile, string $ifMatch): void
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

    private function assertExistingItemOwnership(CvProfile $profile, string $section, mixed $value): void
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

    private function advisoryLock(User $user): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::select('SELECT pg_advisory_xact_lock(?)', [(int) $user->getKey()]);
        }
    }

    private function normalizedTitle(string $title): string
    {
        return trim($title);
    }

    private function isConstraint(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23505', '23000', '19'], true);
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
