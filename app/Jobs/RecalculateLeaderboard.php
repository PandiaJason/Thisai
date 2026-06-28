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
use Illuminate\Support\Facades\Redis;

class RecalculateLeaderboard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * Debounce cooldown in seconds.
     * If a recalculation ran within this window for the same exam,
     * subsequent jobs are skipped to prevent database thrashing.
     */
    protected const DEBOUNCE_SECONDS = 30;

    public function __construct(
        public readonly int $examId,
    ) {}

    /**
     * SCALABILITY: Redis-based debounce lock prevents leaderboard recalculation storms.
     *
     * When 5,000 students submit an exam within a 1-minute window, 5,000
     * RecalculateLeaderboard jobs would flood the queue. Without debouncing,
     * each job queries all attempts, sorts them, and writes rank/percentile
     * for every student — executing the same expensive computation 5,000 times.
     *
     * With a 30-second debounce lock, at most 2-3 recalculations run per minute,
     * reducing database load by ~99.9%.
     */
    public function handle(ExamService $examService, LeaderboardService $leaderboardService): void
    {
        $lockKey = "leaderboard_recalc_lock:{$this->examId}";

        // Attempt to acquire a Redis lock for this exam's recalculation
        $acquired = Redis::set($lockKey, now()->timestamp, 'EX', self::DEBOUNCE_SECONDS, 'NX');

        if (!$acquired) {
            // Another recalculation ran recently — skip this job silently
            return;
        }

        // Recalculate percentile & rank for all attempts of this exam
        $examService->updatePercentilesAndRanks($this->examId);

        // Flush leaderboard cache for all periods so fresh data is served
        $periods = ['daily', 'weekly', 'monthly', 'overall'];
        foreach ($periods as $period) {
            Cache::forget("leaderboard_{$period}_all");
        }
    }
}
