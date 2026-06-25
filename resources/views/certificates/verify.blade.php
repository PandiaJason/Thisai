<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Certificate - THISAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-extrabold text-slate-800">THISAI</h1>
            <p class="text-sm text-slate-500 mt-1">Certificate Verification</p>
        </div>

        {{-- Search Form --}}
        <form method="GET" action="{{ route('certificates.verify', '') }}" class="bg-white/80 backdrop-blur-xl border border-slate-200 rounded-2xl p-6 shadow-xl shadow-slate-200/50 mb-6">
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-2">Certificate Number</label>
            <div class="flex gap-2">
                <input type="text" name="certificate_number" value="{{ $certificateNumber ?? '' }}" placeholder="THISAI-2026-XXXXXX" class="flex-1 rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 font-mono">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </div>
        </form>

        {{-- Result --}}
        @if(isset($certificateNumber) && $certificateNumber)
            @if($certificate)
                <div class="bg-white/80 backdrop-blur-xl border-2 border-emerald-200 rounded-2xl p-6 shadow-xl text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h2 class="text-lg font-extrabold text-emerald-800 mb-1">Certificate Verified</h2>
                    <p class="text-xs text-emerald-600 mb-5">This certificate is authentic and valid</p>

                    <div class="bg-emerald-50 rounded-xl p-4 text-left space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Student Name</span>
                            <span class="font-bold text-slate-800">{{ $certificate->user?->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">{{ $certificate->course ? 'Course' : 'Exam' }}</span>
                            <span class="font-bold text-slate-800">{{ $certificate->course?->title ?? $certificate->exam?->title ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Certificate No.</span>
                            <span class="font-mono font-bold text-slate-800">{{ $certificate->certificate_number }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Issued On</span>
                            <span class="font-bold text-slate-800">{{ $certificate->issued_at->format('F d, Y') }}</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white/80 backdrop-blur-xl border-2 border-red-200 rounded-2xl p-6 shadow-xl text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-red-400 to-rose-500 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="text-lg font-extrabold text-red-800 mb-1">Certificate Not Found</h2>
                    <p class="text-sm text-red-600">No certificate matches the number "{{ $certificateNumber }}"</p>
                </div>
            @endif
        @endif
    </div>
</body>
</html>
