<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Enums\ExamAttemptStatus;
use App\Services\TimeAnalyticsService;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    protected TimeAnalyticsService $timeAnalyticsService;

    public function __construct(TimeAnalyticsService $timeAnalyticsService)
    {
        $this->timeAnalyticsService = $timeAnalyticsService;
    }

    public function show(string $sessionToken)
    {
        $attempt = ExamAttempt::where('session_token', $sessionToken)
            ->where('status', '!=', ExamAttemptStatus::IN_PROGRESS->value)
            ->with(['exam.questions.options', 'exam.questions.subject', 'answers.question.options', 'answers.question.subject'])
            ->first();

        if (!$attempt) {
            return redirect()->route('exams.index')->with('error', 'Result not found or exam still in progress.');
        }

        // Verify authorization (only owner can view)
        if ($attempt->user_id !== Auth::id()) {
            abort(403);
        }

        $exam = $attempt->exam;
        $questions = $exam->questions;
        $answers = $attempt->answers->keyBy('question_id');

        // Time Analytics
        $timeAnalytics = $this->timeAnalyticsService->analyzeAttempt($attempt);
        $questionAnalytics = $timeAnalytics['question_analytics'] ?? [];
        $subjectBreakdown = $timeAnalytics['subject_breakdown'] ?? [];
        $timeDistribution = $timeAnalytics['time_distribution'] ?? [];
        $analyticsSummary = $timeAnalytics['summary'] ?? [];

        return view('exams.result', compact(
            'attempt', 'exam', 'questions', 'answers',
            'questionAnalytics', 'subjectBreakdown', 'timeDistribution', 'analyticsSummary'
        ));
    }
}
