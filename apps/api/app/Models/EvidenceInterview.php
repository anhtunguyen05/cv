<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class EvidenceInterview extends Model
{
    private const IMMUTABLE_ATTRIBUTES = [
        'user_id', 'match_report_id', 'cv_version_id', 'job_description_id',
        'job_description_revision_id', 'analysis_id', 'areas', 'questions',
        'question_set_version',
    ];

    protected $table = 'evidence_interviews';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'match_report_id', 'cv_version_id', 'job_description_id',
        'job_description_revision_id', 'analysis_id', 'areas', 'questions',
        'question_set_version', 'status', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'areas' => 'array',
            'questions' => 'array',
            'expires_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(static function (self $interview): void {
            foreach (self::IMMUTABLE_ATTRIBUTES as $attribute) {
                if ($interview->isDirty($attribute)) {
                    throw new LogicException('Evidence Interview source snapshots are immutable.');
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchReport(): BelongsTo
    {
        return $this->belongsTo(MatchReport::class);
    }

    public function cvVersion(): BelongsTo
    {
        return $this->belongsTo(CvVersion::class, 'cv_version_id');
    }

    public function jobDescription(): BelongsTo
    {
        return $this->belongsTo(JobDescription::class);
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(JobDescriptionRevision::class, 'job_description_revision_id');
    }

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(JobDescriptionAnalysis::class, 'analysis_id');
    }
}
