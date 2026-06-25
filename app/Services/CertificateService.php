<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    public function generateCourseCertificate(User $user, Course $course): Certificate
    {
        // Check if certificate already exists
        $existing = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'issued_at' => now(),
        ]);

        $this->generatePdf($certificate);

        return $certificate;
    }

    public function generateExamCertificate(User $user, ExamAttempt $attempt): Certificate
    {
        $existing = Certificate::where('user_id', $user->id)
            ->where('exam_id', $attempt->exam_id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'exam_id' => $attempt->exam_id,
            'issued_at' => now(),
        ]);

        $this->generatePdf($certificate);

        return $certificate;
    }

    public function generatePdf(Certificate $certificate): string
    {
        $pdf = Pdf::loadView('certificates.pdf', compact('certificate'))
            ->setPaper('a4', 'landscape');

        $fileName = 'certificates/' . $certificate->certificate_number . '.pdf';
        
        // Ensure directory exists
        if (!Storage::disk('public')->exists('certificates')) {
            Storage::disk('public')->makeDirectory('certificates');
        }

        Storage::disk('public')->put($fileName, $pdf->output());

        $certificate->pdf_path = $fileName;
        $certificate->save();

        return $fileName;
    }

    public function verifyCertificate(string $certificateNumber): ?Certificate
    {
        return Certificate::where('certificate_number', $certificateNumber)
            ->with(['user', 'course', 'exam'])
            ->first();
    }
}
