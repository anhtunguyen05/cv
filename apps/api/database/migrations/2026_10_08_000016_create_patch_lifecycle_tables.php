<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patches', function (Blueprint $table): void {
            $table->char('id', 26)->primary();
            $table->unsignedBigInteger('user_id');
            $table->char('source_cv_version_id', 26);
            $table->char('match_report_id', 26);
            $table->char('interview_id', 26);
            $table->char('predecessor_patch_id', 26)->nullable();
            $table->string('status', 32);
            $table->unsignedInteger('revision')->default(1);
            $table->string('patch_schema_version', 20);
            $table->string('prompt_version', 40);
            $table->string('provider_model_version', 40);
            $table->jsonb('target');
            $table->jsonb('old_value');
            $table->jsonb('new_value');
            $table->text('reason');
            $table->jsonb('evidence_source_ids');
            $table->jsonb('provenance');
            $table->string('source_snapshot_hash', 64);
            $table->char('applied_version_id', 26)->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->name('patch_user_id_fk');
            $table->foreign('source_cv_version_id')->references('id')->on('cv_versions')->onDelete('restrict')->name('patch_source_version_fk');
            $table->foreign('match_report_id')->references('id')->on('match_reports')->onDelete('restrict')->name('patch_match_report_fk');
            $table->foreign('interview_id')->references('id')->on('evidence_interviews')->onDelete('restrict')->name('patch_interview_fk');
            $table->foreign('predecessor_patch_id')->references('id')->on('patches')->onDelete('restrict')->name('patch_predecessor_fk');
            $table->foreign('applied_version_id')->references('id')->on('cv_versions')->onDelete('restrict')->name('patch_applied_version_fk');
            $table->index(['user_id', 'status', 'created_at'], 'patch_owner_status_created_idx');
            $table->index(['interview_id', 'status'], 'patch_interview_status_idx');
            $table->index('predecessor_patch_id', 'patch_predecessor_idx');
        });

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('CREATE UNIQUE INDEX patch_one_regeneration_per_predecessor ON patches (predecessor_patch_id) WHERE predecessor_patch_id IS NOT NULL');
        }

        Schema::create('patch_provider_attempts', function (Blueprint $table): void {
            $table->char('id', 26)->primary();
            $table->unsignedBigInteger('user_id');
            $table->char('interview_id', 26);
            $table->char('patch_id', 26)->nullable();
            $table->char('predecessor_patch_id', 26)->nullable();
            $table->string('status', 32);
            $table->string('outcome_code', 80)->nullable();
            $table->string('prompt_version', 40);
            $table->string('tool_schema_version', 40);
            $table->string('provider_model_version', 40);
            $table->string('latency_class', 20)->nullable();
            $table->string('correlation_id', 64);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('completed_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->name('ppa_user_id_fk');
            $table->foreign('interview_id')->references('id')->on('evidence_interviews')->onDelete('restrict')->name('ppa_interview_fk');
            $table->foreign('patch_id')->references('id')->on('patches')->onDelete('restrict')->name('ppa_patch_fk');
            $table->foreign('predecessor_patch_id')->references('id')->on('patches')->onDelete('restrict')->name('ppa_predecessor_fk');
            $table->index(['user_id', 'created_at'], 'ppa_owner_created_idx');
            $table->index(['interview_id', 'status'], 'ppa_interview_status_idx');
            $table->index(['patch_id', 'status'], 'ppa_patch_status_idx');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE patches ADD CONSTRAINT patch_status_valid CHECK (status IN ('pending_validation', 'pending', 'rejected', 'invalid', 'applied'))");
            DB::statement("ALTER TABLE patch_provider_attempts ADD CONSTRAINT ppa_status_valid CHECK (status IN ('requested', 'running', 'succeeded', 'retryable_failure', 'terminal_failure'))");
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS patch_one_regeneration_per_predecessor');
        }
        Schema::dropIfExists('patch_provider_attempts');
        Schema::dropIfExists('patches');
    }
};
