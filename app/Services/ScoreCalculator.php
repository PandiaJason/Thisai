<?php

namespace App\Services;

use App\Models\AttemptAnswer;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Enums\QuestionType;
use Illuminate\Support\Facades\Redis;

class ScoreCalculator
{
    /**
     * Calculate scores for an exam attempt.
     *
     * SCALABILITY: Uses Redis-cached answer keys when available.
     * When 5,000 students submit simultaneously, instead of each submission
     * loading all questions + options from PostgreSQL (N+1 joins), the correct
     * answer map is read from Redis in O(1) — a single HGETALL command.
     *
     * For unanswered questions (lazy creation means no AttemptAnswer row exists),
     * the system counts them by comparing the total question count against
     * the number of AttemptAnswer rows that were actually created.
     */
    public function calculate(ExamAttempt $attempt): array
    {
        $exam = $attempt->exam;
        $examId = $exam->id;

        // Try to load the cached answer key from Redis
        $cachedKey = $this->getCachedAnswerKey($examId);

        if ($cachedKey) {
            return $this->calculateWithCache($attempt, $exam, $cachedKey);
        }

        // Fallback: load from database (first submission or cache miss)
        return $this->calculateFromDatabase($attempt, $exam);
    }

    /**
     * Cache the correct answer key for an exam in Redis.
     * Called when an exam is published or updated.
     *
     * Structure: exam:{id}:answer_key => JSON hash of question_id => {
     *   correct_ids: [int], type: string, marks: float, negative_marks: float
     * }
     */
    public static function cacheAnswerKey(Exam $exam): void
    {
        $questions = $exam->questions()->with('options')->get();
        $answerKey = [];

        foreach ($questions as $question) {
            $correctIds = $question->options
                ->where('is_correct', true)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->sort()
                ->values()
                ->toArray();

            $answerKey[$question->id] = [
                'correct_ids' => $correctIds,
                'type' => $question->type->value,
                'marks' => (float) $question->marks,
                'negative_marks' => (float) ($question->negative_marks ?? 0),
            ];
        }

        // Cache for 24 hours — will be invalidated when exam is updated
        Redis::setex(
            "exam:{$exam->id}:answer_key",
            86400,
            json_encode($answerKey)
        );
    }

    /**
     * Invalidate the cached answer key when questions change.
     */
    public static function invalidateAnswerKey(int $examId): void
    {
        Redis::del("exam:{$examId}:answer_key");
    }

    /**
     * Retrieve the cached answer key from Redis.
     */
    protected function getCachedAnswerKey(int $examId): ?array
    {
        $cached = Redis::get("exam:{$examId}:answer_key");
        if (!$cached) {
            return null;
        }
        return json_decode($cached, true);
    }

    /**
     * Grade using Redis-cached answer key — zero database reads for question/option data.
     */
    protected function calculateWithCache(ExamAttempt $attempt, Exam $exam, array $answerKey): array
    {
        // Load only the student's actual answers (lazy-created rows only)
        $answers = $attempt->answers()->get();

        $correctCount = 0;
        $wrongCount = 0;
        $totalScore = 0;
        $totalPossibleMarks = 0;
        $answeredQuestionIds = [];

        // Calculate total possible marks from the cached key
        foreach ($answerKey as $qId => $qData) {
            $totalPossibleMarks += $qData['marks'];
        }

        // Grade each submitted answer
        foreach ($answers as $answer) {
            $qId = $answer->question_id;
            $answeredQuestionIds[] = $qId;

            if (!isset($answerKey[$qId])) {
                continue;
            }

            $qData = $answerKey[$qId];
            $selectedIds = $answer->selected_option_ids;

            if (empty($selectedIds)) {
                // Unanswered — row exists but no selection
                $answer->is_correct = null;
                $answer->marks_obtained = 0.00;
                $answer->save();
                continue;
            }

            $selectedIds = array_map('intval', (array) $selectedIds);
            $correctOptionIds = $qData['correct_ids'];
            $isCorrect = false;

            if ($qData['type'] === QuestionType::SINGLE_CORRECT->value) {
                $selectedId = reset($selectedIds);
                $isCorrect = in_array($selectedId, $correctOptionIds, true);
            } else {
                sort($selectedIds);
                $isCorrect = ($selectedIds === $correctOptionIds);
            }

            if ($isCorrect) {
                $correctCount++;
                $answer->is_correct = true;
                $answer->marks_obtained = $qData['marks'];
                $totalScore += $qData['marks'];
            } else {
                $wrongCount++;
                $answer->is_correct = false;

                $negMarks = $qData['negative_marks'];
                if ($negMarks <= 0 && $exam->negative_marking > 0) {
                    $negMarks = $qData['marks'] * $exam->negative_marking;
                }

                $answer->marks_obtained = -$negMarks;
                $totalScore -= $negMarks;
            }

            $answer->save();
        }

        // Unanswered = total questions minus answered (both correct + wrong + empty selections)
        $totalQuestions = count($answerKey);
        $unansweredCount = $totalQuestions - ($correctCount + $wrongCount);
        // Also count answers with null/empty selections as unanswered
        $emptySelections = $answers->filter(fn($a) => empty($a->selected_option_ids))->count();
        $unansweredCount = $totalQuestions - $correctCount - $wrongCount;

        $attempted = $correctCount + $wrongCount;
        $accuracy = $attempted > 0 ? ($correctCount / $attempted) * 100 : 0.00;

        return [
            'score' => max(0, $totalScore),
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
            'unanswered_count' => $unansweredCount,
            'accuracy' => round($accuracy, 2),
            'total_marks' => $totalPossibleMarks,
        ];
    }

    /**
     * Fallback: Grade by loading questions and options from the database.
     * Also caches the answer key for subsequent submissions.
     */
    protected function calculateFromDatabase(ExamAttempt $attempt, Exam $exam): array
    {
        // Load all questions with options for this exam
        $questions = $exam->questions()->with('options')->get();

        // Build and cache the answer key for future submissions
        $answerKey = [];
        foreach ($questions as $question) {
            $correctIds = $question->options
                ->where('is_correct', true)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->sort()
                ->values()
                ->toArray();

            $answerKey[$question->id] = [
                'correct_ids' => $correctIds,
                'type' => $question->type->value,
                'marks' => (float) $question->marks,
                'negative_marks' => (float) ($question->negative_marks ?? 0),
            ];
        }

        // Cache for future submissions
        Redis::setex(
            "exam:{$exam->id}:answer_key",
            86400,
            json_encode($answerKey)
        );

        // Load the student's answers
        $answers = $attempt->answers()->get();

        $correctCount = 0;
        $wrongCount = 0;
        $totalScore = 0;
        $totalPossibleMarks = 0;

        foreach ($answerKey as $qId => $qData) {
            $totalPossibleMarks += $qData['marks'];
        }

        foreach ($answers as $answer) {
            $qId = $answer->question_id;
            if (!isset($answerKey[$qId])) {
                continue;
            }

            $qData = $answerKey[$qId];
            $selectedIds = $answer->selected_option_ids;

            if (empty($selectedIds)) {
                $answer->is_correct = null;
                $answer->marks_obtained = 0.00;
                $answer->save();
                continue;
            }

            $correctOptionIds = $qData['correct_ids'];
            $selectedIds = array_map('intval', (array) $selectedIds);
            $isCorrect = false;

            if ($qData['type'] === QuestionType::SINGLE_CORRECT->value) {
                $selectedId = reset($selectedIds);
                $isCorrect = in_array($selectedId, $correctOptionIds, true);
            } else {
                sort($selectedIds);
                $isCorrect = ($selectedIds === $correctOptionIds);
            }

            if ($isCorrect) {
                $correctCount++;
                $answer->is_correct = true;
                $answer->marks_obtained = $qData['marks'];
                $totalScore += $qData['marks'];
            } else {
                $wrongCount++;
                $answer->is_correct = false;

                $negMarks = $qData['negative_marks'];
                if ($negMarks <= 0 && $exam->negative_marking > 0) {
                    $negMarks = $qData['marks'] * $exam->negative_marking;
                }

                $answer->marks_obtained = -$negMarks;
                $totalScore -= $negMarks;
            }

            $answer->save();
        }

        $totalQuestions = count($answerKey);
        $unansweredCount = $totalQuestions - $correctCount - $wrongCount;

        $attempted = $correctCount + $wrongCount;
        $accuracy = $attempted > 0 ? ($correctCount / $attempted) * 100 : 0.00;

        return [
            'score' => max(0, $totalScore),
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
            'unanswered_count' => $unansweredCount,
            'accuracy' => round($accuracy, 2),
            'total_marks' => $totalPossibleMarks,
        ];
    }
}
