<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['exam_attempt_id', 'question_id', 'selected_option_ids', 'is_correct', 'marks_obtained', 'time_spent_seconds'])]
class AttemptAnswer extends Model
{
    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array', // Array of option IDs selected
            'is_correct' => 'boolean',
            'marks_obtained' => 'decimal:2',
            'time_spent_seconds' => 'integer',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
