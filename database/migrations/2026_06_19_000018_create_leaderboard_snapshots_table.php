<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('period'); // daily, weekly, monthly, overall
            $table->date('period_date')->index();
            $table->decimal('total_score', 12, 2)->default(0.00);
            $table->integer('total_exams')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0.00);
            $table->integer('rank')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'period', 'period_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_snapshots');
    }
};
