<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['user_id', 'title', 'body', 'subject_id', 'question_id', 'course_id', 'is_resolved', 'upvotes', 'reply_count'])]
class Discussion extends Model
{
    protected function casts(): array
    {
        return [
            'is_resolved' => 'boolean',
            'upvotes' => 'integer',
            'reply_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class);
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(DiscussionVote::class, 'votable');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('is_resolved', true);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('is_resolved', false);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('upvotes');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }
}
