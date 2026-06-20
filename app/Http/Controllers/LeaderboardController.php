<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use App\Models\Subject;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    protected LeaderboardService $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    public function index(Request $request)
    {
        $subjects = Subject::active()->get();
        
        $period = $request->input('period', 'weekly'); // daily, weekly, monthly, overall
        $subjectId = $request->filled('subject') ? (int) $request->subject : null;

        $rankings = $this->leaderboardService->getLeaderboard($period, $subjectId);

        // Fetch top 3 specifically for podium layout
        $podium = $rankings->take(3);
        $remaining = $rankings->slice(3);

        return view('leaderboard.index', compact('podium', 'remaining', 'period', 'subjects'));
    }
}
