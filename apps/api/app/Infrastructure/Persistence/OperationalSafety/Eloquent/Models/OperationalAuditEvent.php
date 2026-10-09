<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\OperationalSafety\Eloquent\Models;

use App\Domain\OperationalSafety\Enums\AuditEventStatus;
use App\Domain\OperationalSafety\Enums\AuditFailureCategory;
use App\Domain\OperationalSafety\Enums\AuditLatencyCategory;
use Illuminate\Database\Eloquent\Model;
use LogicException;

final class OperationalAuditEvent extends Model
{
    protected $table = 'operational_audit_events';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'event_version', 'redaction_version', 'non_authoritative', 'actor_type',
        'actor_user_id', 'occurred_at', 'operation', 'tool', 'provider', 'model',
        'contract_version', 'prompt_version', 'tool_schema_version', 'resource_type',
        'correlation_id', 'attempt_id', 'environment', 'status', 'failure_category',
        'duration_ms', 'retry_count', 'latency_class',
    ];

    protected static function booted(): void
    {
        self::updating(static function (): void {
            throw new LogicException('Operational audit events are append-only.');
        });

        self::deleting(static function (): void {
            throw new LogicException('Operational audit events are append-only.');
        });
    }

    protected function casts(): array
    {
        return [
            'non_authoritative' => 'boolean',
            'actor_user_id' => 'integer',
            'occurred_at' => 'immutable_datetime',
            'status' => AuditEventStatus::class,
            'failure_category' => AuditFailureCategory::class,
            'duration_ms' => 'integer',
            'retry_count' => 'integer',
            'latency_class' => AuditLatencyCategory::class,
        ];
    }
}
