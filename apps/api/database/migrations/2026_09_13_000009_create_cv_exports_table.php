<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_cv_exports_table
 *
 * Audit/lifecycle record for each Export action initiated by a User.
 *
 * MVP behavior (AD-10):
 * - export_type = 'browser_print': the Vue client triggers window.print() after rendering
 *   the CV Version using the selected Template. No server-side file generation needed.
 * - status = 'initiated' → 'completed' | 'failed'
 * - No file artifact is stored server-side for MVP browser_print exports.
 *   This record is an audit trail only.
 *
 * Post-MVP extension points:
 * - export_type = 'pdf_worker': server-side PDF via optional worker (AD-8, AD-10)
 * - storage_path column can be added in a later migration if file artifacts need tracking
 *
 * template_version is denormalized (snapshot) so the export record remains
 * self-describing even after the template is upgraded (AD-4).
 *
 * user_id denormalized for single-step ownership authorization (SEC-STD-003).
 *
 * Authority: AD-4, AD-8, AD-10, DATA-STD-002, DATA-STD-003, NFR-2, NFR-4
 * FR-11 (export reviewed CV), Story 3.3
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_exports', function (Blueprint $table) {
            // ULID primary key — DATA-STD-002
            $table->char('id', 26)->primary();

            // Denormalized ownership
            $table->unsignedBigInteger('user_id');

            // The exact CV Version used — immutable source (AD-4)
            $table->char('cv_version_id', 26);

            // The template that was active at export time
            $table->char('template_id', 26);

            // Snapshot of template version — denorm for AD-4 reproducibility
            $table->string('template_version', 20)
                ->comment('Snapshot of templates.version at export time; allows reproducing visual output after template upgrades');

            // Export mechanism
            $table->string('export_type', 50)->default('browser_print')
                ->comment('MVP: browser_print. Post-MVP: pdf_worker, html_download');

            // Processing lifecycle — NFR-4 (no silent partial state)
            $table->string('status', 20)->default('initiated')
                ->comment('initiated → completed | failed');

            // Sanitized error detail for retryable UX (ERROR-STD-003)
            $table->text('error_message')->nullable()
                ->comment('Sanitized error for failed exports; must not contain CV content');

            // Timestamps UTC — DATA-STD-003
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign keys — RESTRICT to protect export history
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict')
                ->name('cv_exports_user_id_fk');

            $table->foreign('cv_version_id')
                ->references('id')->on('cv_versions')
                ->onDelete('restrict')
                ->name('cv_exports_cv_version_id_fk');

            $table->foreign('template_id')
                ->references('id')->on('templates')
                ->onDelete('restrict')
                ->name('cv_exports_template_id_fk');
        });

        // Indexes
        Schema::table('cv_exports', function (Blueprint $table) {
            // Primary list query: user's exports, newest first
            $table->index(['user_id', 'created_at'], 'cv_exports_user_created_idx');

            // Related-export lookups
            $table->index('cv_version_id', 'cv_exports_cv_version_id_idx');
        });

        // CHECK constraints
        DB::statement("ALTER TABLE cv_exports ADD CONSTRAINT cv_exports_status_check CHECK (status IN ('initiated', 'completed', 'failed'))");
        DB::statement("ALTER TABLE cv_exports ADD CONSTRAINT cv_exports_type_check CHECK (export_type IN ('browser_print', 'pdf_worker', 'html_download'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_exports');
    }
};
