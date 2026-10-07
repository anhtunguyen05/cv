<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Template extends Model
{
    protected $table = 'templates';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'version',
        'is_active',
        'description',
        'preview_thumbnail_url',
        'layout_config',
        'supported_snapshot_schema_versions',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'layout_config' => 'array',
            'supported_snapshot_schema_versions' => 'array',
        ];
    }
}
