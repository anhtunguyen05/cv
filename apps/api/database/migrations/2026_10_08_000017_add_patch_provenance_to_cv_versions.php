<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->char('source_cv_version_id', 26)->nullable()->after('source_profile_revision');
            $table->char('source_match_report_id', 26)->nullable()->after('source_cv_version_id');
            $table->char('source_interview_id', 26)->nullable()->after('source_match_report_id');
            $table->char('source_patch_id', 26)->nullable()->after('source_interview_id');
            $table->jsonb('provenance')->nullable()->after('snapshot_hash');
            $table->index('source_cv_version_id', 'cv_versions_source_version_idx');
            $table->index('source_patch_id', 'cv_versions_source_patch_idx');
        });
    }

    public function down(): void
    {
        Schema::table('cv_versions', function (Blueprint $table): void {
            $table->dropIndex('cv_versions_source_version_idx');
            $table->dropIndex('cv_versions_source_patch_idx');
            $table->dropColumn(['source_cv_version_id', 'source_match_report_id', 'source_interview_id', 'source_patch_id', 'provenance']);
        });
    }
};
