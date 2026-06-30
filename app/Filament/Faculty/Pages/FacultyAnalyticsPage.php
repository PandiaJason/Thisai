<?php

namespace App\Filament\Faculty\Pages;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\AttemptAnswer;
use App\Models\Subject;
use App\Enums\ExamAttemptStatus;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class FacultyAnalyticsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.faculty.pages.faculty-analytics';

    protected static ?string $title = 'Exam Analytics';

    protected static ?string $slug = 'exam-analytics';

    protected static ?string $navigationLabel = 'Analytics';

    protected static string|\UnitEnum|null $navigationGroup = 'Examinations';

    protected static ?int $navigationSort = 50;

    public ?int $selectedExamId = null;

    public array $examOptions = [];

    public array $questionDifficulty = [];

    public array $scoreDistribution = [];

    public array $mostMissedQuestions = [];

    public array $subjectPerformance = [];

    public array $examSummary = [];

    public function mount(): void
    {
        $query = Exam::query();

        if (!auth()->user()->isSuperAdmin()) {
            $query->where('created_by', auth()->id());
        }

        $this->examOptions = $query->orderByDesc('created_at')
            ->pluck('title', 'id')
            ->toArray();

        if (!empty($this->examOptions)) {
            $this->selectedExamId = array_key_first($this->examOptions);
            $this->loadAnalytics();
        }
    }

    public function updatedSelectedExamId(): void
    {
        $this->loadAnalytics();
    }

    protected function loadAnalytics(): void
    {
        if (!$this->selectedExamId) {
            return;
        }

        $query = Exam::with('questions.subject', 'questions.options');

        if (!auth()->user()->isSuperAdmin()) {
            $query->where('created_by', auth()->id());
        }

        $exam = $query->find($this->selectedExamId);
        if (!$exam) {
            return;
        }

        $attempts = ExamAttempt::where('exam_id', $this->selectedExamId)
            ->where('status', '!=', ExamAttemptStatus::IN_PROGRESS->value)
            ->get();

        $this->loadExamSummary($exam, $attempts);
        $this->loadQuestionDifficulty($exam, $attempts);
        $this->loadScoreDistribution($attempts);
        $this->loadMostMissedQuestions($exam, $attempts);
        $this->loadSubjectPerformance($exam, $attempts);
    }

    protected function loadExamSummary(Exam $exam, $attempts): void
    {
        $this->examSummary = [
            'total_attempts' => $attempts->count(),
            'avg_score' => $attempts->count() > 0 ? round($attempts->avg('score'), 2) : 0,
            'avg_accuracy' => $attempts->count() > 0 ? round($attempts->avg('accuracy'), 2) : 0,
            'highest_score' => $attempts->max('score') ?? 0,
            'lowest_score' => $attempts->min('score') ?? 0,
            'total_marks' => $exam->total_marks,
        ];
    }

    protected function loadQuestionDifficulty(Exam $exam, $attempts): void
    {
        $totalAttempts = $attempts->count();
        if ($totalAttempts === 0) {
            $this->questionDifficulty = [];
            return;
        }

        $attemptIds = $attempts->pluck('id');
        $result = [];

        foreach ($exam->questions as $index => $question) {
            $answers = AttemptAnswer::where('question_id', $question->id)
                ->whereIn('exam_attempt_id', $attemptIds)
                ->get();

            $correctCount = $answers->where('is_correct', true)->count();
            $wrongCount = $answers->where('is_correct', false)->count();
            $unanswered = $answers->whereNull('is_correct')->count();
            $correctRate = $totalAttempts > 0 ? round(($correctCount / $totalAttempts) * 100, 1) : 0;
            $avgTime = $answers->avg('time_spent_seconds') ?? 0;

            $result[] = [
                'number' => $index + 1,
                'text' => strip_tags(substr($question->question_text, 0, 80)) . '...',
                'subject' => $question->subject?->name ?? 'Untagged',
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
                'unanswered' => $unanswered,
                'correct_rate' => $correctRate,
                'avg_time' => round($avgTime, 1),
                'difficulty_label' => match(true) {
                    $correctRate >= 70 => 'Easy',
                    $correctRate >= 40 => 'Medium',
                    default => 'Hard',
                },
            ];
        }

        $this->questionDifficulty = $result;
    }

    protected function loadScoreDistribution($attempts): void
    {
        $buckets = [
            '0-10' => 0, '11-20' => 0, '21-30' => 0, '31-40' => 0, '41-50' => 0,
            '51-60' => 0, '61-70' => 0, '71-80' => 0, '81-90' => 0, '91-100' => 0,
        ];

        foreach ($attempts as $attempt) {
            $pct = $attempt->total_marks > 0 ? ($attempt->score / $attempt->total_marks) * 100 : 0;
            $bucket = match(true) {
                $pct <= 10 => '0-10',
                $pct <= 20 => '11-20',
                $pct <= 30 => '21-30',
                $pct <= 40 => '31-40',
                $pct <= 50 => '41-50',
                $pct <= 60 => '51-60',
                $pct <= 70 => '61-70',
                $pct <= 80 => '71-80',
                $pct <= 90 => '81-90',
                default => '91-100',
            };
            $buckets[$bucket]++;
        }

        $this->scoreDistribution = $buckets;
    }

    protected function loadMostMissedQuestions(Exam $exam, $attempts): void
    {
        $totalAttempts = $attempts->count();
        if ($totalAttempts === 0) {
            $this->mostMissedQuestions = [];
            return;
        }

        $attemptIds = $attempts->pluck('id');
        $missed = [];

        foreach ($exam->questions as $index => $question) {
            $wrongCount = AttemptAnswer::where('question_id', $question->id)
                ->whereIn('exam_attempt_id', $attemptIds)
                ->where('is_correct', false)
                ->count();

            $wrongRate = round(($wrongCount / $totalAttempts) * 100, 1);

            $missed[] = [
                'number' => $index + 1,
                'text' => strip_tags(substr($question->question_text, 0, 100)) . '...',
                'subject' => $question->subject?->name ?? 'Untagged',
                'wrong_rate' => $wrongRate,
                'wrong_count' => $wrongCount,
            ];
        }

        usort($missed, fn($a, $b) => $b['wrong_rate'] <=> $a['wrong_rate']);
        $this->mostMissedQuestions = array_slice($missed, 0, 10);
    }

    protected function loadSubjectPerformance(Exam $exam, $attempts): void
    {
        $totalAttempts = $attempts->count();
        if ($totalAttempts === 0) {
            $this->subjectPerformance = [];
            return;
        }

        $attemptIds = $attempts->pluck('id');
        $subjects = [];

        foreach ($exam->questions as $question) {
            $subjectName = $question->subject?->name ?? 'Untagged';
            if (!isset($subjects[$subjectName])) {
                $subjects[$subjectName] = ['correct' => 0, 'wrong' => 0, 'unanswered' => 0, 'total' => 0, 'total_time' => 0];
            }

            $answers = AttemptAnswer::where('question_id', $question->id)
                ->whereIn('exam_attempt_id', $attemptIds)
                ->get();

            $subjects[$subjectName]['correct'] += $answers->where('is_correct', true)->count();
            $subjects[$subjectName]['wrong'] += $answers->where('is_correct', false)->count();
            $subjects[$subjectName]['unanswered'] += $answers->whereNull('is_correct')->count();
            $subjects[$subjectName]['total'] += $answers->count();
            $subjects[$subjectName]['total_time'] += $answers->sum('time_spent_seconds');
        }

        $result = [];
        foreach ($subjects as $name => $data) {
            $result[] = [
                'name' => $name,
                'correct' => $data['correct'],
                'wrong' => $data['wrong'],
                'unanswered' => $data['unanswered'],
                'total' => $data['total'],
                'accuracy' => $data['total'] > 0 ? round(($data['correct'] / $data['total']) * 100, 1) : 0,
                'avg_time' => $data['total'] > 0 ? round($data['total_time'] / $data['total'], 1) : 0,
            ];
        }

        $this->subjectPerformance = $result;
    }

    public function exportResultsCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = Exam::query();

        if (!auth()->user()->isSuperAdmin()) {
            $query->where('created_by', auth()->id());
        }

        $exam = $query->find($this->selectedExamId);
        if (!$exam) {
            return response()->streamDownload(fn() => null, 'error.csv');
        }

        $attempts = ExamAttempt::where('exam_id', $this->selectedExamId)
            ->where('status', '!=', ExamAttemptStatus::IN_PROGRESS->value)
            ->with(['user', 'answers.question.subject'])
            ->orderByDesc('score')
            ->get();

        $headers = ['Rank', 'Student Name', 'Email', 'Score', 'Total Marks', 'Accuracy %', 'Correct', 'Wrong', 'Unanswered', 'Percentile', 'Duration (min)', 'Submitted At'];

        // Collect unique subjects for subject-wise columns
        $subjects = $exam->questions()->with('subject')->get()
            ->pluck('subject.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        foreach ($subjects as $subject) {
            $headers[] = $subject . ' (Correct)';
            $headers[] = $subject . ' (Total)';
            $headers[] = $subject . ' (Accuracy %)';
        }

        $filename = 'exam_results_' . str_replace(' ', '_', $exam->title) . '_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($attempts, $headers, $subjects) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($attempts as $attempt) {
                $duration = $attempt->submitted_at && $attempt->started_at
                    ? round($attempt->submitted_at->diffInSeconds($attempt->started_at) / 60, 1)
                    : 0;

                $row = [
                    $attempt->rank ?? '-',
                    $attempt->user->name ?? 'Unknown',
                    $attempt->user->email ?? '',
                    $attempt->score,
                    $attempt->total_marks,
                    $attempt->accuracy,
                    $attempt->correct_count,
                    $attempt->wrong_count,
                    $attempt->unanswered_count,
                    $attempt->percentile,
                    $duration,
                    $attempt->submitted_at?->format('Y-m-d H:i:s') ?? '-',
                ];

                // Subject-wise breakdown per student
                foreach ($subjects as $subject) {
                    $subjectAnswers = $attempt->answers->filter(function ($answer) use ($subject) {
                        return $answer->question?->subject?->name === $subject;
                    });
                    $correct = $subjectAnswers->where('is_correct', true)->count();
                    $total = $subjectAnswers->count();
                    $accuracy = $total > 0 ? round(($correct / $total) * 100, 1) : 0;
                    $row[] = $correct;
                    $row[] = $total;
                    $row[] = $accuracy;
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
