<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_cv_profiles_table
 *
 * Mutable structured CV draft workspace owned by a User.
 * NEVER used directly in Match Reports or Exports — must go through cv_versions.
 *
 * Authority: AD-1 (structured CV canonical), AD-2 (Laravel owns trusted state),
 * AD-3 (CV Profile mutable; CV Version immutable), DATA-STD-002 (ULID PK),
 * DATA-STD-003 (TIMESTAMPTZ), DATA-STD-004 (JSONB for structured CV),
 * DATA-STD-008 (JSONB only for intentionally variant structures)
 * FR-3: personal_info, summary, skills, education, experience, projects, certificates, languages, activities
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_profiles', function (Blueprint $table) {
            // ULID primary key — DATA-STD-002
            $table->char('id', 26)->primary();

            // Ownership — FK to users.id (bigint, Laravel default)
            $table->unsignedBigInteger('user_id');

            // Profile identity
            $table->string('title')->comment('User-chosen profile name e.g. "Software Engineer CV"');

            // Structured CV sections — JSONB, default to empty — DATA-STD-004
            // Each array item carries a stable item ULID for cv_item_id cross-reference in match_reports
            $table->jsonb('personal_info')->default('{}')
                ->comment('first_name, last_name, email, phone, address, city, country, linkedin, github, website');
            $table->jsonb('summary')->default('{}')
                ->comment('{ text: string }');
            $table->jsonb('skills')->default('[]')
                ->comment('[{ id, category, items[] }]');
            $table->jsonb('education')->default('[]')
                ->comment('[{ id, institution, degree, field, grade, start_date, end_date, description }]');
            $table->jsonb('experience')->default('[]')
                ->comment('[{ id, company, title, employment_type, start_date, end_date, is_current, location, bullets[] }]');
            $table->jsonb('projects')->default('[]')
                ->comment('[{ id, name, role, url, start_date, end_date, technologies[], bullets[] }]');
            $table->jsonb('certificates')->default('[]')
                ->comment('[{ id, name, issuer, issued_date, expires_date, credential_url }]');
            $table->jsonb('languages')->default('[]')
                ->comment('[{ id, language, level: native|fluent|advanced|intermediate|basic }]');
            $table->jsonb('activities')->default('[]')
                ->comment('[{ id, name, role, description, start_date, end_date }]');

            // Schema versioning for future JSONB migrations — DATA-STD-006 principle
            $table->string('schema_version', 20)->default('1.0')
                ->comment('SemVer of the JSONB section schemas; used for future data migration');

            // Timestamps UTC — DATA-STD-003
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign key — CASCADE: if user is hard-deleted, their profiles go too
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->name('cv_profiles_user_id_fk');

            // Query index: list all profiles for a user
            $table->index('user_id', 'cv_profiles_user_id_idx');
        });

        // CHECK constraint: title must not be blank
        DB::statement("ALTER TABLE cv_profiles ADD CONSTRAINT cv_profiles_title_not_empty CHECK (LENGTH(TRIM(title)) > 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_profiles');
    }
};
