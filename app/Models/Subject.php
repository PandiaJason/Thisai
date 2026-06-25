<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description', 'color', 'category_id', 'sort_order', 'is_active'])]
class Subject extends Model
{
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($subject) {
            if (empty($subject->slug)) {
                $subject->slug = Str::slug($subject->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class)->orderBy('sort_order');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(QuestionTopic::class)->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function currentAffairs(): HasMany
    {
        return $this->hasMany(CurrentAffairs::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
