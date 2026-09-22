<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Migration: create_templates_table
 *
 * System-managed CV presentation templates.
 * User may only read; only active templates are selectable for Preview/Export.
 *
 * Authority: DATA-STD-002 (ULID PK), DATA-STD-003 (TIMESTAMPTZ)
 * AD-4 (template_version snapshot in cv_exports for reproducibility)
 * AD-10 (MVP export starts at browser print/HTML; template drives rendering)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            // ULID primary key — DATA-STD-002
            $table->char('id', 26)->primary();

            // Display and selection
            $table->string('name');
            $table->string('slug', 100)->unique()->comment('URL-safe identifier e.g. clean-modern');
            $table->string('version', 20)->comment('SemVer e.g. 1.0.0 — pinned in cv_exports for reproducibility');
            $table->boolean('is_active')->default(true)->comment('Only active templates may be used for Preview or Export');

            // Optional display metadata
            $table->text('description')->nullable();
            $table->string('preview_thumbnail_url', 500)->nullable();

            // Rendering configuration
            $table->jsonb('layout_config')->default('{}')->comment('font, colors, sections order, page margins');

            // Timestamps — DATA-STD-003 (TIMESTAMPTZ via useCurrent())
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Composite unique: same slug may exist across versions but slug+version is unique
        Schema::table('templates', function (Blueprint $table) {
            $table->unique(['slug', 'version'], 'templates_slug_version_unique');
            $table->index('is_active', 'templates_is_active_idx');
        });

        // Seed the single MVP template
        DB::table('templates')->insert([
            'id' => Str::ulid(),
            'name' => 'Clean Modern',
            'slug' => 'clean-modern',
            'version' => '1.0.0',
            'is_active' => true,
            'description' => 'A clean, professional single-column CV template.',
            'layout_config' => json_encode([
                'font_family' => 'Inter, sans-serif',
                'primary_color' => '#1e293b',
                'section_order' => ['personal_info', 'summary', 'experience', 'projects', 'education', 'skills', 'certificates', 'languages', 'activities'],
                'page_margin_mm' => 20,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
