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
}
