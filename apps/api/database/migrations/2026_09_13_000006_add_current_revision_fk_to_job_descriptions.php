<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration: add_current_revision_fk_to_job_descriptions
 *
 * Step 3 of 3 for the circular FK resolution:
 *   Step 1 → job_descriptions created with current_revision_id column but NO FK
 *   Step 2 → job_description_revisions created (depends on job_descriptions)
 *   Step 3 → this file adds the FK now that the target table exists
 *
 * Why raw SQL instead of Schema::table + Blueprint?
 * Laravel's Blueprint does not support DEFERRABLE foreign keys.
 * The DEFERRABLE INITIALLY DEFERRED option is required because:
 *   - When creating a new JD + first revision within a transaction,
 *     the revision does not exist yet when the JD row is first inserted.
 *   - With DEFERRABLE INITIALLY DEFERRED, the FK check is postponed
 *     until COMMIT, allowing the transaction to insert both rows safely.
 *
 * Authority: AD-11 (current revision pointer), DATA-STD-008
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add DEFERRABLE FK — not expressible via Blueprint
        DB::statement(<<<SQL
            ALTER TABLE job_descriptions
            ADD CONSTRAINT job_descriptions_current_revision_id_fk
            FOREIGN KEY (current_revision_id)
            REFERENCES job_description_revisions (id)
            ON DELETE RESTRICT
            DEFERRABLE INITIALLY DEFERRED
        SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE job_descriptions DROP CONSTRAINT IF EXISTS job_descriptions_current_revision_id_fk');
    }
};
