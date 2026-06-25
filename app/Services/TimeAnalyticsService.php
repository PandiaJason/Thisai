<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Enums\ExamAttemptStatus;
use Illuminate\Support\Collection;

class TimeAnalyticsService
{
    protected int $quickThreshold = 30;
    protected int $slowThreshold = 90;

    public function analyzeAttempt(ExamAttempt $attempt): array
    {
        $attempt->load('answers.question.subject', 'answers.question.topic');

        $questionAnalytics = [];
        $subjectData = [];
        $timeDistribution = [
            'quick_correct' => 0, 'quick_wrong' => 0,
            'medium_correct' => 0, 'medium_wrong' => 0,
            'slow_correct' => 0, 'slow_wrong' => 0,
        ];

        foreach ($attempt->answers as $answer) {
            $question = $answer->question;
            if (!$question) {
                continue;
            }

            $timeSpent = $answer->time_spent_seconds ?? 0;
            $timeCategory = $this->categorizeTime($timeSpent);
            $result = $this->categorizeResult($answer->is_correct, $answer->selected_option_ids);
            $insight = $this->getInsight($timeCategory, $result);

            $subjectName = $question->subject?->name ?? 'General';
            $topicName = $question->topic?->name ?? null;
            $subjectId = $question->subject_id ?? 0;

            $questionAnalytics[$question->id] = [
                'time_spent' => $timeSpent,
                'time_category' => $timeCategory,
                'result' => $result,
                'insight_key' => $insight['key'],
                'insight_label' => $insight['label'],
                'insight_color' => $insight['color'],
                'insight_icon' => $insight['icon'],
                'subject_name' => $subjectName,
                'topic_name' => $topicName,
            ];

            // Aggregate subject data
            if (!isset($subjectData[$subjectId])) {
                $subjectData[$subjectId] = [
                    'name' => $subjectName,
                    'correct' => 0,
                    'wrong' => 0,
                    'unanswered' => 0,
                    'total' => 0,
                    'total_time' => 0,
                ];
            }
            $subjectData[$subjectId]['total']++;
            $subjectData[$subjectId]['total_time'] += $timeSpent;
            $subjectData[$subjectId][$result]++;

            // Time distribution
            if ($result !== 'unanswered') {
                $key = $timeCategory . '_' . $result;
                if (isset($timeDistribution[$key])) {
                    $timeDistribution[$key]++;
                }
            }
        }

        // Compute subject breakdown with accuracy and avg time
        $subjectBreakdown = [];
        foreach ($subjectData as $subjectId => $data) {
            $attempted = $data['correct'] + $data['wrong'];
            $subjectBreakdown[$subjectId] = [
                'name' => $data['name'],
                'correct' => $data['correct'],
                'wrong' => $data['wrong'],
                'unanswered' => $data['unanswered'],
                'total' => $data['total'],
                'accuracy' => $attempted > 0 ? round(($data['correct'] / $attempted) * 100, 1) : 0,
                'avg_time' => $data['total'] > 0 ? round($data['total_time'] / $data['total'], 1) : 0,
            ];
        }

        // Summary
        $summary = $this->buildSummary($subjectBreakdown);

        return [
            'question_analytics' => $questionAnalytics,
            'subject_breakdown' => $subjectBreakdown,
            'time_distribution' => $timeDistribution,
            'summary' => $summary,
        ];
    }

    protected function categorizeTime(int $seconds): string
    {
        if ($seconds < $this->quickThreshold) {
            return 'quick';
        }
        if ($seconds <= $this->slowThreshold) {
            return 'medium';
        }
        return 'slow';
    }

    protected function categorizeResult(?bool $isCorrect, mixed $selectedOptionIds): string
    {
        if ($isCorrect === null || empty($selectedOptionIds)) {
            return 'unanswered';
        }
        return $isCorrect ? 'correct' : 'wrong';
    }

    protected function getInsight(string $timeCategory, string $result): array
    {
        if ($result === 'unanswered') {
            return [
                'key' => 'skipped',
                'label' => 'Skipped',
                'color' => 'text-slate-600 bg-slate-50 border-slate-200',
                'icon' => 'minus-circle',
            ];
        }

        $matrix = [
            'quick' => [
                'correct' => [
                    'key' => 'quick_solve',
                    'label' => 'Quick Solve',
                    'color' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                    'icon' => 'bolt',
                ],
                'wrong' => [
                    'key' => 'careless_error',
                    'label' => 'Careless Error',
                    'color' => 'text-red-700 bg-red-50 border-red-200',
                    'icon' => 'exclamation-triangle',
                ],
            ],
            'medium' => [
                'correct' => [
                    'key' => 'well_solved',
                    'label' => 'Well Solved',
                    'color' => 'text-blue-700 bg-blue-50 border-blue-200',
                    'icon' => 'check-circle',
                ],
                'wrong' => [
                    'key' => 'near_miss',
                    'label' => 'Near Miss',
                    'color' => 'text-amber-700 bg-amber-50 border-amber-200',
                    'icon' => 'minus-circle',
                ],
            ],
            'slow' => [
                'correct' => [
                    'key' => 'deep_thinking',
                    'label' => 'Deep Thinking',
                    'color' => 'text-teal-700 bg-teal-50 border-teal-200',
                    'icon' => 'light-bulb',
                ],
                'wrong' => [
                    'key' => 'overthinking',
                    'label' => 'Overthinking',
                    'color' => 'text-orange-700 bg-orange-50 border-orange-200',
                    'icon' => 'clock',
                ],
            ],
        ];

        return $matrix[$timeCategory][$result] ?? [
            'key' => 'unknown',
            'label' => 'Unknown',
            'color' => 'text-slate-600 bg-slate-50 border-slate-200',
            'icon' => 'question-mark-circle',
        ];
    }

    protected function buildSummary(array $subjectBreakdown): array
    {
        if (empty($subjectBreakdown)) {
            return [
                'fastest_subject' => null,
                'slowest_subject' => null,
                'most_accurate_subject' => null,
                'weakest_subject' => null,
            ];
        }

        $fastest = null;
        $slowest = null;
        $mostAccurate = null;
        $weakest = null;
        $minTime = PHP_INT_MAX;
        $maxTime = 0;
        $maxAccuracy = -1;
        $minAccuracy = 101;

        foreach ($subjectBreakdown as $data) {
            if ($data['avg_time'] < $minTime && $data['total'] > 0) {
                $minTime = $data['avg_time'];
                $fastest = $data['name'] . ' (avg ' . $data['avg_time'] . 's)';
            }
            if ($data['avg_time'] > $maxTime && $data['total'] > 0) {
                $maxTime = $data['avg_time'];
                $slowest = $data['name'] . ' (avg ' . $data['avg_time'] . 's)';
            }
            if ($data['accuracy'] > $maxAccuracy) {
                $maxAccuracy = $data['accuracy'];
                $mostAccurate = $data['name'] . ' (' . $data['accuracy'] . '%)';
            }
            if ($data['accuracy'] < $minAccuracy && ($data['correct'] + $data['wrong']) > 0) {
                $minAccuracy = $data['accuracy'];
                $weakest = $data['name'] . ' (' . $data['accuracy'] . '%)';
            }
        }

        return [
            'fastest_subject' => $fastest,
            'slowest_subject' => $slowest,
            'most_accurate_subject' => $mostAccurate,
            'weakest_subject' => $weakest,
        ];
    }
}
