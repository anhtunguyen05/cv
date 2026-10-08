<?php

declare(strict_types=1);

namespace App\Application\Cv;

use App\Models\CvProfile;
use App\Models\CvVersion;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class VersionService
{
    /** @return array{body: array<string, mixed>, status: int, replayed: bool} */
    public function create(User $user, string $profileId, string $name, string $ifMatch, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $payload = ProfileDocumentValidator::canonicalize(['name' => $name]);
        $hash = hash('sha256', CanonicalJson::encode($payload)."\n".$route."\n".$ifMatch);

        return DB::transaction(function () use ($user, $profileId, $name, $ifMatch, $idempotencyKey, $hash): array {
            if (! ProfileDocument::isUlid($profileId)) {
                throw $this->notFound();
            }
            /** @var CvProfile|null $profile */
            $profile = CvProfile::query()->where('id', $profileId)->where('user_id', $user->getKey())->lockForUpdate()->first();
            if (! $profile instanceof CvProfile) {
                throw $this->notFound();
            }
            $existing = CvIdempotency::existing($user, 'create-version', $idempotencyKey, $hash);
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
            $version = new CvVersion([
                'id' => ProfileDocument::id(),
                'user_id' => $user->getKey(),
                'source_profile_id' => $profile->getKey(),
                'source_profile_revision' => $profile->revision,
                'name' => (string) ProfileDocumentValidator::canonicalize(['name' => $name])['name'],
                'snapshot_schema_version' => '1.0',
                'snapshot' => $snapshot,
                'snapshot_hash' => hash('sha256', $encodedSnapshot),
                'created_at' => Carbon::now(),
            ]);
            try {
                $version->save();
            } catch (QueryException $exception) {
                throw $exception;
            }
            $body = ['data' => VersionPresenter::data($version->fresh())];
            CvIdempotency::record($user, 'create-version', $idempotencyKey, $hash, $body, 201);

            return ['body' => $body, 'status' => 201, 'replayed' => false];
        });
    }

    public function findOwned(User $user, string $id): CvVersion
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }
        $version = CvVersion::query()->where('id', $id)->where('user_id', $user->getKey())->first();
        if (! $version instanceof CvVersion) {
            throw $this->notFound();
        }

        return $version;
    }

    public function list(User $user, int $page, int $perPage, ?string $profileId): LengthAwarePaginator
    {
        $query = CvVersion::query()->where('user_id', $user->getKey());
        if ($profileId !== null) {
            if (! ProfileDocument::isUlid($profileId) || ! CvProfile::query()->where('id', $profileId)->where('user_id', $user->getKey())->exists()) {
                throw $this->notFound();
            }
            $query->where('source_profile_id', $profileId);
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, [
                'id', 'user_id', 'source_profile_id', 'source_profile_revision', 'name', 'created_at',
            ], 'page', $page);
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

    private function notFound(): ApiProblem
    {
        return new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
    }
}
