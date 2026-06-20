@extends('layouts.guest')

@section('title', 'Sign Up')

@section('content')
<div class="w-full max-w-md">
    <div class="text-center mb-6 flex flex-col items-center">
        <img src="{{ asset('images/logo.png') }}" alt="THISAI Logo" class="h-28 w-28 object-contain mb-3">
        <h2 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100">Create Account</h2>
        <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Start your UPSC preparation today</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-3.5">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3.5 py-1.5 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:border-blue-500/50 transition-all">
            @error('name')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3.5 py-1.5 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:border-blue-500/50 transition-all">
            @error('email')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Phone Number -->
        <div>
            <label for="phone" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Phone Number</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3.5 py-1.5 text-sm text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:border-blue-500/50 transition-all">
            @error('phone')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-3.5">
            <!-- Target Exam -->
            <div>
                <label for="target_exam" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Target Exam</label>
                <select id="target_exam" name="target_exam" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-1.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:border-blue-500/50 transition-all">
                    <option value="UPSC CSE" {{ old('target_exam') == 'UPSC CSE' ? 'selected' : '' }}>UPSC CSE</option>
                    <option value="State PSC" {{ old('target_exam') == 'State PSC' ? 'selected' : '' }}>State PSC</option>
                </select>
                @error('target_exam')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Target Year -->
            <div>
                <label for="target_year" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Target Year</label>
                <select id="target_year" name="target_year" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-1.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:border-blue-500/50 transition-all">
                    <option value="2026" {{ old('target_year') == '2026' ? 'selected' : '' }}>2026</option>
                    <option value="2027" {{ old('target_year') == '2027' ? 'selected' : '' }}>2027</option>
                    <option value="2028" {{ old('target_year') == '2028' ? 'selected' : '' }}>2028</option>
                </select>
                @error('target_year')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Batch Selection -->
        <div>
            <label for="batch_id" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Assign Batch / Cohort</label>
            <select id="batch_id" name="batch_id" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3.5 py-1.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:border-blue-500/50 transition-all">
                @foreach($batches as $batch)
                    <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->name }} ({{ $batch->year }})</option>
                @endforeach
            </select>
            @error('batch_id')
                <p class="text-red-500 dark:text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="grid grid-cols-2 gap-3.5">
            <div>
                <label for="password" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Password</label>
                <input id="password" type="password" name="password" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3.5 py-1.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:border-blue-500/50 transition-all">
                @error('password')
                    <p class="text-red-500 dark:text-red-400 text-xs mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Confirm</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3.5 py-1.5 text-sm text-slate-800 dark:text-slate-100 focus:outline-none focus:border-blue-500/50 transition-all">
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded-lg text-sm shadow-lg hover:shadow-blue-500/10 active:scale-[0.98] transition-all">
            Create Account
        </button>

        <p class="text-xs text-slate-500 dark:text-slate-400 text-center mt-4">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 font-bold transition-colors">Sign In</a>
        </p>
    </form>
</div>
@endsection
