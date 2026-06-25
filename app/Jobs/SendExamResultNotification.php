<?php

namespace App\Jobs;

use App\Models\ExamAttempt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SendExamResultNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly ExamAttempt $attempt,
    ) {}

    public function handle(): void
    {
        $attempt = $this->attempt->load('exam', 'user');

        $examTitle = $attempt->exam->title ?? 'Unknown Exam';
        $score = $attempt->score ?? 0;
        $totalMarks = $attempt->total_marks ?? 0;
        $rank = $attempt->rank;
        $percentile = $attempt->percentile;
        $accuracy = $attempt->accuracy;

        // Create in-app notification using Laravel's notifications table schema
        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\ExamResultNotification',
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $attempt->user_id,
            'data' => json_encode([
                'title' => 'Exam Result: ' . $examTitle,
                'message' => "You scored {$score}/{$totalMarks} in {$examTitle}.",
                'exam_id' => $attempt->exam_id,
                'exam_title' => $examTitle,
                'score' => $score,
                'total_marks' => $totalMarks,
                'rank' => $rank,
                'percentile' => $percentile,
                'accuracy' => $accuracy,
                'attempt_id' => $attempt->id,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
