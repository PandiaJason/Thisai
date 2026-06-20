<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Enums\ExamAttemptStatus;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function show(string $sessionToken)
    {
        $attempt = ExamAttempt::where('session_token', $sessionToken)
            ->where('status', '!=', ExamAttemptStatus::IN_PROGRESS->value)
            ->with(['exam.questions.options', 'answers.question.options'])
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

        return view('exams.result', compact('attempt', 'exam', 'questions', 'answers'));
    }
}
