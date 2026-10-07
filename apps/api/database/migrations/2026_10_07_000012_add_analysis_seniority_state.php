<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_description_analyses') && ! Schema::hasColumn('job_description_analyses', 'seniority_state')) {
            Schema::table('job_description_analyses', function (Blueprint $table): void {
                $table->string('seniority_state', 20)->default('absent')->after('seniority');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('job_description_analyses') && Schema::hasColumn('job_description_analyses', 'seniority_state')) {
            Schema::table('job_description_analyses', function (Blueprint $table): void {
                $table->dropColumn('seniority_state');
            });
        }
    }
};
