<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class MatchReport extends Model
{
    protected $table = 'match_reports';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'cv_version_id', 'job_description_id', 'job_description_revision_id',
        'analysis_id', 'analysis_rule_version', 'matching_rule_version', 'report_schema_version',
        'overall_score', 'matched_skills', 'missing_skills', 'weak_evidence', 'recommendations', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_score' => 'decimal:2', 'matched_skills' => 'array', 'missing_skills' => 'array',
            'weak_evidence' => 'array', 'recommendations' => 'array', 'created_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function evidenceInterviews(): HasMany
    {
        return $this->hasMany(EvidenceInterview::class);
    }
}
