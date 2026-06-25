<?php

namespace App\Models;

use App\Enums\CourseDifficulty;
use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'description',
    'thumbnail',
    'subject_id',
    'faculty_id',
    'status',
    'difficulty',
    'duration_hours',
    'is_free',
    'price',
    'sort_order',
])]
class Course extends Model
{
    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'difficulty' => CourseDifficulty::class,
            'duration_hours' => 'integer',
            'is_free' => 'boolean',
            'price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($course) {
            if (empty($course->slug)) {
                $course->slug = Str::slug($course->title);
            }
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class)->orderBy('sort_order');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(Discussion::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', CourseStatus::PUBLISHED->value);
    }

    public function isEnrolledByUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->enrollments()->where('user_id', $user->id)->exists();
    }
}
