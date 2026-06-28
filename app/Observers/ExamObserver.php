<?php

namespace App\Observers;

use App\Models\Exam;
use App\Services\ScoreCalculator;

/**
 * SCALABILITY: Automatically manages the Redis answer key cache.
 *
 * When an exam is saved (e.g., published, updated), this observer
 * checks if the exam is published and caches the correct answer key
 * in Redis. This ensures the ScoreCalculator can grade submissions
 * using in-memory Redis reads instead of database queries.
 *
 * If an exam is unpublished or deleted, the cache is invalidated
 * to prevent stale data from being used.
 */
class ExamObserver
{
    /**
     * After an exam is saved (created or updated), cache the answer key
     * if the exam is published. Invalidate if unpublished.
     */
    public function saved(Exam $exam): void
    {
        if ($exam->is_published) {
            // Only cache if the exam has questions
            if ($exam->questions()->count() > 0) {
                ScoreCalculator::cacheAnswerKey($exam);
            }
        } else {
            ScoreCalculator::invalidateAnswerKey($exam->id);
        }
    }

    /**
     * When an exam is deleted, clean up the cached answer key.
     */
    public function deleted(Exam $exam): void
    {
        ScoreCalculator::invalidateAnswerKey($exam->id);
    }
}
