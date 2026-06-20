<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VideoProgress;
use App\Models\CourseEnrollment;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoProgressController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'video_id' => ['required', 'exists:videos,id'],
            'watched_seconds' => ['required', 'integer', 'min:0'],
            'total_seconds' => ['required', 'integer', 'min:1'],
        ]);

        $user = Auth::user();
        $videoId = $request->video_id;
        $watched = $request->watched_seconds;
        $total = $request->total_seconds;

        // Determine if video is completed (e.g. >= 90% watched)
        $completed = ($watched / $total) >= 0.90;

        $progress = VideoProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'video_id' => $videoId,
            ],
            [
                'watched_seconds' => $watched,
                'total_seconds' => $total,
                'completed' => $completed,
                'last_watched_at' => Carbon::now(),
            ]
        );

        // Recalculate Course progress percent if video belongs to a course
        $video = Video::find($videoId);
        if ($video && $video->course_id) {
            $enrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $video->course_id)
                ->first();

            if ($enrollment) {
                // Get total videos in course
                $totalVideos = Video::where('course_id', $video->course_id)->count();
                if ($totalVideos > 0) {
                    // Get completed videos in course
                    $completedVideos = Video::where('course_id', $video->course_id)
                        ->whereHas('progresses', function ($q) use ($user) {
                            $q->where('user_id', $user->id)->where('completed', true);
                        })->count();

                    $percent = min(100, round(($completedVideos / $totalVideos) * 100));
                    $enrollment->progress_percent = $percent;
                    if ($percent === 100 && empty($enrollment->completed_at)) {
                        $enrollment->completed_at = Carbon::now();
                    }
                    $enrollment->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'progress_percent' => $progress->progress_percent,
            'completed' => $completed
        ]);
    }
}
