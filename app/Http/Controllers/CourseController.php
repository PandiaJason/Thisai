<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $subjects = Subject::active()->get();
        
        $query = Course::published()->with(['subject', 'faculty']);

        if ($request->filled('subject')) {
            $query->where('subject_id', $request->subject);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('type')) {
            if ($request->type === 'free') {
                $query->where('is_free', true);
            } elseif ($request->type === 'paid') {
                $query->where('is_free', false);
            }
        }

        $courses = $query->paginate(9);

        return view('courses.index', compact('courses', 'subjects'));
    }

    public function show(Course $course)
    {
        $course->load(['subject', 'faculty', 'sections.videos']);
        
        $isEnrolled = false;
        $progress = null;

        if (Auth::check()) {
            $enrollment = CourseEnrollment::where('user_id', Auth::id())
                ->where('course_id', $course->id)
                ->first();
            
            if ($enrollment) {
                $isEnrolled = true;
                $progress = $enrollment->progress_percent;
            }
        }

        return view('courses.show', compact('course', 'isEnrolled', 'progress'));
    }

    public function enroll(Course $course)
    {
        $user = Auth::user();

        $enrollment = CourseEnrollment::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'enrolled_at' => Carbon::now(),
            ]
        );

        return redirect()->route('courses.show', $course->slug)
            ->with('success', 'Enrolled in course successfully!');
    }
}
