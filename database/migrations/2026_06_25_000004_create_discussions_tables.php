<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('question_id')->nullable()->constrained('questions')->onDelete('set null');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('set null');
            $table->boolean('is_resolved')->default(false);
            $table->integer('upvotes')->default(0);
            $table->integer('reply_count')->default(0);
            $table->timestamps();

            $table->index(['subject_id', 'is_resolved']);
        });

        Schema::create('discussion_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->constrained('discussions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('body');
            $table->boolean('is_accepted')->default(false);
            $table->integer('upvotes')->default(0);
            $table->timestamps();
        });

        Schema::create('discussion_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('votable_id');
            $table->string('votable_type');
            $table->smallInteger('value'); // +1 or -1
            $table->timestamps();

            $table->unique(['user_id', 'votable_id', 'votable_type']);
            $table->index(['votable_id', 'votable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_votes');
        Schema::dropIfExists('discussion_replies');
        Schema::dropIfExists('discussions');
    }
};
