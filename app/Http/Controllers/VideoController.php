<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\CourseEnrollment;
use App\Services\BunnySignedUrlService;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    protected BunnySignedUrlService $signedUrlService;

    public function __construct(BunnySignedUrlService $signedUrlService)
    {
        $this->signedUrlService = $signedUrlService;
    }

    public function watch(Video $video)
    {
        $user = Auth::user();

        // Check enrollment if video is not free
        if (!$video->is_free && $video->course_id) {
            $isEnrolled = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $video->course_id)
                ->exists();

            if (!$isEnrolled) {
                return redirect()->route('courses.show', $video->course->slug)
                    ->with('error', 'You must enroll in the course to watch this video.');
            }
        }

        // Generate signed URL
        $embedUrl = $this->signedUrlService->generateSignedEmbedUrl($video->bunny_video_id);

        $course = $video->course->load('sections.videos');
        
        $currentProgress = $video->getUserProgress($user);

        return view('courses.player', compact('video', 'embedUrl', 'course', 'currentProgress'));
    }
}
