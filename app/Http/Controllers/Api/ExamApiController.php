<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Services\ExamService;
use App\Enums\ExamAttemptStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamApiController extends Controller
{
    protected ExamService $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    public function saveAnswer(Request $request)
    {
        $request->validate([
            'session_token' => ['required', 'string'],
            'question_id' => ['required', 'exists:questions,id'],
            'selected_option_ids' => ['nullable', 'array'],
            'time_spent_seconds' => ['required', 'integer', 'min:0'],
        ]);

        $attempt = $this->examService->validateSession($request->session_token);

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Exam session has expired or is invalid.'
            ], 403);
        }

        $question = Question::findOrFail($request->question_id);
        $selectedOptionIds = $request->selected_option_ids ?? [];

        $answer = $this->examService->saveAnswer(
            $attempt,
            $question,
            $selectedOptionIds,
            $request->time_spent_seconds
        );

        return response()->json([
            'success' => true,
            'message' => 'Answer saved successfully.'
        ]);
    }

    public function getTimeRemaining(Request $request)
    {
        $request->validate([
            'session_token' => ['required', 'string'],
        ]);

        $attempt = ExamAttempt::where('session_token', $request->session_token)
            ->where('status', ExamAttemptStatus::IN_PROGRESS->value)
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'time_remaining_seconds' => 0
            ]);
        }

        $elapsedSeconds = Carbon::now()->diffInSeconds($attempt->started_at);
        $durationSeconds = $attempt->exam->duration_minutes * 60;
        $remaining = max(0, $durationSeconds - $elapsedSeconds);

        return response()->json([
            'success' => true,
            'time_remaining_seconds' => $remaining
        ]);
    }
}
