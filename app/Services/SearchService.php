<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Video;
use App\Models\CurrentAffairs;
use App\Models\Exam;
use App\Models\Subject;

class SearchService
{
    public function search(string $query): array
    {
        $searchTerm = '%' . $query . '%';

        $courses = Course::published()
            ->where('title', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm)
            ->limit(5)
            ->get();

        $videos = Video::where('status', 'ready')
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            })
            ->limit(5)
            ->get();

        $currentAffairs = CurrentAffairs::published()
            ->where('title', 'like', $searchTerm)
            ->orWhere('content', 'like', $searchTerm)
            ->limit(5)
            ->get();

        $exams = Exam::published()
            ->where('title', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm)
            ->limit(5)
            ->get();

        return [
            'query' => $query,
            'courses' => $courses,
            'videos' => $videos,
            'current_affairs' => $currentAffairs,
            'exams' => $exams,
            'total_count' => $courses->count() + $videos->count() + $currentAffairs->count() + $exams->count()
        ];
    }
}
