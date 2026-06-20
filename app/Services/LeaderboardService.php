<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\LeaderboardSnapshot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    protected int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = config('thisai.leaderboard.cache_ttl', 3600);
    }

    public function getLeaderboard(string $period = 'overall', ?int $subjectId = null)
    {
        $cacheKey = "leaderboard_{$period}_" . ($subjectId ?? 'all');

        $data = Cache::remember($cacheKey, $this->cacheTtl, function () use ($period, $subjectId) {
            $query = ExamAttempt::select(
                'user_id',
                DB::raw('SUM(score) as total_score'),
                DB::raw('COUNT(id) as total_exams'),
                DB::raw('AVG(accuracy) as average_accuracy')
            )
            ->where('status', '!=', 'in_progress');

            // Apply period filters
            if ($period === 'daily') {
                $query->whereDate('submitted_at', Carbon::today());
            } elseif ($period === 'weekly') {
                $query->where('submitted_at', '>=', Carbon::now()->startOfWeek());
            } elseif ($period === 'monthly') {
                $query->where('submitted_at', '>=', Carbon::now()->startOfMonth());
            }

            // Apply subject filter if requested
            if ($subjectId) {
                $query->whereHas('exam', function ($q) use ($subjectId) {
                    $q->where('subject_id', $subjectId);
                });
            }

            $rows = $query->groupBy('user_id')
                ->orderByDesc('total_score')
                ->limit(100)
                ->with('user.studentProfile')
                ->get();

            $list = [];
            foreach ($rows as $index => $row) {
                $list[] = [
                    'user_id' => $row->user_id,
                    'total_score' => (float) $row->total_score,
                    'total_exams' => (int) $row->total_exams,
                    'average_accuracy' => (float) $row->average_accuracy,
                    'rank' => $index + 1,
                    'user_name' => $row->user->name ?? 'Unknown Student',
                    'user_avatar' => $row->user->avatar ?? null,
                    'target_exam' => $row->user->studentProfile->target_exam ?? null,
                    'target_year' => $row->user->studentProfile->target_year ?? null,
                ];
            }
            return $list;
        });

        return collect($data)->map(function ($item) {
            $profile = new \stdClass();
            $profile->target_exam = $item['target_exam'] ?? null;
            $profile->target_year = $item['target_year'] ?? null;

            $user = new \stdClass();
            $user->name = $item['user_name'];
            $user->avatar = $item['user_avatar'];
            $user->studentProfile = $profile;

            $student = new \stdClass();
            $student->user_id = $item['user_id'];
            $student->total_score = $item['total_score'];
            $student->total_exams = $item['total_exams'];
            $student->average_accuracy = $item['average_accuracy'];
            $student->rank = $item['rank'];
            $student->user = $user;

            return $student;
        });
    }

    public function generateSnapshots(string $period): void
    {
        $date = Carbon::today();
        
        // Fetch leaderboard ranking lists
        $results = $this->getLeaderboard($period);

        DB::transaction(function () use ($results, $period, $date) {
            foreach ($results as $row) {
                LeaderboardSnapshot::updateOrCreate(
                    [
                        'user_id' => $row->user_id,
                        'period' => $period,
                        'period_date' => $date->format('Y-m-d'),
                    ],
                    [
                        'total_score' => $row->total_score,
                        'total_exams' => $row->total_exams,
                        'accuracy' => $row->average_accuracy,
                        'rank' => $row->rank,
                    ]
                );
            }
        });
    }
}
