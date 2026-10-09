<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Patch\Eloquent\Models;

use App\Domain\Patch\Enums\PatchAttemptStatus;
use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use App\Infrastructure\Persistence\Evidence\Eloquent\Models\EvidenceInterview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PatchProviderAttempt extends Model
{
    protected $table = 'patch_provider_attempts';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'interview_id', 'patch_id', 'predecessor_patch_id', 'status',
        'outcome_code', 'prompt_version', 'tool_schema_version',
        'provider_model_version', 'latency_class', 'correlation_id',
        'execution_id', 'request_hash', 'source_cv_version_id', 'source_snapshot_hash',
        'logical_operation_key', 'response_metadata',
        'created_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PatchAttemptStatus::class,
            'created_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'response_metadata' => 'array',
        ];
    }

    public function interview(): BelongsTo
    {
        return $this->belongsTo(EvidenceInterview::class, 'interview_id');
    }

    public function patch(): BelongsTo
    {
        return $this->belongsTo(Patch::class, 'patch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
