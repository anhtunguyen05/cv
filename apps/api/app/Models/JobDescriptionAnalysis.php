<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class JobDescriptionAnalysis extends Model
{
    protected $table = 'job_description_analyses';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'job_description_revision_id', 'user_id', 'status', 'analysis_schema_version',
        'analysis_rule_version', 'extracted_role', 'required_skills', 'nice_to_have_skills',
        'responsibilities', 'keywords', 'seniority', 'soft_skills', 'domain_context',
        'seniority_state', 'error_message', 'completed_at', 'created_at', 'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'extracted_role' => 'array', 'required_skills' => 'array', 'nice_to_have_skills' => 'array',
            'responsibilities' => 'array', 'keywords' => 'array', 'soft_skills' => 'array',
            'domain_context' => 'array', 'completed_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime', 'updated_at' => 'immutable_datetime',
        ];
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(JobDescriptionRevision::class, 'job_description_revision_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
