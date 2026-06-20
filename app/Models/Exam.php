<?php

namespace App\Models;

use App\Enums\ExamType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'description',
    'activation_key',
    'subject_id',
    'created_by',
    'type',
    'difficulty',
    'duration_minutes',
    'total_marks',
    'negative_marking',
    'randomize_questions',
    'randomize_options',
    'is_published',
    'starts_at',
    'ends_at',
    'max_attempts',
])]
class Exam extends Model
{
    protected function casts(): array
    {
        return [
            'type' => ExamType::class,
            'duration_minutes' => 'integer',
            'total_marks' => 'integer',
            'negative_marking' => 'decimal:2',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'is_published' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'max_attempts' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($exam) {
            if (empty($exam->slug)) {
                $exam->slug = Str::slug($exam->title);
            }
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeActive($query)
    {
        $now = now();
        return $query->published()
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>=', $now);
            });
    }

    public function getUserAttempt(User $user): ?ExamAttempt
    {
        return $this->attempts()->where('user_id', $user->id)->first();
    }
}
