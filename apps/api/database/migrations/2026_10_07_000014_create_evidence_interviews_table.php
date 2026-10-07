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
        Schema::create('evidence_interviews', function (Blueprint $table): void {
            $table->char('id', 26)->primary();
            $table->unsignedBigInteger('user_id');
            $table->char('match_report_id', 26);
            $table->char('cv_version_id', 26);
            $table->char('job_description_id', 26);
            $table->char('job_description_revision_id', 26);
            $table->char('analysis_id', 26);
            $table->jsonb('areas');
            $table->jsonb('questions');
            $table->string('question_set_version', 20);
            $table->string('status', 20);
            $table->timestampTz('expires_at');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->name('ei_user_id_fk');
            $table->foreign('match_report_id')->references('id')->on('match_reports')->onDelete('restrict')->name('ei_match_report_id_fk');
            $table->foreign('cv_version_id')->references('id')->on('cv_versions')->onDelete('restrict')->name('ei_cv_version_id_fk');
            $table->foreign('job_description_id')->references('id')->on('job_descriptions')->onDelete('restrict')->name('ei_job_description_id_fk');
            $table->foreign('job_description_revision_id')->references('id')->on('job_description_revisions')->onDelete('restrict')->name('ei_jdr_id_fk');
            $table->foreign('analysis_id')->references('id')->on('job_description_analyses')->onDelete('restrict')->name('ei_analysis_id_fk');

            $table->index(['user_id', 'status', 'created_at'], 'ei_owner_status_created_idx');
            $table->index(['match_report_id', 'status'], 'ei_report_status_idx');
            $table->index('expires_at', 'ei_expires_idx');
        });

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement("CREATE UNIQUE INDEX ei_one_active_report_unique ON evidence_interviews (match_report_id) WHERE status = 'active'");
        }
        if ($driver === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX ei_match_source_pin_unique ON match_reports (id, user_id, cv_version_id, job_description_id, job_description_revision_id, analysis_id)');
            DB::statement('ALTER TABLE evidence_interviews ADD CONSTRAINT ei_match_source_pin_fk FOREIGN KEY (match_report_id, user_id, cv_version_id, job_description_id, job_description_revision_id, analysis_id) REFERENCES match_reports (id, user_id, cv_version_id, job_description_id, job_description_revision_id, analysis_id)');
            DB::statement("ALTER TABLE evidence_interviews ADD CONSTRAINT ei_status_valid CHECK (status IN ('active', 'completed', 'expired'))");
            DB::statement("ALTER TABLE evidence_interviews ADD CONSTRAINT ei_question_set_version_valid CHECK (question_set_version = '1.0')");
            DB::statement("ALTER TABLE evidence_interviews ADD CONSTRAINT ei_areas_array CHECK (jsonb_typeof(areas) = 'array')");
            DB::statement("ALTER TABLE evidence_interviews ADD CONSTRAINT ei_questions_array CHECK (jsonb_typeof(questions) = 'array')");
            DB::statement("ALTER TABLE evidence_interviews ADD CONSTRAINT ei_areas_count_valid CHECK (jsonb_array_length(areas) BETWEEN 1 AND 5)");
            DB::statement("ALTER TABLE evidence_interviews ADD CONSTRAINT ei_questions_count_valid CHECK (jsonb_array_length(questions) BETWEEN 1 AND 5)");
        }
    }

    public function down(): void
    {
        if (in_array(DB::connection()->getDriverName(), ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS ei_one_active_report_unique');
        }
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE evidence_interviews DROP CONSTRAINT IF EXISTS ei_match_source_pin_fk');
            DB::statement('DROP INDEX IF EXISTS ei_match_source_pin_unique');
        }
        Schema::dropIfExists('evidence_interviews');
    }
};
