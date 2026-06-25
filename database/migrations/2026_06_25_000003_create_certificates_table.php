<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('set null');
            $table->foreignId('exam_id')->nullable()->constrained('exams')->onDelete('set null');
            $table->string('certificate_number')->unique();
            $table->timestamp('issued_at');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
