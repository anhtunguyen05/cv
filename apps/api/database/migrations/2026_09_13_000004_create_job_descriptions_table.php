<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_job_descriptions_table (WITHOUT current_revision_id FK)
 *
 * Step 1 of 3 for the circular FK between job_descriptions ↔ job_description_revisions.
 * This migration creates the table WITHOUT the foreign key to job_description_revisions,
 * because that table does not exist yet.
 *
 * Step 2 → create_job_description_revisions_table (depends on this table)
 * Step 3 → add_current_revision_fk_to_job_descriptions (adds the FK after revisions table exists)
 *
 * Soft delete behavior:
 * - Logical deletion uses `deleted_at` (DATA-STD-007)
 * - Hard delete is NEVER done when historical Match Reports exist
 * - `deleted_at IS NOT NULL` blocks all new writes (edit, analyze, match) — enforced in domain code
 *
 * Authority: AD-2, AD-11, DATA-STD-002 (ULID), DATA-STD-003, DATA-STD-007 (soft delete)
 * FR-6 (save, update, delete Job Description), FR-7, FR-8
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_descriptions', function (Blueprint $table) {
            // ULID primary key — stable logical identity across revisions (DATA-STD-002)
            $table->char('id', 26)->primary();

            // Ownership
            $table->unsignedBigInteger('user_id');

            // Display fields — nullable because JD may be created with only raw_text first
            // then title/company extracted via analysis or manual edit
            $table->string('title')->nullable()
                ->comment('Role title — may be null until set explicitly');
            $table->string('company')->nullable()
                ->comment('Company name — may be null until set explicitly');

            // current_revision_id added in migration 2026_09_13_000006 after revisions table exists
            // Declared nullable here; the FK constraint is added separately (DEFERRABLE approach)
            $table->char('current_revision_id', 26)->nullable()
                ->comment('FK to job_description_revisions.id — added in separate migration to avoid circular dependency');

            // Soft delete — DATA-STD-007
            // NULL = active; NOT NULL = logically deleted for new operations
            // Historical revisions, analyses, and match reports remain accessible after deletion
            $table->timestampTz('deleted_at')->nullable()
                ->comment('Soft delete: NULL = active. NOT NULL = blocked from new edits/analysis/matches');

            // Timestamps UTC — DATA-STD-003
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign key to users
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->name('job_descriptions_user_id_fk');
        });

        // Indexes
        Schema::table('job_descriptions', function (Blueprint $table) {
            // List active JDs for a user: WHERE user_id = ? AND deleted_at IS NULL
            $table->index(['user_id', 'deleted_at'], 'job_descriptions_user_deleted_idx');
            // FK index for current_revision_id — added now even though FK constraint comes later
            $table->index('current_revision_id', 'job_descriptions_current_revision_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_descriptions');
    }
};
