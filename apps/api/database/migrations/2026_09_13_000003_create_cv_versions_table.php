<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_cv_versions_table
 *
 * IMMUTABLE named snapshots of a CV Profile.
 * Once inserted, records in this table MUST NEVER be updated.
 * Match Reports and Exports reference ONLY this table, never cv_profiles directly.
 *
 * Design notes:
 * - No `updated_at` column — immutability must not be implied away
 * - user_id is denormalized for fast ownership policy checks without joining cv_profiles
 *   (safe even if cv_profile is later soft-deleted or reassigned)
 * - All snapshot_* columns are deep copies of the cv_profiles JSONB at creation time
 * - schema_version captures the JSONB shape at snapshot time for future migration tooling
 *
 * Authority: AD-3, AD-4, DATA-STD-002 (ULID), DATA-STD-003 (TIMESTAMPTZ),
 * DATA-STD-006 (immutable), FR-4, FR-5
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_versions', function (Blueprint $table) {
            // ULID primary key — DATA-STD-002
            $table->char('id', 26)->primary();

            // Denormalized ownership — allows ownership check without joining cv_profiles
            // Critical for security: still valid even if cv_profile changes ownership (SEC-STD-003)
            $table->unsignedBigInteger('user_id');

            // Source profile reference — for traceability back to the original profile
            $table->char('cv_profile_id', 26);

            // User-chosen version name e.g. "Frontend Intern – Jun 2026"
            $table->string('name');

            // Immutable deep copies of all CV sections at snapshot time — DATA-STD-004
            // Prefixed with snapshot_ to make immutability semantically explicit
            $table->jsonb('snapshot_personal_info')
                ->comment('Deep copy of cv_profiles.personal_info at version creation');
            $table->jsonb('snapshot_summary')
                ->comment('Deep copy of cv_profiles.summary');
            $table->jsonb('snapshot_skills')
                ->comment('Deep copy of cv_profiles.skills — items retain their ULIDs for cross-reference');
            $table->jsonb('snapshot_education')
                ->comment('Deep copy of cv_profiles.education');
            $table->jsonb('snapshot_experience')
                ->comment('Deep copy of cv_profiles.experience');
            $table->jsonb('snapshot_projects')
                ->comment('Deep copy of cv_profiles.projects');
            $table->jsonb('snapshot_certificates')
                ->comment('Deep copy of cv_profiles.certificates');
            $table->jsonb('snapshot_languages')
                ->comment('Deep copy of cv_profiles.languages');
            $table->jsonb('snapshot_activities')
                ->comment('Deep copy of cv_profiles.activities');

            // Schema version at snapshot time — DATA-STD-008
            $table->string('schema_version', 20)->default('1.0')
                ->comment('JSONB schema version at the time of snapshot; used for future migration tooling');

            // Single creation timestamp — NO updated_at (immutable record)
            $table->timestampTz('created_at')->useCurrent();

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->name('cv_versions_user_id_fk');

            $table->foreign('cv_profile_id')
                ->references('id')->on('cv_profiles')
                ->onDelete('restrict')
                ->name('cv_versions_cv_profile_id_fk');
        });

        // Composite index: list user's versions in creation order (newest first)
        Schema::table('cv_versions', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'cv_versions_user_created_idx');
            $table->index('cv_profile_id', 'cv_versions_cv_profile_id_idx');
        });

        // CHECK: version name must not be blank
        DB::statement('ALTER TABLE cv_versions ADD CONSTRAINT cv_versions_name_not_empty CHECK (LENGTH(TRIM(name)) > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_versions');
    }
};
