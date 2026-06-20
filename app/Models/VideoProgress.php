<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'video_id', 'watched_seconds', 'total_seconds', 'completed', 'last_watched_at'])]
class VideoProgress extends Model
{
    protected $table = 'video_progress';

    protected function casts(): array
    {
        return [
            'watched_seconds' => 'integer',
            'total_seconds' => 'integer',
            'completed' => 'boolean',
            'last_watched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->total_seconds <= 0) {
            return 0;
        }
        return min(100, round(($this->watched_seconds / $this->total_seconds) * 100));
    }
}
