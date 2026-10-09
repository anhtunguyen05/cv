<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\JobFit\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class JobDescriptionRevision extends Model
{
    protected $table = 'job_description_revisions';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'job_description_id', 'user_id', 'revision_number', 'title', 'company', 'raw_text', 'created_at'];

    protected function casts(): array
    {
        return ['revision_number' => 'integer', 'created_at' => 'immutable_datetime'];
    }

    public function jobDescription(): BelongsTo
    {
        return $this->belongsTo(JobDescription::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(JobDescriptionAnalysis::class);
    }
}
