<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'title',
    'description',
    'stream_url',
    'thumbnail_url',
    'scheduled_date',
    'start_time',
    'end_time',
    'auto_delete_at',
    'status', // scheduled, live, ended, deleted
    'created_by',
    'is_active',
])]
class LiveTelecast extends Model
{
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'auto_delete_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($telecast) {
            if (empty($telecast->auto_delete_at) && !empty($telecast->scheduled_date)) {
                // Default delete at 6:00 PM (18:00) on the scheduled day
                $dateStr = $telecast->scheduled_date instanceof Carbon
                    ? $telecast->scheduled_date->format('Y-m-d')
                    : $telecast->scheduled_date;
                $telecast->auto_delete_at = Carbon::parse($dateStr . ' 18:00:00');
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', '!=', 'deleted');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', Carbon::today());
    }

    public function scopeLiveNow($query)
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');

        return $query->where('is_active', true)
            ->where('status', 'live')
            ->orWhere(function ($q) use ($currentTime) {
                $q->where('is_active', true)
                    ->whereDate('scheduled_date', Carbon::today())
                    ->where('start_time', '<=', $currentTime)
                    ->where('end_time', '>=', $currentTime)
                    ->whereIn('status', ['scheduled', 'live']);
            });
    }

    public function isCurrentlyLive(): bool
    {
        if (!$this->is_active || $this->status === 'deleted' || $this->status === 'ended') {
            return false;
        }

        $now = Carbon::now('Asia/Kolkata');
        $today = Carbon::today('Asia/Kolkata')->format('Y-m-d');
        $scheduledDate = $this->scheduled_date instanceof Carbon
            ? $this->scheduled_date->format('Y-m-d')
            : $this->scheduled_date;

        if ($today !== $scheduledDate) {
            return false;
        }

        $currentTime = $now->format('H:i:s');
        return $currentTime >= $this->start_time && $currentTime <= $this->end_time;
    }
}
