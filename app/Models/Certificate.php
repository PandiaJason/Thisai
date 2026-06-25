<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'course_id', 'exam_id', 'certificate_number', 'issued_at', 'pdf_path'])]
class Certificate extends Model
{
    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Certificate $certificate) {
            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = 'THISAI-' . date('Y') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
