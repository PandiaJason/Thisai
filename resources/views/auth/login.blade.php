@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="w-full max-w-sm">
    <div class="text-center mb-8 flex flex-col items-center">
        <img src="{{ asset('images/logo.png') }}" alt="THISAI Logo" class="h-28 w-28 object-contain mb-4">
        <h2 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">Welcome Back</h2>
        <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Sign in to resume your learning</p>
    </div>

    <!-- Session Status -->
    @if(session('status'))
        <div class="mb-4 text-sm font-semibold text-green-600 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
            @error('email')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Password</label>
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
            @error('password')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1.5 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-800 text-blue-600 rounded focus:ring-0 focus:ring-offset-0">
            <label for="remember_me" class="ml-2 text-xs text-slate-500 dark:text-slate-400 font-semibold select-none cursor-pointer">Remember this device</label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-lg hover:shadow-blue-500/10 active:scale-[0.98] transition-all">
            Sign In
        </button>

        <p class="text-xs text-slate-500 dark:text-slate-400 text-center mt-6">
            New to THISAI? 
            <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 font-bold transition-colors">Create an account</a>
        </p>
    </form>
</div>
@endsection
