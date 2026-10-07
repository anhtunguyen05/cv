<?php

declare(strict_types=1);

namespace App\Models;

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
        'created_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
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
