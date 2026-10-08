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
        Schema::create('operational_audit_events', function (Blueprint $table): void {
            $table->char('id', 26)->primary();
            $table->string('event_version', 20);
            $table->string('redaction_version', 20);
            $table->boolean('non_authoritative')->default(true);
            $table->string('actor_type', 16);
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->timestampTz('occurred_at');
            $table->string('operation', 64);
            $table->string('tool', 64);
            $table->string('provider', 64);
            $table->string('model', 128);
            $table->string('contract_version', 64);
            $table->string('prompt_version', 64);
            $table->string('tool_schema_version', 64);
            $table->string('resource_type', 64);
            $table->string('correlation_id', 128);
            $table->string('attempt_id', 128);
            $table->string('environment', 128);
            $table->string('status', 32);
            $table->string('failure_category', 32);
            $table->unsignedInteger('duration_ms');
            $table->unsignedTinyInteger('retry_count');
            $table->string('latency_class', 32);

            $table->index(['occurred_at', 'id'], 'oae_occurred_id_idx');
            $table->index(['operation', 'occurred_at'], 'oae_operation_occurred_idx');
            $table->index(['correlation_id', 'occurred_at'], 'oae_correlation_occurred_idx');
            $table->index(['actor_user_id', 'occurred_at'], 'oae_actor_occurred_idx');
        });

        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::unprepared(<<<'SQL'
                CREATE FUNCTION operational_audit_events_append_only_guard()
                RETURNS trigger
                LANGUAGE plpgsql
                AS $$
                BEGIN
                    RAISE EXCEPTION 'operational_audit_events is append-only';
                END;
                $$
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER operational_audit_events_append_only_guard
                BEFORE UPDATE OR DELETE ON operational_audit_events
                FOR EACH ROW
                EXECUTE FUNCTION operational_audit_events_append_only_guard()
            SQL);
        }

        if ($driver === 'sqlite') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER operational_audit_events_update_guard
                BEFORE UPDATE ON operational_audit_events
                BEGIN
                    SELECT RAISE(ABORT, 'operational_audit_events is append-only');
                END
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER operational_audit_events_delete_guard
                BEFORE DELETE ON operational_audit_events
                BEGIN
                    SELECT RAISE(ABORT, 'operational_audit_events is append-only');
                END
            SQL);
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS operational_audit_events_append_only_guard ON operational_audit_events');
            DB::unprepared('DROP FUNCTION IF EXISTS operational_audit_events_append_only_guard()');
        }

        if ($driver === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS operational_audit_events_update_guard');
            DB::unprepared('DROP TRIGGER IF EXISTS operational_audit_events_delete_guard');
        }

        Schema::dropIfExists('operational_audit_events');
    }
};
