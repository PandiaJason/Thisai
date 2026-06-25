<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Models\User;
use App\Enums\ExamAttemptStatus;
use App\Jobs\RecalculateLeaderboard;
use App\Jobs\SendExamResultNotification;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ExamService
{
    protected ScoreCalculator $scoreCalculator;

    public function __construct(ScoreCalculator $scoreCalculator)
    {
        $this->scoreCalculator = $scoreCalculator;
    }

    public function startExam(User $user, Exam $exam): ExamAttempt
    {
        // Check if student has already started/completed this exam
        $existing = ExamAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Create new attempt
        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'started_at' => Carbon::now(),
            'status' => ExamAttemptStatus::IN_PROGRESS,
            'session_token' => Str::random(40),
        ]);

        // Pre-create attempt answers for all questions in the exam
        $questions = $exam->questions;
        foreach ($questions as $question) {
            AttemptAnswer::create([
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'selected_option_ids' => null,
                'is_correct' => null,
                'marks_obtained' => 0,
                'time_spent_seconds' => 0,
            ]);
        }

        return $attempt;
    }

    public function saveAnswer(ExamAttempt $attempt, Question $question, array $selectedOptionIds, int $timeSpentSeconds = 0): AttemptAnswer
    {
        $answer = AttemptAnswer::where('exam_attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->firstOrCreate([
                'exam_attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ]);

        $answer->selected_option_ids = $selectedOptionIds;
        $answer->time_spent_seconds = $timeSpentSeconds;
        $answer->save();

        return $answer;
    }

    public function submitExam(ExamAttempt $attempt, bool $isAutoSubmit = false): void
    {
        if ($attempt->status !== ExamAttemptStatus::IN_PROGRESS) {
            return;
        }

        $attempt->submitted_at = Carbon::now();
        $attempt->status = $isAutoSubmit ? ExamAttemptStatus::AUTO_SUBMITTED : ExamAttemptStatus::SUBMITTED;

        // Perform calculation
        $result = $this->scoreCalculator->calculate($attempt);

        $attempt->score = $result['score'];
        $attempt->total_marks = $result['total_marks'];
        $attempt->correct_count = $result['correct_count'];
        $attempt->wrong_count = $result['wrong_count'];
        $attempt->unanswered_count = $result['unanswered_count'];
        $attempt->accuracy = $result['accuracy'];
        $attempt->save();

        // Dispatch async jobs for leaderboard recalculation and notification
        RecalculateLeaderboard::dispatch($attempt->exam_id);
        SendExamResultNotification::dispatch($attempt);
    }

    public function updatePercentilesAndRanks(int $examId): void
    {
        $attempts = ExamAttempt::where('exam_id', $examId)
            ->where('status', '!=', ExamAttemptStatus::IN_PROGRESS->value)
            ->orderByDesc('score')
            ->get();

        $totalAttempts = $attempts->count();
        if ($totalAttempts === 0) {
            return;
        }

        foreach ($attempts as $index => $attempt) {
            $rank = $index + 1;
            
            // Percentile calculation: (Total - Rank) / Total * 100
            // If total is 1, percentile is 100%
            $percentile = $totalAttempts > 1 
                ? (($totalAttempts - $rank) / ($totalAttempts - 1)) * 100 
                : 100.00;

            $attempt->rank = $rank;
            $attempt->percentile = round($percentile, 2);
            $attempt->save();
        }
    }

    public function validateSession(string $sessionToken): ?ExamAttempt
    {
        $attempt = ExamAttempt::where('session_token', $sessionToken)
            ->where('status', ExamAttemptStatus::IN_PROGRESS->value)
            ->first();

        if (!$attempt) {
            return null;
        }

        // Check if duration expired
        $started = $attempt->started_at;
        $durationLimit = $attempt->exam->duration_minutes * 60;
        
        if (Carbon::now()->diffInSeconds($started) > ($durationLimit + 30)) { // 30 sec buffer
            // Auto submit
            $this->submitExam($attempt, true);
            return null;
        }

        return $attempt;
    }
}
