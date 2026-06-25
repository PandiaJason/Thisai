@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
<div class="w-full max-w-sm">
    <div class="text-center mb-8 flex flex-col items-center">
        <img src="{{ asset('images/logo.png') }}" alt="THISAI Logo" class="h-28 w-28 object-contain mb-4">
        <h2 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">Reset Password</h2>
        <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Enter your email to receive a reset link</p>
    </div>

    <!-- Session Status -->
    @if(session('status'))
        <div class="mb-4 flex items-center gap-2 text-sm font-semibold text-green-600 dark:text-green-400">
            <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
            @error('email')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-lg hover:shadow-blue-500/10 active:scale-[0.98] transition-all">
            Send Reset Link
        </button>

        <p class="text-xs text-slate-500 dark:text-slate-400 text-center mt-6">
            <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 font-bold transition-colors inline-flex items-center gap-1">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Back to Sign In
            </a>
        </p>
    </form>
</div>
@endsection
