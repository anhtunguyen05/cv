<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_description_analyses') && ! Schema::hasColumn('job_description_analyses', 'analysis_schema_version')) {
            Schema::table('job_description_analyses', function (Blueprint $table): void {
                $table->string('analysis_schema_version', 50)->default('1.0.0')->after('status');
            });
        }
        if (Schema::hasTable('job_description_analyses') && ! Schema::hasColumn('job_description_analyses', 'seniority_state')) {
            Schema::table('job_description_analyses', function (Blueprint $table): void {
                $table->string('seniority_state', 20)->default('absent')->after('seniority');
            });
        }

        if (Schema::hasTable('match_reports') && ! Schema::hasColumn('match_reports', 'report_schema_version')) {
            Schema::table('match_reports', function (Blueprint $table): void {
                $table->string('report_schema_version', 50)->default('1.0.0')->after('matching_rule_version');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('match_reports') && Schema::hasColumn('match_reports', 'report_schema_version')) {
            Schema::table('match_reports', function (Blueprint $table): void {
                $table->dropColumn('report_schema_version');
            });
        }
        if (Schema::hasTable('job_description_analyses') && Schema::hasColumn('job_description_analyses', 'analysis_schema_version')) {
            $columns = ['analysis_schema_version'];
            if (Schema::hasColumn('job_description_analyses', 'seniority_state')) {
                $columns[] = 'seniority_state';
            }
            Schema::table('job_description_analyses', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};
