<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index()
    {
        $user = Auth::user();

        $scoreTrend = $this->analyticsService->getScoreTrend($user);
        $subjectHeatmap = $this->analyticsService->getSubjectHeatmap($user);
        $timeManagement = $this->analyticsService->getTimeManagement($user);
        $batchComparison = $this->analyticsService->getBatchComparison($user);
        $weakAreas = $this->analyticsService->getWeakAreas($user);
        $improvement = $this->analyticsService->getImprovementTracker($user);
        $stats = $this->analyticsService->getStudentStats($user);

        return view('analytics.index', compact(
            'scoreTrend',
            'subjectHeatmap',
            'timeManagement',
            'batchComparison',
            'weakAreas',
            'improvement',
            'stats'
        ));
    }
}
