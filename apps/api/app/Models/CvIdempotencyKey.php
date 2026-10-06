<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CvIdempotencyKey extends Model
{
    protected $table = 'cv_idempotency_keys';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'operation', 'key', 'request_hash', 'response_status', 'response_body',
        'expires_at', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'response_body' => 'array',
            'expires_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }
}
