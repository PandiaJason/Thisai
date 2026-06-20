<?php

namespace App\Services;

use App\Models\AttemptAnswer;
use App\Models\ExamAttempt;
use App\Enums\QuestionType;

class ScoreCalculator
{
    public function calculate(ExamAttempt $attempt): array
    {
        $attempt->load('answers.question.options');

        $correctCount = 0;
        $wrongCount = 0;
        $unansweredCount = 0;
        $totalScore = 0;
        $totalPossibleMarks = 0;

        foreach ($attempt->answers as $answer) {
            $question = $answer->question;
            $selectedIds = $answer->selected_option_ids;

            // Track total marks possible in the exam
            $totalPossibleMarks += $question->marks;

            if (empty($selectedIds)) {
                $unansweredCount++;
                $answer->is_correct = null;
                $answer->marks_obtained = 0.00;
                $answer->save();
                continue;
            }

            // Get correct options and selected options cast to integers
            $correctOptionIds = array_map('intval', $question->options->where('is_correct', true)->pluck('id')->toArray());
            $selectedIds = array_map('intval', (array)$selectedIds);

            $isCorrect = false;

            if ($question->type === QuestionType::SINGLE_CORRECT) {
                // For single correct, there should be exactly 1 selection
                $selectedId = reset($selectedIds);
                $isCorrect = in_array($selectedId, $correctOptionIds, true);
            } else {
                // For multiple correct, selections must match correct options exactly (same values, regardless of order)
                sort($selectedIds);
                sort($correctOptionIds);
                $isCorrect = ($selectedIds === $correctOptionIds);
            }

            if ($isCorrect) {
                $correctCount++;
                $answer->is_correct = true;
                $answer->marks_obtained = $question->marks;
                $totalScore += $question->marks;
            } else {
                $wrongCount++;
                $answer->is_correct = false;
                
                // Calculate negative marks based on question configuration or exam configuration
                $negMarks = $question->negative_marks;
                if ($negMarks <= 0 && $attempt->exam->negative_marking > 0) {
                    $negMarks = $question->marks * $attempt->exam->negative_marking;
                }
                
                $answer->marks_obtained = -$negMarks;
                $totalScore -= $negMarks;
            }

            $answer->save();
        }

        // Accuracy is correct / total attempted
        $attempted = $correctCount + $wrongCount;
        $accuracy = $attempted > 0 ? ($correctCount / $attempted) * 100 : 0.00;

        return [
            'score' => max(0, $totalScore), // score cannot be negative for final display
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
            'unanswered_count' => $unansweredCount,
            'accuracy' => round($accuracy, 2),
            'total_marks' => $totalPossibleMarks,
        ];
    }
}
