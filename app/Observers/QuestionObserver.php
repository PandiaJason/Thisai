<?php

namespace App\Observers;

use App\Models\Question;
use App\Services\ScoreCalculator;

/**
 * SCALABILITY: Invalidates the Redis answer key cache when questions change.
 *
 * If an admin adds, edits, or deletes a question (or its options change),
 * the cached answer key for that exam must be rebuilt to prevent stale
 * grading data from producing incorrect scores.
 */
class QuestionObserver
{
    public function saved(Question $question): void
    {
        if ($question->exam_id) {
            ScoreCalculator::invalidateAnswerKey($question->exam_id);

            // Re-cache if the exam is published
            $exam = $question->exam;
            if ($exam && $exam->is_published) {
                ScoreCalculator::cacheAnswerKey($exam);
            }
        }
    }

    public function deleted(Question $question): void
    {
        if ($question->exam_id) {
            ScoreCalculator::invalidateAnswerKey($question->exam_id);

            // Re-cache if the exam is still published
            $exam = $question->exam;
            if ($exam && $exam->is_published) {
                ScoreCalculator::cacheAnswerKey($exam);
            }
        }
    }
}
