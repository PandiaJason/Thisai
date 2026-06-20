@extends('layouts.app')

@section('title', 'Profile Settings - THISAI')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header -->
    <div class="border-b border-slate-200 pb-4">
        <h1 class="text-2xl font-extrabold text-slate-800">Profile Settings</h1>
        <p class="text-xs text-slate-500 mt-1">Manage your account information, preparation details, and security settings.</p>
    </div>

    <!-- Status Messages -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-lg text-sm font-semibold shadow">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Panel: User Avatar & Academy Information -->
        <div class="space-y-6">
            <div class="glass-card p-6 rounded-xl border border-slate-200 text-center space-y-4 shadow-lg">
                <!-- Initials Avatar -->
                <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-700 border-2 border-blue-500/30 flex items-center justify-center overflow-hidden mx-auto shadow-lg shadow-blue-500/10">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                    @else
                        <span class="font-black text-2xl text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>

                <div>
                    <h2 class="font-extrabold text-slate-800 text-base leading-snug">{{ $user->name }}</h2>
                    <span class="text-[10px] uppercase font-bold tracking-widest text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100 mt-1 inline-block">
                        {{ $user->role->label() ?? 'Student' }}
                    </span>
                </div>
            </div>

            <!-- Read Only Academy Card -->
            <div class="glass-card p-6 rounded-xl border border-slate-200 space-y-4 shadow-lg">
                <h3 class="text-xs uppercase font-extrabold text-slate-800 tracking-widest border-b border-slate-200 pb-2">Academic Status</h3>
                
                <div class="space-y-3 text-xs leading-relaxed">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">Enrollment ID:</span>
                        <span class="font-mono text-slate-700 font-semibold">{{ $user->studentProfile->enrollment_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">Current Batch:</span>
                        <span class="text-slate-700 font-semibold">{{ $user->studentProfile->batch->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">Target Exam:</span>
                        <span class="text-slate-700 font-semibold">{{ $user->studentProfile->target_exam ?? 'UPSC CSE' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-semibold">Target Year:</span>
                        <span class="text-slate-700 font-semibold">{{ $user->studentProfile->target_year ?? date('Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Edit Forms (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Personal Contact Info -->
                <div class="glass-card p-6 rounded-xl border border-slate-200 shadow-lg space-y-4">
                    <h3 class="text-xs uppercase font-extrabold text-slate-800 tracking-widest border-b border-slate-200 pb-2.5">Personal Information</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Name -->
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
                            @error('name')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
                            @error('email')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
                            @error('phone')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- City -->
                        <div>
                            <label for="city" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">City</label>
                            <input type="text" id="city" name="city" value="{{ old('city', $user->studentProfile->city ?? '') }}"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
                            @error('city')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- State -->
                        <div>
                            <label for="state" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">State</label>
                            <input type="text" id="state" name="state" value="{{ old('state', $user->studentProfile->state ?? '') }}"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
                            @error('state')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Password/Security Info -->
                <div class="glass-card p-6 rounded-xl border border-slate-200 shadow-lg space-y-4">
                    <h3 class="text-xs uppercase font-extrabold text-slate-800 tracking-widest border-b border-slate-200 pb-2.5">Security Settings</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">New Password</label>
                            <input type="password" id="password" name="password" placeholder="Leave blank to keep current"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
                            @error('password')
                                <p class="text-red-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Verify new password"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/30 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Form Submit -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg text-xs shadow-lg hover:shadow-blue-500/10 active:scale-[0.98] transition-all">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
