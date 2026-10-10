<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Patch\Eloquent\Models;

use App\Domain\Patch\Enums\PatchStatus;
use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use App\Infrastructure\Persistence\Cv\Eloquent\Models\CvVersion;
use App\Infrastructure\Persistence\Evidence\Eloquent\Models\EvidenceInterview;
use App\Infrastructure\Persistence\JobFit\Eloquent\Models\MatchReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Patch extends Model
{
    protected $table = 'patches';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'source_cv_version_id', 'match_report_id', 'interview_id',
        'predecessor_patch_id', 'status', 'revision', 'patch_schema_version',
        'prompt_version', 'provider_model_version', 'target', 'old_value',
        'new_value', 'reason', 'evidence_source_ids', 'provenance',
        'source_snapshot_hash', 'applied_version_id', 'created_at', 'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PatchStatus::class,
            'revision' => 'integer',
            'target' => 'array',
            'old_value' => 'array',
            'new_value' => 'array',
            'evidence_source_ids' => 'array',
            'provenance' => 'array',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceVersion(): BelongsTo
    {
        return $this->belongsTo(CvVersion::class, 'source_cv_version_id');
    }

    public function matchReport(): BelongsTo
    {
        return $this->belongsTo(MatchReport::class);
    }

    public function interview(): BelongsTo
    {
        return $this->belongsTo(EvidenceInterview::class, 'interview_id');
    }

    public function predecessor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'predecessor_patch_id');
    }

    public function appliedVersion(): BelongsTo
    {
        return $this->belongsTo(CvVersion::class, 'applied_version_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PatchProviderAttempt::class, 'patch_id');
    }
}
