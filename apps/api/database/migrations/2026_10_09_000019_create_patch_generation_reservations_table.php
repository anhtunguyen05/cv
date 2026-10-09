<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patch_generation_reservations', function (Blueprint $table): void {
            $table->char('id', 26)->primary();
            $table->unsignedBigInteger('user_id');
            $table->char('interview_id', 26);
            $table->char('predecessor_patch_id', 26)->nullable();
            $table->char('logical_operation_key', 64);
            $table->char('execution_id', 26);
            $table->char('request_hash', 64);
            $table->char('source_cv_version_id', 26);
            $table->char('source_snapshot_hash', 64);
            $table->char('correlation_id', 32);
            $table->string('status', 32);
            $table->char('patch_id', 26)->nullable();
            $table->jsonb('response')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->name('pgr_user_id_fk');
            $table->foreign('interview_id')->references('id')->on('evidence_interviews')->onDelete('restrict')->name('pgr_interview_fk');
            $table->foreign('predecessor_patch_id')->references('id')->on('patches')->onDelete('restrict')->name('pgr_predecessor_fk');
            $table->foreign('source_cv_version_id')->references('id')->on('cv_versions')->onDelete('restrict')->name('pgr_source_version_fk');
            $table->foreign('patch_id')->references('id')->on('patches')->onDelete('restrict')->name('pgr_patch_fk');
            $table->unique(['user_id', 'logical_operation_key'], 'pgr_user_operation_unique');
            $table->index(['interview_id', 'status'], 'pgr_interview_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patch_generation_reservations');
    }
};
