<?php

namespace App\Models;

use App\Enums\VideoStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'description',
    'course_section_id',
    'course_id',
    'subject_id',
    'uploaded_by',
    'bunny_video_id',
    'bunny_library_id',
    'duration_seconds',
    'thumbnail_url',
    'status',
    'is_free',
    'sort_order',
])]
class Video extends Model
{
    protected function casts(): array
    {
        return [
            'status' => VideoStatus::class,
            'duration_seconds' => 'integer',
            'is_free' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(VideoProgress::class);
    }

    public function getDurationFormattedAttribute(): string
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getUserProgress(User $user): ?VideoProgress
    {
        return $this->progresses()->where('user_id', $user->id)->first();
    }
}
