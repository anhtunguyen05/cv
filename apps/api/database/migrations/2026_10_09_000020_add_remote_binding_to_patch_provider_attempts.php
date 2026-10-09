<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patch_provider_attempts', function (Blueprint $table): void {
            $table->char('execution_id', 26)->nullable()->after('predecessor_patch_id');
            $table->char('request_hash', 64)->nullable()->after('execution_id');
            $table->char('source_cv_version_id', 26)->nullable()->after('request_hash');
            $table->char('source_snapshot_hash', 64)->nullable()->after('source_cv_version_id');
            $table->char('logical_operation_key', 64)->nullable()->after('source_snapshot_hash');
            $table->jsonb('response_metadata')->nullable()->after('logical_operation_key');
            $table->index('execution_id', 'ppa_execution_idx');
            $table->index('logical_operation_key', 'ppa_operation_idx');
        });
    }

    public function down(): void
    {
        Schema::table('patch_provider_attempts', function (Blueprint $table): void {
            $table->dropIndex('ppa_execution_idx');
            $table->dropIndex('ppa_operation_idx');
            $table->dropColumn(['execution_id', 'request_hash', 'source_cv_version_id', 'source_snapshot_hash', 'logical_operation_key', 'response_metadata']);
        });
    }
};
