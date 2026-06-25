<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'subject_id', 'parent_id', 'is_active', 'sort_order'])]
class QuestionTopic extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (QuestionTopic $topic) {
            if (empty($topic->slug)) {
                $topic->slug = Str::slug($topic->name);
            }
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(QuestionTopic::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(QuestionTopic::class, 'parent_id')->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'topic_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function getFullPathAttribute(): string
    {
        $segments = [$this->name];
        $current = $this;

        while ($current->parent) {
            $current = $current->parent;
            array_unshift($segments, $current->name);
        }

        return implode(' > ', $segments);
    }
}
