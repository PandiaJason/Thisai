<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Video;
use App\Models\CurrentAffairs;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getStudentStats(User $user): array
    {
        $enrolledCount = CourseEnrollment::where('user_id', $user->id)->count();
        
        $attemptsCount = ExamAttempt::where('user_id', $user->id)
            ->where('status', '!=', 'in_progress')
            ->count();

        $avgScore = ExamAttempt::where('user_id', $user->id)
            ->where('status', '!=', 'in_progress')
            ->avg('score') ?? 0;

        $watchedCount = Video::whereHas('progresses', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('completed', true);
        })->count();

        // Weak/Strong Areas
        $subjectScores = ExamAttempt::where('user_id', $user->id)
            ->where('status', '!=', 'in_progress')
            ->join('exams', 'exam_attempts.exam_id', '=', 'exams.id')
            ->join('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->select('subjects.name', DB::raw('AVG(exam_attempts.accuracy) as avg_accuracy'))
            ->groupBy('subjects.name')
            ->get();

        $strongAreas = $subjectScores->where('avg_accuracy', '>=', 70)->pluck('name')->toArray();
        $weakAreas = $subjectScores->where('avg_accuracy', '<', 70)->pluck('name')->toArray();

        return [
            'enrolled_courses' => $enrolledCount,
            'exams_attempted' => $attemptsCount,
            'avg_score' => round($avgScore, 2),
            'videos_watched' => $watchedCount,
            'strong_areas' => $strongAreas,
            'weak_areas' => $weakAreas,
        ];
    }

    public function getFacultyStats(User $faculty): array
    {
        $coursesCount = Course::where('faculty_id', $faculty->id)->count();
        
        $studentCount = CourseEnrollment::whereHas('course', function ($q) use ($faculty) {
            $q->where('faculty_id', $faculty->id);
        })->count();

        $videoCount = Video::where('uploaded_by', $faculty->id)->count();

        $examsCount = Exam::where('created_by', $faculty->id)->count();

        return [
            'courses_created' => $coursesCount,
            'total_students' => $studentCount,
            'videos_uploaded' => $videoCount,
            'exams_created' => $examsCount,
        ];
    }

    public function getAdminStats(): array
    {
        $totalStudents = User::role(UserRole::STUDENT)->count();
        $activeStudents = User::role(UserRole::STUDENT)->active()->count();
        $totalCourses = Course::count();
        $totalVideos = Video::count();
        
        // Mock Revenue Ready Metrics
        $totalEnrollments = CourseEnrollment::count();
        $paidEnrollments = CourseEnrollment::whereHas('course', function($q) {
            $q->where('is_free', false);
        })->count();
        
        $estRevenue = CourseEnrollment::join('courses', 'course_enrollments.course_id', '=', 'courses.id')
            ->where('courses.is_free', false)
            ->sum('courses.price');

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'total_courses' => $totalCourses,
            'total_videos' => $totalVideos,
            'total_enrollments' => $totalEnrollments,
            'paid_enrollments' => $paidEnrollments,
            'estimated_revenue' => round($estRevenue, 2),
        ];
    }

    public function getScoreTrend(User $user, int $limit = 20): \Illuminate\Support\Collection
    {
        return ExamAttempt::where('user_id', $user->id)
            ->where('status', '!=', 'in_progress')
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at')
            ->limit($limit)
            ->with('exam:id,title')
            ->get()
            ->map(fn ($a) => [
                'exam_title' => $a->exam?->title ?? 'Unknown',
                'score' => $a->score,
                'total_marks' => $a->total_marks,
                'accuracy' => $a->accuracy,
                'percentage' => $a->total_marks > 0 ? round(($a->score / $a->total_marks) * 100, 1) : 0,
                'submitted_at' => $a->submitted_at->format('M d'),
            ]);
    }

    public function getSubjectHeatmap(User $user): array
    {
        $attempts = ExamAttempt::where('user_id', $user->id)
            ->where('status', '!=', 'in_progress')
            ->with(['exam:id,title', 'answers.question.subject'])
            ->orderBy('submitted_at')
            ->limit(10)
            ->get();

        $heatmap = [];
        foreach ($attempts as $attempt) {
            $examTitle = $attempt->exam?->title ?? 'Unknown';
            $subjectData = [];

            foreach ($attempt->answers as $answer) {
                $subjectName = $answer->question?->subject?->name ?? 'General';
                if (!isset($subjectData[$subjectName])) {
                    $subjectData[$subjectName] = ['correct' => 0, 'attempted' => 0];
                }
                if ($answer->is_correct !== null) {
                    $subjectData[$subjectName]['attempted']++;
                    if ($answer->is_correct) {
                        $subjectData[$subjectName]['correct']++;
                    }
                }
            }

            foreach ($subjectData as $subject => $data) {
                if (!isset($heatmap[$subject])) {
                    $heatmap[$subject] = [];
                }
                $heatmap[$subject][$examTitle] = $data['attempted'] > 0
                    ? round(($data['correct'] / $data['attempted']) * 100, 1)
                    : 0;
            }
        }

        return $heatmap;
    }

    public function getTimeManagement(User $user): array
    {
        $results = DB::table('attempt_answers')
            ->join('exam_attempts', 'attempt_answers.exam_attempt_id', '=', 'exam_attempts.id')
            ->join('questions', 'attempt_answers.question_id', '=', 'questions.id')
            ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->where('exam_attempts.user_id', $user->id)
            ->where('exam_attempts.status', '!=', 'in_progress')
            ->select('subjects.name', DB::raw('AVG(attempt_answers.time_spent_seconds) as avg_time'))
            ->groupBy('subjects.name')
            ->get();

        $data = [];
        foreach ($results as $row) {
            $data[$row->name] = round($row->avg_time, 1);
        }
        return $data;
    }

    public function getBatchComparison(User $user): array
    {
        $profile = $user->studentProfile;
        $batchId = $profile?->batch_id;

        // Get user's subject-wise accuracy
        $userAccuracy = $this->getUserSubjectAccuracy($user);

        // Get batch average if batch exists
        $batchAccuracy = [];
        if ($batchId) {
            $batchStudentIds = DB::table('student_profiles')
                ->where('batch_id', $batchId)
                ->pluck('user_id');

            $batchResults = DB::table('attempt_answers')
                ->join('exam_attempts', 'attempt_answers.exam_attempt_id', '=', 'exam_attempts.id')
                ->join('questions', 'attempt_answers.question_id', '=', 'questions.id')
                ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
                ->whereIn('exam_attempts.user_id', $batchStudentIds)
                ->where('exam_attempts.status', '!=', 'in_progress')
                ->whereNotNull('attempt_answers.is_correct')
                ->select(
                    'subjects.name',
                    DB::raw('SUM(CASE WHEN attempt_answers.is_correct = true THEN 1 ELSE 0 END) as correct'),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('subjects.name')
                ->get();

            foreach ($batchResults as $row) {
                $batchAccuracy[$row->name] = $row->total > 0 ? round(($row->correct / $row->total) * 100, 1) : 0;
            }
        }

        $comparison = [];
        $allSubjects = array_unique(array_merge(array_keys($userAccuracy), array_keys($batchAccuracy)));
        foreach ($allSubjects as $subject) {
            $comparison[$subject] = [
                'user_accuracy' => $userAccuracy[$subject] ?? 0,
                'batch_accuracy' => $batchAccuracy[$subject] ?? 0,
            ];
        }

        return $comparison;
    }

    public function getWeakAreas(User $user, float $threshold = 50.0): array
    {
        $accuracy = $this->getUserSubjectAccuracy($user);
        $counts = $this->getUserSubjectAttemptCounts($user);

        $weak = [];
        foreach ($accuracy as $subject => $acc) {
            if ($acc < $threshold && ($counts[$subject] ?? 0) > 0) {
                $weak[] = [
                    'name' => $subject,
                    'accuracy' => $acc,
                    'attempts' => $counts[$subject] ?? 0,
                ];
            }
        }

        usort($weak, fn($a, $b) => $a['accuracy'] <=> $b['accuracy']);
        return $weak;
    }

    public function getImprovementTracker(User $user): array
    {
        $recent = ExamAttempt::where('user_id', $user->id)
            ->where('status', '!=', 'in_progress')
            ->orderByDesc('submitted_at')
            ->limit(5)
            ->avg('accuracy') ?? 0;

        $previous = ExamAttempt::where('user_id', $user->id)
            ->where('status', '!=', 'in_progress')
            ->orderByDesc('submitted_at')
            ->skip(5)
            ->limit(5)
            ->avg('accuracy') ?? 0;

        $change = round($recent - $previous, 2);

        return [
            'recent_avg' => round($recent, 2),
            'previous_avg' => round($previous, 2),
            'change' => $change,
        ];
    }

    protected function getUserSubjectAccuracy(User $user): array
    {
        $results = DB::table('attempt_answers')
            ->join('exam_attempts', 'attempt_answers.exam_attempt_id', '=', 'exam_attempts.id')
            ->join('questions', 'attempt_answers.question_id', '=', 'questions.id')
            ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->where('exam_attempts.user_id', $user->id)
            ->where('exam_attempts.status', '!=', 'in_progress')
            ->whereNotNull('attempt_answers.is_correct')
            ->select(
                'subjects.name',
                DB::raw('SUM(CASE WHEN attempt_answers.is_correct = true THEN 1 ELSE 0 END) as correct'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('subjects.name')
            ->get();

        $accuracy = [];
        foreach ($results as $row) {
            $accuracy[$row->name] = $row->total > 0 ? round(($row->correct / $row->total) * 100, 1) : 0;
        }
        return $accuracy;
    }

    protected function getUserSubjectAttemptCounts(User $user): array
    {
        $results = DB::table('attempt_answers')
            ->join('exam_attempts', 'attempt_answers.exam_attempt_id', '=', 'exam_attempts.id')
            ->join('questions', 'attempt_answers.question_id', '=', 'questions.id')
            ->join('subjects', 'questions.subject_id', '=', 'subjects.id')
            ->where('exam_attempts.user_id', $user->id)
            ->where('exam_attempts.status', '!=', 'in_progress')
            ->whereNotNull('attempt_answers.is_correct')
            ->select('subjects.name', DB::raw('COUNT(*) as total'))
            ->groupBy('subjects.name')
            ->get();

        $counts = [];
        foreach ($results as $row) {
            $counts[$row->name] = $row->total;
        }
        return $counts;
    }
}
