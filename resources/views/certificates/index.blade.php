@extends('layouts.app')

@section('title', 'My Certificates - THISAI')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-800">My Certificates</h1>
        <p class="text-slate-500 text-sm mt-1">Download and share your achievements</p>
    </div>

    @if($certificates->isEmpty())
        <div class="glass-card p-12 rounded-2xl border border-slate-200 text-center">
            <svg class="w-14 h-14 mx-auto text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            <p class="text-sm font-semibold text-slate-500">No certificates yet</p>
            <p class="text-xs text-slate-400 mt-1">Complete courses and exams to earn certificates</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($certificates as $certificate)
                <div class="glass-card p-5 rounded-2xl border border-slate-200 hover:border-blue-200 hover:shadow-md transition-all">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-slate-800 truncate">
                                {{ $certificate->course?->title ?? $certificate->exam?->title ?? 'Certificate' }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $certificate->course ? 'Course Completion' : 'Exam Completion' }}</p>
                        </div>
                    </div>
                    <div class="space-y-1.5 mb-4 text-xs text-slate-500">
                        <div class="flex justify-between">
                            <span>Certificate No.</span>
                            <span class="font-mono font-bold text-slate-700">{{ $certificate->certificate_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Issued</span>
                            <span class="font-semibold text-slate-700">{{ $certificate->issued_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('certificates.download', $certificate->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-2 rounded-lg text-xs transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download
                        </a>
                        <a href="{{ route('certificates.verify', $certificate->certificate_number) }}" target="_blank" class="inline-flex items-center justify-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-3 py-2 rounded-lg text-xs transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Verify
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
