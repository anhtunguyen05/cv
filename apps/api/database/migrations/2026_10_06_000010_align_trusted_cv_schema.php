<?php

declare(strict_types=1);

use App\Application\Cv\CanonicalJson;
use App\Application\Cv\LegacyCvCanonicalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->alignProfiles();
        $this->alignVersions();
        $this->createIdempotencyLedger();
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cv_profiles DROP CONSTRAINT IF EXISTS cv_profiles_revision_positive');
            DB::statement('ALTER TABLE cv_versions DROP CONSTRAINT IF EXISTS cv_versions_source_revision_positive');
            DB::statement('DROP TRIGGER IF EXISTS cv_versions_immutable_trigger ON cv_versions');
            DB::statement('DROP FUNCTION IF EXISTS reject_cv_version_mutation()');
        }
        Schema::dropIfExists('cv_idempotency_keys');
    }

    private function alignProfiles(): void
    {
        if (! Schema::hasTable('cv_profiles')) {
            return;
        }
        Schema::table('cv_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('cv_profiles', 'normalized_title')) {
                $table->string('normalized_title', 480)->nullable();
            }
            if (! Schema::hasColumn('cv_profiles', 'revision')) {
                $table->unsignedBigInteger('revision')->default(1);
            }
            if (! Schema::hasColumn('cv_profiles', 'document')) {
                $table->jsonb('document')->nullable();
            }
            if (! Schema::hasColumn('cv_profiles', 'schema_version')) {
                $table->string('schema_version', 20)->default('1.0');
            }
        });

        $normalizedTitles = [];
        foreach (DB::table('cv_profiles')->orderBy('user_id')->orderBy('id')->get() as $profile) {
            $normalizedTitle = trim((string) $profile->title);
            $ownerTitles = $normalizedTitles[(string) $profile->user_id] ?? [];
            if (isset($ownerTitles[$normalizedTitle])) {
                $normalizedTitle .= ' #'.substr((string) $profile->id, -8);
            }
            $ownerTitles[$normalizedTitle] = true;
            $normalizedTitles[(string) $profile->user_id] = $ownerTitles;
            $updates = [
                'normalized_title' => $normalizedTitle,
                'revision' => max(1, (int) ($profile->revision ?? 1)),
            ];
            if ($profile->document === null || $profile->document === '') {
                $legacy = [
                    'personal_info' => $this->decodeJson($profile->personal_info ?? '{}', []),
                    'summary' => $this->decodeJson($profile->summary ?? 'null', null),
                    'skills' => $this->decodeJson($profile->skills ?? '[]', []),
                    'education' => $this->decodeJson($profile->education ?? '[]', []),
                    'experience' => $this->decodeJson($profile->experience ?? '[]', []),
                    'projects' => $this->decodeJson($profile->projects ?? '[]', []),
                    'certificates' => $this->decodeJson($profile->certificates ?? '[]', []),
                    'languages' => $this->decodeJson($profile->languages ?? '[]', []),
                    'activities' => $this->decodeJson($profile->activities ?? '[]', []),
                ];
                $updates['document'] = CanonicalJson::encode(LegacyCvCanonicalizer::profileDocument($legacy, (string) $profile->title));
            }
            DB::table('cv_profiles')->where('id', $profile->id)->update($updates);
        }

        Schema::table('cv_profiles', function (Blueprint $table): void {
            $table->unique(['user_id', 'normalized_title'], 'cv_profiles_user_title_unique');
            $table->index(['user_id', 'updated_at', 'id'], 'cv_profiles_owner_updated_idx');
            $table->jsonb('document')->nullable(false)->change();
        });
        $legacyColumns = array_values(array_filter([
            Schema::hasColumn('cv_profiles', 'personal_info') ? 'personal_info' : null,
            Schema::hasColumn('cv_profiles', 'summary') ? 'summary' : null,
            Schema::hasColumn('cv_profiles', 'skills') ? 'skills' : null,
            Schema::hasColumn('cv_profiles', 'education') ? 'education' : null,
            Schema::hasColumn('cv_profiles', 'experience') ? 'experience' : null,
            Schema::hasColumn('cv_profiles', 'projects') ? 'projects' : null,
            Schema::hasColumn('cv_profiles', 'certificates') ? 'certificates' : null,
            Schema::hasColumn('cv_profiles', 'languages') ? 'languages' : null,
            Schema::hasColumn('cv_profiles', 'activities') ? 'activities' : null,
        ]));
        if ($legacyColumns !== []) {
            Schema::table('cv_profiles', function (Blueprint $table) use ($legacyColumns): void {
                $table->dropColumn($legacyColumns);
            });
        }
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cv_profiles ADD CONSTRAINT cv_profiles_revision_positive CHECK (revision > 0)');
        }
    }

    private function alignVersions(): void
    {
        if (! Schema::hasTable('cv_versions')) {
            return;
        }
        Schema::table('cv_versions', function (Blueprint $table): void {
            if (Schema::hasColumn('cv_versions', 'cv_profile_id') && ! Schema::hasColumn('cv_versions', 'source_profile_id')) {
                $table->renameColumn('cv_profile_id', 'source_profile_id');
            }
            if (! Schema::hasColumn('cv_versions', 'source_profile_revision')) {
                $table->unsignedBigInteger('source_profile_revision')->default(1);
            }
            if (! Schema::hasColumn('cv_versions', 'snapshot_schema_version')) {
                $table->string('snapshot_schema_version', 20)->default('1.0');
            }
            if (! Schema::hasColumn('cv_versions', 'snapshot')) {
                $table->jsonb('snapshot')->nullable();
            }
            if (! Schema::hasColumn('cv_versions', 'snapshot_hash')) {
                $table->string('snapshot_hash', 64)->nullable();
            }
        });
        foreach (DB::table('cv_versions')->get() as $version) {
            $updates = [];
            if ($version->snapshot === null || $version->snapshot === '') {
                $legacy = [
                    'personal_info' => $this->decodeJson($version->snapshot_personal_info ?? '{}', []),
                    'summary' => $this->decodeJson($version->snapshot_summary ?? 'null', null),
                    'skills' => $this->decodeJson($version->snapshot_skills ?? '[]', []),
                    'education' => $this->decodeJson($version->snapshot_education ?? '[]', []),
                    'experience' => $this->decodeJson($version->snapshot_experience ?? '[]', []),
                    'projects' => $this->decodeJson($version->snapshot_projects ?? '[]', []),
                    'certificates' => $this->decodeJson($version->snapshot_certificates ?? '[]', []),
                    'languages' => $this->decodeJson($version->snapshot_languages ?? '[]', []),
                    'activities' => $this->decodeJson($version->snapshot_activities ?? '[]', []),
                ];
                $snapshot = LegacyCvCanonicalizer::snapshotFromLegacy(
                    (string) (DB::table('cv_profiles')->where('id', $version->source_profile_id)->value('title') ?? ''),
                    $legacy,
                );
                $updates['snapshot'] = CanonicalJson::encode($snapshot);
            } else {
                $snapshot = $this->decodeJson($version->snapshot, []);
            }
            $updates['snapshot_hash'] = hash('sha256', CanonicalJson::encode($snapshot));
            $updates['snapshot_schema_version'] = $version->snapshot_schema_version ?? ($version->schema_version ?? '1.0');
            DB::table('cv_versions')->where('id', $version->id)->update($updates);
        }
        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->jsonb('snapshot')->nullable(false)->change();
            $table->string('snapshot_hash', 64)->nullable(false)->change();
            $table->index(['user_id', 'created_at', 'id'], 'cv_versions_owner_created_idx');
            $table->index('source_profile_id', 'cv_versions_source_profile_idx');
        });
        $legacyColumns = array_values(array_filter([
            Schema::hasColumn('cv_versions', 'snapshot_personal_info') ? 'snapshot_personal_info' : null,
            Schema::hasColumn('cv_versions', 'snapshot_summary') ? 'snapshot_summary' : null,
            Schema::hasColumn('cv_versions', 'snapshot_skills') ? 'snapshot_skills' : null,
            Schema::hasColumn('cv_versions', 'snapshot_education') ? 'snapshot_education' : null,
            Schema::hasColumn('cv_versions', 'snapshot_experience') ? 'snapshot_experience' : null,
            Schema::hasColumn('cv_versions', 'snapshot_projects') ? 'snapshot_projects' : null,
            Schema::hasColumn('cv_versions', 'snapshot_certificates') ? 'snapshot_certificates' : null,
            Schema::hasColumn('cv_versions', 'snapshot_languages') ? 'snapshot_languages' : null,
            Schema::hasColumn('cv_versions', 'snapshot_activities') ? 'snapshot_activities' : null,
            Schema::hasColumn('cv_versions', 'schema_version') ? 'schema_version' : null,
        ]));
        if ($legacyColumns !== []) {
            Schema::table('cv_versions', function (Blueprint $table) use ($legacyColumns): void {
                $table->dropColumn($legacyColumns);
            });
        }
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cv_versions ADD CONSTRAINT cv_versions_source_revision_positive CHECK (source_profile_revision > 0)');
            DB::statement(<<<'SQL'
                CREATE OR REPLACE FUNCTION reject_cv_version_mutation()
                RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    RAISE EXCEPTION 'cv_versions are immutable';
                END;
                $$
            SQL);
            DB::statement(<<<'SQL'
                CREATE TRIGGER cv_versions_immutable_trigger
                BEFORE UPDATE OR DELETE ON cv_versions
                FOR EACH ROW EXECUTE FUNCTION reject_cv_version_mutation()
            SQL);
        }
    }

    private function createIdempotencyLedger(): void
    {
        if (Schema::hasTable('cv_idempotency_keys')) {
            return;
        }
        Schema::create('cv_idempotency_keys', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('operation', 80);
            $table->uuid('key');
            $table->string('request_hash', 64);
            $table->unsignedSmallInteger('response_status');
            $table->jsonb('response_body');
            $table->timestampTz('expires_at');
            $table->timestampTz('created_at')->useCurrent();
            $table->unique(['user_id', 'operation', 'key'], 'cv_idempotency_owner_operation_key_unique');
            $table->index('expires_at', 'cv_idempotency_expires_idx');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    private function decodeJson(mixed $value, mixed $fallback): mixed
    {
        if (is_array($value)) {
            return $value;
        }
        try {
            return json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return $fallback;
        }
    }
};
