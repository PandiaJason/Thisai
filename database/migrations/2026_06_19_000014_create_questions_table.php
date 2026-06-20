<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->text('question_text');
            $table->text('explanation')->nullable();
            $table->string('type')->default('single_correct'); // QuestionType: single_correct, multiple_correct
            $table->string('difficulty')->default('medium'); // easy, medium, hard
            $table->integer('marks')->default(1);
            $table->decimal('negative_marks', 3, 2)->default(0.00);
            $table->json('tags')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
