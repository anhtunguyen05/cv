<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('templates')) {
            return;
        }

        Schema::table('templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('templates', 'supported_snapshot_schema_versions')) {
                $table->jsonb('supported_snapshot_schema_versions')->default('["1.0"]');
            }
        });

        DB::table('templates')->whereNull('supported_snapshot_schema_versions')->update([
            'supported_snapshot_schema_versions' => json_encode(['1.0']),
        ]);

        try {
            Schema::table('templates', function (Blueprint $table): void {
                $table->dropUnique('templates_slug_unique');
            });
        } catch (Throwable) {
            // Existing installations may already have reconciled the index.
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("CREATE OR REPLACE FUNCTION careerfitcv_guard_template_pair() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN IF TG_OP = 'DELETE' THEN RAISE EXCEPTION 'published template pairs are immutable'; END IF; IF OLD.id IS DISTINCT FROM NEW.id OR OLD.version IS DISTINCT FROM NEW.version OR OLD.name IS DISTINCT FROM NEW.name OR OLD.slug IS DISTINCT FROM NEW.slug OR OLD.description IS DISTINCT FROM NEW.description OR OLD.preview_thumbnail_url IS DISTINCT FROM NEW.preview_thumbnail_url OR OLD.layout_config IS DISTINCT FROM NEW.layout_config OR OLD.supported_snapshot_schema_versions IS DISTINCT FROM NEW.supported_snapshot_schema_versions THEN RAISE EXCEPTION 'published template pairs are immutable'; END IF; RETURN NEW; END; $$");
            DB::statement('DROP TRIGGER IF EXISTS templates_immutable_pair ON templates');
            DB::statement('CREATE TRIGGER templates_immutable_pair BEFORE UPDATE OR DELETE ON templates FOR EACH ROW EXECUTE FUNCTION careerfitcv_guard_template_pair()');
        }

        if (Schema::hasTable('cv_exports') && DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cv_exports DROP CONSTRAINT IF EXISTS cv_exports_status_check');
            DB::statement("ALTER TABLE cv_exports ADD CONSTRAINT cv_exports_status_check CHECK (status = 'initiated')");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('templates')) {
            return;
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS templates_immutable_pair ON templates');
            DB::statement('DROP FUNCTION IF EXISTS careerfitcv_guard_template_pair()');
        }

        Schema::table('templates', function (Blueprint $table): void {
            if (Schema::hasColumn('templates', 'supported_snapshot_schema_versions')) {
                $table->dropColumn('supported_snapshot_schema_versions');
            }
        });

        if (! DB::table('templates')->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->exists()) {
            Schema::table('templates', function (Blueprint $table): void {
                $table->unique('slug', 'templates_slug_unique');
            });
        }

        if (Schema::hasTable('cv_exports') && DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE cv_exports DROP CONSTRAINT IF EXISTS cv_exports_status_check');
            DB::statement("ALTER TABLE cv_exports ADD CONSTRAINT cv_exports_status_check CHECK (status IN ('initiated', 'completed', 'failed'))");
        }
    }
};
