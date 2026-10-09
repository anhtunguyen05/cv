<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PatchGenerationReservation extends Model
{
    protected $table = 'patch_generation_reservations';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'interview_id', 'predecessor_patch_id', 'logical_operation_key',
        'execution_id', 'request_hash', 'source_cv_version_id', 'source_snapshot_hash',
        'correlation_id', 'status', 'patch_id', 'response', 'created_at', 'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'response' => 'array',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
