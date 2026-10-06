<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CvVersion extends Model
{
    protected $table = 'cv_versions';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'source_profile_id', 'source_profile_revision', 'name',
        'snapshot_schema_version', 'snapshot', 'snapshot_hash', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'source_profile_revision' => 'integer',
            'snapshot' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CvProfile::class, 'source_profile_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
