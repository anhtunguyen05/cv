<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_job_description_analyses_table
 *
 * Stores the deterministic analysis result of one Job Description Revision
 * under one specific analysis rule version.
 *
 * Key design decisions:
 * 1. UNIQUE(job_description_revision_id, analysis_rule_version)
 *    → Same revision + same rules always yields one canonical analysis record.
 *    → Enforces determinism required by NFR-5 and AD-5.
 *
 * 2. All extracted JSONB fields are NULLABLE:
 *    NULL means "signal absent in this JD" — distinct from "analysis not yet run".
 *    Frontend must distinguish between NULL (absent) and missing (not analyzed).
 *
 * 3. status lifecycle:
 *    pending → running → succeeded | failed
 *    Only `succeeded` analyses may be consumed by match_reports (AD-11, FR-8).
 *
 * 4. error_message is sanitized before storage (ERROR-STD-003).
 *    raw_text and user content must NOT appear here (SEC-STD-004).
 *
 * 5. user_id denormalized for ownership checks without joins (SEC-STD-003).
 *
 * Authority: AD-5, AD-11, DATA-STD-002, DATA-STD-003, DATA-STD-005, NFR-4, NFR-5
 * FR-7 (analyze Job Description), FR-8 (precondition for Match Report)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_description_analyses', function (Blueprint $table) {
            // ULID primary key — DATA-STD-002
            $table->char('id', 26)->primary();

            // Source revision — immutable reference
            $table->char('job_description_revision_id', 26);

            // Denormalized ownership
            $table->unsignedBigInteger('user_id');

            // Processing status
            $table->string('status', 20)->default('pending')
                ->comment('Lifecycle: pending → running → succeeded | failed');

            // The version of extraction rules used — pinned for reproducibility (AD-5, NFR-5)
            $table->string('analysis_rule_version', 50)
                ->comment('SemVer of the deterministic extraction rules e.g. 1.0.0');

            // Extracted signals — NULL = absent in JD (not fabricated) per FR-7, section 6
            $table->jsonb('extracted_role')->nullable()
                ->comment('{ title, seniority_level, employment_type } — null if not detectable');
            $table->jsonb('required_skills')->nullable()
                ->comment('[{ name, normalized, confidence }] — null if none detected');
            $table->jsonb('nice_to_have_skills')->nullable()
                ->comment('[{ name, normalized }] — null if none detected');
            $table->jsonb('responsibilities')->nullable()
                ->comment('["string", ...] — null if not detectable');
            $table->jsonb('keywords')->nullable()
                ->comment('["string", ...] — null if not detectable');
            $table->string('seniority', 50)->nullable()
                ->comment('intern|junior|mid|senior|lead|unknown — null if not detectable');
            $table->jsonb('soft_skills')->nullable()
                ->comment('["string", ...] — null if not detectable');
            $table->jsonb('domain_context')->nullable()
                ->comment('{ industry, stack, tags[] } — null if not detectable');

            // Failure detail — sanitized, no user content (SEC-STD-004, ERROR-STD-003)
            $table->text('error_message')->nullable()
                ->comment('Sanitized error detail when status=failed; must not contain CV/JD content');

            // Completion timestamp for SLA/monitoring
            $table->timestampTz('completed_at')->nullable()
                ->comment('Set when status transitions to succeeded or failed');

            // Timestamps UTC — DATA-STD-003
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign keys
            $table->foreign('job_description_revision_id')
                ->references('id')->on('job_description_revisions')
                ->onDelete('restrict')
                ->name('jda_revision_id_fk');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->name('jda_user_id_fk');
        });

        // Indexes
        Schema::table('job_description_analyses', function (Blueprint $table) {
            // Core query: "does this revision have a successful analysis?"
            // Used as precondition check before creating a Match Report
            $table->index(['job_description_revision_id', 'status'], 'jda_revision_status_idx');

            // Determinism constraint: 1 revision + 1 rule version = 1 analysis record
            $table->unique(
                ['job_description_revision_id', 'analysis_rule_version'],
                'jda_revision_rule_version_unique'
            );

            $table->index('user_id', 'jda_user_id_idx');
        });

        // CHECK constraints
        DB::statement("ALTER TABLE job_description_analyses ADD CONSTRAINT jda_status_check CHECK (status IN ('pending', 'running', 'succeeded', 'failed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('job_description_analyses');
    }
};
