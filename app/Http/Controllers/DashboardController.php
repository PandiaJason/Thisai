<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use App\Models\CurrentAffairs;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\LiveTelecast;
use App\Models\Video;
use App\Services\AnalyticsService;
use App\Services\LeaderboardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected LeaderboardService $leaderboardService;

    public function __construct(AnalyticsService $analyticsService, LeaderboardService $leaderboardService)
    {
        $this->analyticsService = $analyticsService;
        $this->leaderboardService = $leaderboardService;
    }

    public function index()
    {
        $user = Auth::user();
        
        // 1. Enrolled Courses
        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->with(['course.subject', 'course.faculty'])
            ->limit(3)
            ->get();

        // 2. Recent Videos
        $recentVideos = Video::whereHas('progresses', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('completed', false);
        })
        ->with('course')
        ->limit(3)
        ->get();

        // 3. Daily Current Affairs
        $currentAffairs = CurrentAffairs::published()
            ->whereDate('publish_date', Carbon::today())
            ->latest()
            ->limit(4)
            ->get();

        // 4. Upcoming & Available Tests
        $upcomingTests = Exam::published()
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>=', now());
            })
            ->with('subject')
            ->limit(3)
            ->get();

        // 5. Leaderboard Top 5 (Weekly)
        $topStudents = $this->leaderboardService->getLeaderboard('weekly');
        $topStudents = $topStudents ? $topStudents->take(5) : collect();

        // 6. Live Telecast (6-7 AM)
        $liveTelecast = LiveTelecast::liveNow()->first();

        // 7. Student Analytics
        $analytics = $this->analyticsService->getStudentStats($user);

        // 8. Weekly Progress Scores (for chart)
        $weeklyAttempts = ExamAttempt::where('user_id', $user->id)
            ->where('status', '!=', 'in_progress')
            ->where('submitted_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('submitted_at')
            ->get();

        // 9. Gamified Streaks, Badges & Rank Trends
        $profile = $user->studentProfile;
        $badges = \App\Models\Badge::all();
        $unlockedBadgeIds = $user->badges()->pluck('badges.id')->toArray();

        $currentRank = \App\Models\LeaderboardSnapshot::where('user_id', $user->id)
            ->where('period', 'weekly')
            ->orderByDesc('period_date')
            ->value('rank') ?? null;

        $previousRank = \App\Models\LeaderboardSnapshot::where('user_id', $user->id)
            ->where('period', 'weekly')
            ->orderByDesc('period_date')
            ->skip(1)
            ->value('rank') ?? null;

        $rankChange = null;
        if ($currentRank && $previousRank) {
            $rankChange = $previousRank - $currentRank; // Positive indicates rank climbed up
        }

        return view('dashboard', compact(
            'enrollments',
            'recentVideos',
            'currentAffairs',
            'upcomingTests',
            'topStudents',
            'liveTelecast',
            'analytics',
            'weeklyAttempts',
            'profile',
            'badges',
            'unlockedBadgeIds',
            'currentRank',
            'rankChange'
        ));
    }
}
