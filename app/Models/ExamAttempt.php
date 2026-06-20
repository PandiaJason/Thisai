<?php

namespace App\Models;

use App\Enums\ExamAttemptStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'exam_id',
    'started_at',
    'submitted_at',
    'score',
    'total_marks',
    'correct_count',
    'wrong_count',
    'unanswered_count',
    'accuracy',
    'percentile',
    'rank',
    'status',
    'session_token',
])]
class ExamAttempt extends Model
{
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
            'total_marks' => 'integer',
            'correct_count' => 'integer',
            'wrong_count' => 'integer',
            'unanswered_count' => 'integer',
            'accuracy' => 'decimal:2',
            'percentile' => 'decimal:2',
            'rank' => 'integer',
            'status' => ExamAttemptStatus::class,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attempt) {
            if (empty($attempt->session_token)) {
                $attempt->session_token = Str::random(40);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function getDurationTakenAttribute(): int
    {
        if (!$this->submitted_at) {
            return now()->diffInSeconds($this->started_at);
        }
        return $this->submitted_at->diffInSeconds($this->started_at);
    }
}
