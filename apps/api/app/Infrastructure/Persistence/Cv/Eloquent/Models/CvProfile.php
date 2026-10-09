<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cv\Eloquent\Models;

use App\Infrastructure\Persistence\Auth\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class CvProfile extends Model
{
    protected $table = 'cv_profiles';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 'user_id', 'title', 'normalized_title', 'revision', 'schema_version', 'document',
    ];

    protected function casts(): array
    {
        return [
            'revision' => 'integer',
            'document' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CvVersion::class, 'source_profile_id');
    }
}
