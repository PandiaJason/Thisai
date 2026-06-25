<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::where('user_id', Auth::id())
            ->with('exam')
            ->latest()
            ->get();

        return view('certificates.index', compact('certificates'));
    }

    public function download($id)
    {
        $certificate = Certificate::where('user_id', Auth::id())->findOrFail($id);

        if (!$certificate->pdf_path || !Storage::disk('public')->exists($certificate->pdf_path)) {
            $service = app(\App\Services\CertificateService::class);
            $service->generatePdf($certificate);
        }

        return Storage::disk('public')->download(
            $certificate->pdf_path,
            'certificate-' . $certificate->certificate_number . '.pdf'
        );
    }

    public function verify(string $certificateNumber = '')
    {
        $certificate = null;

        if ($certificateNumber) {
            $certificate = Certificate::with(['user', 'course', 'exam'])
                ->where('certificate_number', $certificateNumber)
                ->first();
        }

        return view('certificates.verify', compact('certificate', 'certificateNumber'));
    }
}
