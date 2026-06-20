@extends('layouts.app')

@section('title', 'Verify Activation - ' . $exam->title)

@section('content')
<div class="min-h-[60vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="glass-card max-w-md w-full p-8 rounded-2xl border border-slate-200 shadow-xl space-y-6">
        <div class="text-center space-y-2">
            <div class="w-16 h-16 bg-blue-50 border border-blue-100 rounded-full flex items-center justify-center mx-auto text-blue-600 shadow-sm">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800">Passcode Required</h2>
            <p class="text-slate-500 text-xs mt-1">This exam is protected. Please enter the activation key provided by the administration to start.</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border border-red-100 text-red-700 text-xs font-semibold px-4 py-3 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('exams.start', $exam->slug) }}" method="POST" class="space-y-4">
            @csrf
            <div class="space-y-1">
                <label for="activation_key" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Activation Key / Passcode</label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-9 5a2 2 0 012-2m7-7H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2z" />
                        </svg>
                    </div>
                    <input type="text" name="activation_key" id="activation_key" required autofocus placeholder="Enter passcode"
                           class="block w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-lg text-slate-800 text-sm font-semibold placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('exams.index') }}" class="flex-1 text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl text-xs transition-colors">
                    Back to Exams
                </a>
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-2.5 px-4 rounded-xl text-xs transition-all shadow-md shadow-blue-500/10 active:scale-[0.98]">
                    Verify & Start
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
