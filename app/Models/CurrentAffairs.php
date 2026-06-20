<?php

namespace App\Models;

use App\Enums\CurrentAffairsType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'content', 'author_id', 'subject_id', 'type', 'publish_date', 'is_published', 'tags'])]
class CurrentAffairs extends Model
{
    protected $table = 'current_affairs';

    protected function casts(): array
    {
        return [
            'type' => CurrentAffairsType::class,
            'publish_date' => 'date',
            'is_published' => 'boolean',
            'tags' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function isBookmarkedByUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->bookmarks()->where('user_id', $user->id)->exists();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
