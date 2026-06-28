<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SCALABILITY: Adds a composite unique index to enforce data integrity
     * for the lazy answer creation pattern (updateOrCreate).
     *
     * Without this index, concurrent updateOrCreate calls for the same
     * (exam_attempt_id, question_id) pair could create duplicate rows
     * under high load. The unique constraint guarantees exactly one
     * answer row per question per attempt at the database level.
     */
    public function up(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->unique(
                ['exam_attempt_id', 'question_id'],
                'attempt_answers_attempt_question_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->dropUnique('attempt_answers_attempt_question_unique');
        });
    }
};
