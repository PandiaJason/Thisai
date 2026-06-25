<?php

namespace App\Jobs;

use App\Services\ExamService;
use App\Services\LeaderboardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RecalculateLeaderboard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly int $examId,
    ) {}

    public function handle(ExamService $examService, LeaderboardService $leaderboardService): void
    {
        // Recalculate percentile & rank for all attempts of this exam
        $examService->updatePercentilesAndRanks($this->examId);

        // Flush leaderboard cache for all periods so fresh data is served
        $periods = ['daily', 'weekly', 'monthly', 'overall'];
        foreach ($periods as $period) {
            Cache::forget("leaderboard_{$period}_all");
        }
    }
}
