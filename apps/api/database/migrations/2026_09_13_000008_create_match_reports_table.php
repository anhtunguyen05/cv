<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_match_reports_table
 *
 * IMMUTABLE deterministic comparison result for one CV Version against one Job Description.
 * This table is the core output of the CareerFitCV matching workflow (FR-8, FR-9).
 *
 * Source pinning (AD-4, AD-11, NFR-2, NFR-5):
 * Every record stores ALL of the following identifiers so the report is fully
 * reproducible regardless of later profile edits or JD updates:
 *   - cv_version_id          → the exact immutable CV snapshot used
 *   - job_description_id     → the logical JD (preserved even after soft-delete)
 *   - job_description_revision_id → the exact immutable revision used
 *   - analysis_id            → the exact analysis record consumed
 *   - analysis_rule_version  → denorm snapshot (redundant with analysis.analysis_rule_version for self-sufficiency)
 *   - matching_rule_version  → the version of the scoring/vocabulary/weight rules
 *
 * No `updated_at` — record is immutable once created.
 * user_id denormalized for single-step ownership check (SEC-STD-003).
 *
 * Business preconditions (enforced in domain/application code, NOT database constraints):
 * 1. job_descriptions.deleted_at MUST be NULL at creation time
 * 2. job_description_analyses.status MUST be 'succeeded' for the chosen analysis_id
 * 3. Both cv_version and job_description must belong to auth()->id()
 *
 * Authority: AD-4, AD-5, AD-11, DATA-STD-002, DATA-STD-003, DATA-STD-006, NFR-2, NFR-5
 * FR-8 (generate), FR-9 (explain evidence)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_reports', function (Blueprint $table) {
            // ULID primary key — DATA-STD-002
            $table->char('id', 26)->primary();

            // Denormalized ownership — single-step authorization check (SEC-STD-003)
            $table->unsignedBigInteger('user_id');

            // Source CV Version — immutable snapshot (AD-3, AD-4)
            $table->char('cv_version_id', 26);

            // Logical Job Description reference — preserved even after soft-delete (AD-11, NFR-2)
            $table->char('job_description_id', 26);

            // Exact immutable revision used for this report (AD-11)
            $table->char('job_description_revision_id', 26);

            // Exact analysis record consumed (must have status='succeeded')
            $table->char('analysis_id', 26);

            // Rule version snapshots — denorm for full self-contained reproducibility (NFR-5)
            $table->string('analysis_rule_version', 50)
                ->comment('Denorm from job_description_analyses.analysis_rule_version at creation time');
            $table->string('matching_rule_version', 50)
                ->comment('Version of skill vocabulary, weights, and scoring algorithm e.g. 1.0.0');

            // Matching output
            $table->decimal('overall_score', 5, 2)
                ->comment('0.00 to 100.00 — overall CV-to-JD fit score');

            // Detailed results — JSONB for explainability (FR-9)
            $table->jsonb('matched_skills')->default('[]')
                ->comment('[{ skill, normalized, evidence_level: strong|weak, cv_sections[] }]');
            $table->jsonb('missing_skills')->default('[]')
                ->comment('[{ skill, normalized, importance: required|nice_to_have }]');
            $table->jsonb('weak_evidence')->default('[]')
                ->comment('[{ skill, found_in, missing_from[], recommendation_ref }]');
            $table->jsonb('recommendations')->default('[]')
                ->comment('[{ id, area, action, cv_section, cv_item_id }] — cv_item_id links to item ULID in snapshot');

            // Single creation timestamp — NO updated_at (immutable record)
            $table->timestampTz('created_at')->useCurrent();

            // Foreign keys — RESTRICT to protect historical reproducibility
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->name('mr_user_id_fk');

            $table->foreign('cv_version_id')
                ->references('id')->on('cv_versions')
                ->onDelete('restrict')
                ->name('mr_cv_version_id_fk');

            $table->foreign('job_description_id')
                ->references('id')->on('job_descriptions')
                ->onDelete('restrict')
                ->name('mr_job_description_id_fk');

            $table->foreign('job_description_revision_id')
                ->references('id')->on('job_description_revisions')
                ->onDelete('restrict')
                ->name('mr_job_description_revision_id_fk');

            $table->foreign('analysis_id')
                ->references('id')->on('job_description_analyses')
                ->onDelete('restrict')
                ->name('mr_analysis_id_fk');
        });

        // Indexes
        Schema::table('match_reports', function (Blueprint $table) {
            // Primary list query: user's reports, newest first
            $table->index(['user_id', 'created_at'], 'mr_user_created_idx');

            // Cross-references for related-report lookups
            $table->index('cv_version_id', 'mr_cv_version_id_idx');
            $table->index('job_description_id', 'mr_job_description_id_idx');
            $table->index('job_description_revision_id', 'mr_jdr_id_idx');
            $table->index('analysis_id', 'mr_analysis_id_idx');
        });

        // CHECK constraints
        DB::statement('ALTER TABLE match_reports ADD CONSTRAINT mr_score_range CHECK (overall_score BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('match_reports');
    }
};
