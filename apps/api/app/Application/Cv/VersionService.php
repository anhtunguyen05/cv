<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Application\Auth\Data\AuthenticatedUser;
use App\Application\Cv\Contracts\IdempotencyStore;
use App\Application\Cv\Contracts\ProfileRepository;
use App\Application\Cv\Contracts\VersionRepository;
use App\Application\Cv\Data\ProfileRecord;
use App\Application\Cv\Data\VersionPage;
use App\Application\Cv\Data\VersionRecord;
use App\Domain\Cv\Policies\ImmutableSnapshotPolicy;
use App\Domain\Shared\ValueObjects\SnapshotHash;
use App\Shared\Application\Contracts\TransactionManager;
use DateTimeImmutable;

final class VersionService
{
    public function __construct(
        private readonly TransactionManager $transactions,
        private readonly ProfileRepository $profiles,
        private readonly VersionRepository $versions,
        private readonly IdempotencyStore $idempotency,
    ) {}

    /** @return array{body: array<string, mixed>, status: int, replayed: bool} */
    public function create(AuthenticatedUser $user, string $profileId, string $name, string $ifMatch, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $payload = ProfileDocumentValidator::canonicalize(['name' => $name]);
        $hash = hash('sha256', CanonicalJson::encode($payload)."\n".$route."\n".$ifMatch);

        return $this->transactions->run(function () use ($user, $profileId, $name, $ifMatch, $idempotencyKey, $hash): array {
            if (! ProfileDocument::isUlid($profileId)) {
                throw $this->notFound();
            }
            $profile = $this->profiles->lockOwned($user->id, $profileId);
            if ($profile === null) {
                throw $this->notFound();
            }
            $existing = CvIdempotency::existing($this->idempotency, $user->id, 'create-version', $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 201, 'replayed' => true];
            }
            $this->assertRevision($profile, $ifMatch);
            $snapshot = [
                'title' => $profile->title,
                ...(is_array($profile->document) ? $profile->document : []),
            ];
            if (! is_string($snapshot['personal_information']['full_name'] ?? null) || trim($snapshot['personal_information']['full_name']) === '') {
                throw new ApiProblem('PROFILE_NOT_VERSIONABLE', 'The Profile cannot be versioned.', 409, [
                    'personal_information.full_name' => [['code' => 'INVALID', 'message' => 'A full name is required.']],
                ]);
            }
            $encodedSnapshot = CanonicalJson::encode($snapshot);
            $snapshotHash = hash('sha256', $encodedSnapshot);
            if (! ImmutableSnapshotPolicy::matchesHash($encodedSnapshot, SnapshotHash::fromString($snapshotHash))) {
                throw new ApiProblem('PROFILE_NOT_VERSIONABLE', 'The Profile snapshot could not be verified.', 409);
            }
            $version = $this->versions->create([
                'id' => ProfileDocument::id(),
                'user_id' => $user->id,
                'source_profile_id' => $profile->id,
                'source_profile_revision' => $profile->revision,
                'name' => (string) ProfileDocumentValidator::canonicalize(['name' => $name])['name'],
                'snapshot_schema_version' => '1.0',
                'snapshot' => $snapshot,
                'snapshot_hash' => $snapshotHash,
                'created_at' => new DateTimeImmutable,
            ]);
            $body = ['data' => VersionPresenter::data($version)];
            CvIdempotency::record($this->idempotency, $user->id, 'create-version', $idempotencyKey, $hash, $body, 201);

            return ['body' => $body, 'status' => 201, 'replayed' => false];
        });
    }

    public function findOwned(AuthenticatedUser $user, string $id): VersionRecord
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }
        $version = $this->versions->findOwned($user->id, $id);
        if ($version === null) {
            throw $this->notFound();
        }

        return $version;
    }

    public function list(AuthenticatedUser $user, int $page, int $perPage, ?string $profileId, array $query = []): VersionPage
    {
        if ($profileId !== null) {
            if (! ProfileDocument::isUlid($profileId) || $this->profiles->findOwned($user->id, $profileId) === null) {
                throw $this->notFound();
            }
        }

        return $this->versions->listOwned($user->id, $page, $perPage, $profileId, $query);
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

    private function notFound(): ApiProblem
    {
        return new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
    }
}
