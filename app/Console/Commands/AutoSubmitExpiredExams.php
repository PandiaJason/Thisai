<?php

namespace App\Console\Commands;

use App\Enums\ExamAttemptStatus;
use App\Models\ExamAttempt;
use App\Services\ExamService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoSubmitExpiredExams extends Command
{
    protected $signature = 'thisai:auto-submit-expired';

    protected $description = 'Auto-submit exam attempts that have exceeded their allowed duration';

    public function handle(ExamService $examService): int
    {
        $expiredAttempts = ExamAttempt::where('status', ExamAttemptStatus::IN_PROGRESS->value)
            ->with('exam')
            ->get()
            ->filter(function (ExamAttempt $attempt) {
                if (!$attempt->exam || !$attempt->started_at) {
                    return false;
                }

                $deadline = $attempt->started_at->addMinutes($attempt->exam->duration_minutes);

                return Carbon::now()->greaterThan($deadline);
            });

        $count = 0;

        foreach ($expiredAttempts as $attempt) {
            $examService->submitExam($attempt, true);
            $count++;
        }

        if ($count > 0) {
            $this->info("Auto-submitted {$count} expired exam attempt(s).");
        } else {
            $this->info('No expired exam attempts found.');
        }

        return self::SUCCESS;
    }
}
