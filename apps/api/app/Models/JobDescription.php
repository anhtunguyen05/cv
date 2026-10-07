<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class JobDescription extends Model
{
    protected $table = 'job_descriptions';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'user_id', 'title', 'company', 'current_revision_id', 'deleted_at'];

    protected function casts(): array
    {
        return ['deleted_at' => 'immutable_datetime', 'created_at' => 'immutable_datetime', 'updated_at' => 'immutable_datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(JobDescriptionRevision::class);
    }

    public function currentRevision(): BelongsTo
    {
        return $this->belongsTo(JobDescriptionRevision::class, 'current_revision_id');
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }
}
