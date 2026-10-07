<?php

declare(strict_types=1);

namespace App\Application\JobFit;

use App\Application\Cv\ApiProblem;
use App\Application\Cv\CanonicalJson;
use App\Application\Cv\CvIdempotency;
use App\Application\Cv\ProfileDocument;
use App\Models\JobDescription;
use App\Models\JobDescriptionRevision;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class JobDescriptionService
{
    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function create(User $user, array $payload, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $payload = JobDescriptionValidator::create($payload);
        $hash = $this->fingerprint($payload, $route);

        return DB::transaction(function () use ($user, $payload, $idempotencyKey, $hash): array {
            $this->advisoryLock($user);
            $existing = CvIdempotency::existing($user, 'create-job-description', $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 201, 'replayed' => true];
            }
            $jobDescription = new JobDescription([
                'id' => ProfileDocument::id(), 'user_id' => $user->getKey(),
                'title' => $payload['role'], 'company' => $payload['company'],
                'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),
            ]);
            $jobDescription->save();
            $revision = new JobDescriptionRevision([
                'id' => ProfileDocument::id(), 'job_description_id' => $jobDescription->getKey(),
                'user_id' => $user->getKey(), 'revision_number' => 1,
                'title' => $payload['role'], 'company' => $payload['company'],
                'raw_text' => $payload['raw_text'], 'created_at' => Carbon::now(),
            ]);
            $revision->save();
            $jobDescription->forceFill(['current_revision_id' => $revision->getKey()])->save();
            $body = ['data' => JobDescriptionPresenter::data($jobDescription->fresh(['currentRevision']))];
            CvIdempotency::record($user, 'create-job-description', $idempotencyKey, $hash, $body, 201);

            return ['body' => $body, 'status' => 201, 'replayed' => false];
        });
    }

    public function findOwnedActive(User $user, string $id): JobDescription
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }
        $jobDescription = $this->ownedQuery($user, $id)->whereNull('deleted_at')->with('currentRevision')->first();
        if (! $jobDescription instanceof JobDescription) {
            throw $this->notFound();
        }

        return $jobDescription;
    }

    /** @return array{body:array<string,mixed>,status:int,etag:string,replayed:bool} */
    public function update(User $user, string $id, array $input, string $ifMatch, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $patch = JobDescriptionValidator::patch($input);
        $hash = $this->fingerprint($patch, $route."\n".$ifMatch);

        return DB::transaction(function () use ($user, $id, $patch, $ifMatch, $idempotencyKey, $hash): array {
            $jobDescription = $this->lockOwned($user, $id);
            $existing = CvIdempotency::existing($user, 'update-job-description', $idempotencyKey, $hash);
            if ($existing['replayed']) {
                $body = $existing['body'] ?? [];
                $currentRevision = $body['data']['current_revision']['id'] ?? '';

                return ['body' => $body, 'status' => $existing['status'] ?? 200, 'etag' => '"'.$currentRevision.'"', 'replayed' => true];
            }
            if ($jobDescription->isDeleted()) {
                throw new ApiProblem('JOB_DESCRIPTION_DELETED', 'The Job Description is deleted and cannot be changed.', 409);
            }
            $this->assertIfMatch($jobDescription, $ifMatch);
            $current = $jobDescription->currentRevision()->firstOrFail();
            $next = [
                'raw_text' => $patch['raw_text'] ?? $current->raw_text,
                'company' => array_key_exists('company', $patch) ? $patch['company'] : $current->company,
                'role' => array_key_exists('role', $patch) ? $patch['role'] : $current->title,
            ];
            if ($next['raw_text'] === $current->raw_text && $next['company'] === $current->company && $next['role'] === $current->title) {
                throw new ApiProblem('JOB_DESCRIPTION_NO_CHANGES', 'The Job Description has no changes to save.', 422);
            }
            $revision = new JobDescriptionRevision([
                'id' => ProfileDocument::id(), 'job_description_id' => $jobDescription->getKey(),
                'user_id' => $user->getKey(), 'revision_number' => $current->revision_number + 1,
                'title' => $next['role'], 'company' => $next['company'], 'raw_text' => $next['raw_text'],
                'created_at' => Carbon::now(),
            ]);
            $revision->save();
            $jobDescription->forceFill([
                'title' => $next['role'], 'company' => $next['company'],
                'current_revision_id' => $revision->getKey(), 'updated_at' => Carbon::now(),
            ])->save();
            $body = ['data' => JobDescriptionPresenter::data($jobDescription->fresh(['currentRevision']))];
            CvIdempotency::record($user, 'update-job-description', $idempotencyKey, $hash, $body, 200);

            return ['body' => $body, 'status' => 200, 'etag' => '"'.$revision->getKey().'"', 'replayed' => false];
        });
    }

    /** @return array{body:array<string,mixed>,status:int,replayed:bool} */
    public function delete(User $user, string $id, string $ifMatch, string $idempotencyKey, string $route): array
    {
        CvIdempotency::validate($idempotencyKey);
        $hash = $this->fingerprint([], $route."\n".$ifMatch);

        return DB::transaction(function () use ($user, $id, $ifMatch, $idempotencyKey, $hash): array {
            $jobDescription = $this->lockOwned($user, $id);
            $existing = CvIdempotency::existing($user, 'delete-job-description', $idempotencyKey, $hash);
            if ($existing['replayed']) {
                return ['body' => $existing['body'] ?? [], 'status' => $existing['status'] ?? 204, 'replayed' => true];
            }
            if ($jobDescription->isDeleted()) {
                throw new ApiProblem('JOB_DESCRIPTION_DELETED', 'The Job Description is already deleted.', 409);
            }
            $this->assertIfMatch($jobDescription, $ifMatch);
            $jobDescription->forceFill(['deleted_at' => Carbon::now(), 'updated_at' => Carbon::now()])->save();
            CvIdempotency::record($user, 'delete-job-description', $idempotencyKey, $hash, [], 204);

            return ['body' => [], 'status' => 204, 'replayed' => false];
        });
    }

    public function list(User $user, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->ownedQuery($user)->whereNull('deleted_at')
            ->with('currentRevision')->orderByDesc('updated_at')->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    private function lockOwned(User $user, string $id): JobDescription
    {
        if (! ProfileDocument::isUlid($id)) {
            throw $this->notFound();
        }
        $jobDescription = $this->ownedQuery($user, $id)->lockForUpdate()->first();
        if (! $jobDescription instanceof JobDescription) {
            throw $this->notFound();
        }

        return $jobDescription;
    }

    private function ownedQuery(User $user, ?string $id = null): Builder
    {
        $query = JobDescription::query()->where('user_id', $user->getKey());
        if ($id !== null) {
            $query->where('id', $id);
        }

        return $query;
    }

    private function assertIfMatch(JobDescription $jobDescription, string $ifMatch): void
    {
        if (preg_match('/^"([0-9A-HJKMNP-TV-Z]{26})"$/', $ifMatch, $matches) !== 1) {
            throw new ApiProblem('VALIDATION_FAILED', 'One or more fields are invalid.', 422, [
                'if_match' => [['code' => 'INVALID', 'message' => 'The If-Match header must contain the current revision identifier.']],
            ]);
        }
        if ((string) $jobDescription->current_revision_id !== $matches[1]) {
            throw new ApiProblem('JOB_DESCRIPTION_UPDATE_CONFLICT', 'The Job Description has changed. Reload it before saving.', 409, [
                'current_revision_id' => [['code' => 'STALE', 'message' => 'The supplied revision is stale.']],
            ]);
        }
    }

    private function fingerprint(array $payload, string $route): string
    {
        return hash('sha256', CanonicalJson::encode($payload)."\n".$route);
    }

    private function advisoryLock(User $user): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::select('SELECT pg_advisory_xact_lock(?)', [(int) $user->getKey()]);
        }
    }

    private function notFound(): ApiProblem
    {
        return new ApiProblem('RESOURCE_NOT_FOUND', 'The requested resource was not found.', 404);
    }
}
