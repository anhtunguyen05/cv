<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_job_description_revisions_table
 *
 * IMMUTABLE revision records — one new row per each edit of a Job Description.
 * Old revisions MUST NEVER be updated or deleted (AD-11, DATA-STD-006).
 *
 * This is Step 2 of 3 for the circular FK resolution:
 *   Step 1 → job_descriptions created WITHOUT current_revision_id FK
 *   Step 2 → this file (depends on job_descriptions)
 *   Step 3 → add_current_revision_fk_to_job_descriptions (adds FK from step 1)
 *
 * Design notes:
 * - No `updated_at` — record is immutable after INSERT
 * - user_id denormalized for ownership checks without joining job_descriptions
 * - title and company are snapshot values at revision creation time
 *   (separate from job_descriptions.title/company which may change on next edit)
 * - raw_text is sensitive — NEVER include in application logs (SEC-STD-004)
 *
 * Authority: AD-4, AD-11, DATA-STD-002, DATA-STD-003, DATA-STD-005 (raw input separate),
 * DATA-STD-006 (immutable), FR-6, FR-7
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_description_revisions', function (Blueprint $table) {
            // ULID primary key — DATA-STD-002
            $table->char('id', 26)->primary();

            // Parent logical Job Description
            $table->char('job_description_id', 26);

            // Denormalized ownership for fast authorization without joins (SEC-STD-003)
            $table->unsignedBigInteger('user_id');

            // Monotonically increasing revision counter within the same JD
            $table->unsignedInteger('revision_number')
                ->comment('Increments from 1 per job_description_id; used for ordering and display');

            // Snapshot of display fields at revision creation time
            // These may differ from current job_descriptions.title / .company after later edits
            $table->string('title')->nullable()
                ->comment('Snapshot of role title at the time this revision was created');
            $table->string('company')->nullable()
                ->comment('Snapshot of company name at the time this revision was created');

            // Raw Job Description text — sensitive content (SEC-STD-004)
            // NOT NULL: a revision with no text is meaningless
            $table->text('raw_text')
                ->comment('Original pasted JD text — SENSITIVE; must not appear in logs');

            // Single creation timestamp — NO updated_at (immutable record)
            $table->timestampTz('created_at')->useCurrent();

            // Foreign keys
            $table->foreign('job_description_id')
                ->references('id')->on('job_descriptions')
                ->onDelete('restrict')
                ->name('jd_revisions_job_description_id_fk');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->name('jd_revisions_user_id_fk');
        });

        // Indexes
        Schema::table('job_description_revisions', function (Blueprint $table) {
            // Sort revisions for a JD — newest first; also enforces sequence ordering
            $table->index(['job_description_id', 'revision_number'], 'jdr_jd_revision_idx');

            // Ownership lookup
            $table->index('user_id', 'jdr_user_id_idx');

            // Unique: each (job_description_id, revision_number) pair must be distinct
            $table->unique(['job_description_id', 'revision_number'], 'jdr_jd_revision_number_unique');
        });

        // CHECK constraints
        DB::statement('ALTER TABLE job_description_revisions ADD CONSTRAINT jdr_raw_text_not_empty CHECK (LENGTH(TRIM(raw_text)) > 0)');
        DB::statement('ALTER TABLE job_description_revisions ADD CONSTRAINT jdr_revision_number_positive CHECK (revision_number > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('job_description_revisions');
    }
};
