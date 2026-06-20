<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'enrollment_number',
    'date_of_birth',
    'city',
    'state',
    'target_exam',
    'target_year',
    'batch_id',
])]
class StudentProfile extends Model
{
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'target_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
