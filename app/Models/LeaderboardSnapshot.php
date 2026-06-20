<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'period', 'period_date', 'total_score', 'total_exams', 'accuracy', 'rank'])]
class LeaderboardSnapshot extends Model
{
    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'total_score' => 'decimal:2',
            'total_exams' => 'integer',
            'accuracy' => 'decimal:2',
            'rank' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
