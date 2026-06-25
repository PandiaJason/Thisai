<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('question_topics')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('topic_id')->nullable()->after('subject_id')->constrained('question_topics')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
        });

        Schema::dropIfExists('question_topics');
    }
};
