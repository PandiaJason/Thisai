<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_telecasts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('stream_url'); // Bunny Stream embed, YouTube live, or external HLS
            $table->string('thumbnail_url')->nullable();
            $table->date('scheduled_date');
            $table->time('start_time')->default('06:00:00');
            $table->time('end_time')->default('07:00:00');
            $table->timestamp('auto_delete_at')->nullable(); // Defaults to 6 PM (18:00) on the scheduled_date
            $table->string('status')->default('scheduled'); // scheduled, live, ended, deleted
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['scheduled_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_telecasts');
    }
};
