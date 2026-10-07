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
        Schema::create('evidence_answers', function (Blueprint $table): void {
            $table->char('id', 26)->primary();
            $table->unsignedBigInteger('user_id');
            $table->char('interview_id', 26);
            $table->char('question_id', 26);
            $table->string('question_version', 20);
            $table->string('area_signal_id', 160);
            $table->string('outcome', 24);
            $table->text('answer_original')->nullable();
            $table->text('answer_normalized')->nullable();
            $table->string('provenance', 20)->default('user');
            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->name('ea_user_id_fk');
            $table->foreign('interview_id')->references('id')->on('evidence_interviews')->onDelete('restrict')->name('ea_interview_id_fk');
            $table->unique(['interview_id', 'question_id'], 'ea_interview_question_unique');
            $table->index(['user_id', 'created_at'], 'ea_owner_created_idx');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE evidence_answers ADD CONSTRAINT ea_outcome_valid CHECK (outcome IN ('answer', 'cannot_provide'))");
            DB::statement("ALTER TABLE evidence_answers ADD CONSTRAINT ea_provenance_valid CHECK (provenance = 'user')");
            DB::statement("ALTER TABLE evidence_answers ADD CONSTRAINT ea_answer_shape_valid CHECK ((outcome = 'answer' AND answer_original IS NOT NULL AND answer_normalized IS NOT NULL) OR (outcome = 'cannot_provide' AND answer_original IS NULL AND answer_normalized IS NULL))");
            DB::statement(<<<'SQL'
                CREATE OR REPLACE FUNCTION reject_evidence_answer_mutation()
                RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    RAISE EXCEPTION 'evidence_answers are immutable';
                END;
                $$
            SQL);
            DB::statement(<<<'SQL'
                CREATE TRIGGER evidence_answers_immutable_trigger
                BEFORE UPDATE OR DELETE ON evidence_answers
                FOR EACH ROW EXECUTE FUNCTION reject_evidence_answer_mutation()
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP TRIGGER IF EXISTS evidence_answers_immutable_trigger ON evidence_answers');
            DB::statement('DROP FUNCTION IF EXISTS reject_evidence_answer_mutation()');
        }
        Schema::dropIfExists('evidence_answers');
    }
};
