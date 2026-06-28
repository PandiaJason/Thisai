<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamService;
use App\Enums\ExamAttemptStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    protected ExamService $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    public function index(Request $request)
    {
        $query = Exam::published()->with('subject');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $exams = $query->paginate(12);

        return view('exams.index', compact('exams'));
    }

    public function start(Request $request, Exam $exam)
    {
        $user = Auth::user();

        // Check if student has an in-progress attempt
        $attempt = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', ExamAttemptStatus::IN_PROGRESS->value)
            ->first();

        if (!$attempt) {
            // Check if they reached max attempts
            $completedAttempts = ExamAttempt::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->where('status', '!=', ExamAttemptStatus::IN_PROGRESS->value)
                ->count();

            $maxAllowed = $exam->max_attempts ?? config('thisai.exam.max_attempts_per_exam', 1);

            if ($completedAttempts >= $maxAllowed) {
                $lastAttempt = ExamAttempt::where('user_id', $user->id)
                    ->where('exam_id', $exam->id)
                    ->latest()
                    ->first();
                
                if ($lastAttempt) {
                    return redirect()->route('results.show', $lastAttempt->session_token)
                        ->with('info', 'You have reached the maximum number of attempts allowed for this exam.');
                }
                
                return redirect()->route('exams.index')
                    ->with('error', 'You have reached the maximum number of attempts allowed for this exam.');
            }

            // If exam has an activation key, verify passcode
            if ($exam->activation_key) {
                $keyInput = $request->input('activation_key');
                if ($keyInput !== $exam->activation_key) {
                    return redirect()->route('exams.verify', $exam->slug)
                        ->with('error', $keyInput ? 'Incorrect activation key. Please try again.' : null);
                }
            }

            // Check if exam has questions
            if ($exam->questions()->count() === 0) {
                return redirect()->route('exams.index')->with('error', 'This exam does not have any questions yet.');
            }

            // Start the new attempt
            $attempt = $this->examService->startExam($user, $exam);
        }

        return redirect()->route('exams.take', $attempt->session_token);
    }

    public function verify(Exam $exam)
    {
        $user = Auth::user();
        
        $attempt = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', ExamAttemptStatus::IN_PROGRESS->value)
            ->first();

        if ($attempt) {
            return redirect()->route('exams.take', $attempt->session_token);
        }

        // Check if they reached max attempts
        $completedAttempts = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', '!=', ExamAttemptStatus::IN_PROGRESS->value)
            ->count();

        $maxAllowed = $exam->max_attempts ?? config('thisai.exam.max_attempts_per_exam', 1);

        if ($completedAttempts >= $maxAllowed) {
            $lastAttempt = ExamAttempt::where('user_id', $user->id)
                ->where('exam_id', $exam->id)
                ->latest()
                ->first();
            
            if ($lastAttempt) {
                return redirect()->route('results.show', $lastAttempt->session_token)
                    ->with('info', 'You have reached the maximum number of attempts allowed for this exam.');
            }
            
            return redirect()->route('exams.index')
                ->with('error', 'You have reached the maximum number of attempts allowed for this exam.');
        }

        return view('exams.verify', compact('exam'));
    }

    public function take(string $sessionToken)
    {
        $attempt = ExamAttempt::where('session_token', $sessionToken)
            ->where('status', ExamAttemptStatus::IN_PROGRESS->value)
            ->with(['exam.questions.options', 'answers'])
            ->first();

        if (!$attempt) {
            return redirect()->route('exams.index')->with('error', 'Invalid or expired exam session.');
        }

        $exam = $attempt->exam;
        $questions = $exam->questions;
        
        if ($questions->isEmpty()) {
            return redirect()->route('exams.index')->with('error', 'This exam does not have any questions yet.');
        }
        
        // With lazy answer creation, the answers collection may be empty or partial.
        // Key existing answers by question_id for the frontend to use.
        $answers = $attempt->answers->keyBy('question_id');

        return view('exams.take', compact('attempt', 'exam', 'questions', 'answers'));
    }

    public function submit(string $sessionToken)
    {
        $attempt = ExamAttempt::where('session_token', $sessionToken)
            ->where('status', ExamAttemptStatus::IN_PROGRESS->value)
            ->first();

        if (!$attempt) {
            return redirect()->route('exams.index')->with('error', 'Exam attempt session not found.');
        }

        $this->examService->submitExam($attempt);

        return redirect()->route('results.show', $attempt->session_token)
            ->with('success', 'Exam submitted successfully!');
    }
}
