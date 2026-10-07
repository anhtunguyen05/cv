<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class EvidenceAnswer extends Model
{
    protected $table = 'evidence_answers';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'interview_id', 'question_id', 'question_version',
        'area_signal_id', 'outcome', 'answer_original', 'answer_normalized',
        'provenance', 'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        self::updating(static function (): void {
            throw new LogicException('Evidence Answers are immutable.');
        });
        self::deleting(static function (): void {
            throw new LogicException('Evidence Answers are immutable.');
        });
    }

    public function interview(): BelongsTo
    {
        return $this->belongsTo(EvidenceInterview::class, 'interview_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
