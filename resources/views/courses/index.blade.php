@extends('layouts.app')

@section('title', 'Explore Courses - THISAI')

@section('content')
<div class="space-y-8">
    
    <!-- Hero Header -->
    <div class="text-center py-6">
        <h1 class="text-3xl font-extrabold text-slate-800">IAS & UPSC Study Courses</h1>
        <p class="text-slate-500 text-sm mt-2 max-w-lg mx-auto">Explore comprehensive video courses prepared by expert mentors across all core General Studies subjects.</p>
    </div>

    <!-- Filters Bar -->
    <div class="glass-card p-4 rounded-xl">
        <form action="{{ route('courses.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <!-- Subject Filter -->
            <div>
                <label for="subject" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Subject</label>
                <select id="subject" name="subject" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500/50">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ request('subject') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Difficulty Filter -->
            <div>
                <label for="difficulty" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Level</label>
                <select id="difficulty" name="difficulty" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500/50">
                    <option value="">All Levels</option>
                    @foreach(\App\Enums\CourseDifficulty::cases() as $level)
                        <option value="{{ $level->value }}" {{ request('difficulty') == $level->value ? 'selected' : '' }}>{{ $level->label() }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Pricing Filter -->
            <div>
                <label for="type" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pricing</label>
                <select id="type" name="type" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500/50">
                    <option value="">All Courses</option>
                    <option value="free" {{ request('type') == 'free' ? 'selected' : '' }}>Free Courses</option>
                    <option value="paid" {{ request('type') == 'paid' ? 'selected' : '' }}>Paid Courses</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition-colors">
                    Apply Filters
                </button>
                <a href="{{ route('courses.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-1.5 px-3 rounded-lg text-xs transition-colors text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Courses Grid -->
    @if($courses->isEmpty())
        <div class="glass-card p-12 rounded-xl text-center">
            <p class="text-slate-500 text-sm">No courses matching your filter criteria were found.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $course)
                <div class="glass-card rounded-xl overflow-hidden glass-card-hover flex flex-col justify-between">
                    <div>
                        <!-- Thumbnail -->
                        <div class="aspect-video w-full bg-slate-100 relative">
                            @if($course->thumbnail)
                                <img src="{{ asset('storage/' . $course->thumbnail) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-tr from-slate-100 to-slate-200 flex items-center justify-center border-b border-slate-200">
                                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ $course->subject->name }}</span>
                                </div>
                            @endif
                            <span class="absolute top-3 left-3 text-[9px] font-bold text-white uppercase tracking-widest px-2.5 py-0.5 rounded {{ $course->difficulty->value == 'beginner' ? 'bg-emerald-600' : ($course->difficulty->value == 'intermediate' ? 'bg-amber-600' : 'bg-red-600') }}">
                                {{ $course->difficulty->label() }}
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="p-5 space-y-3">
                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">{{ $course->subject->name }}</span>
                            <h3 class="font-extrabold text-slate-800 text-base line-clamp-1"><a href="{{ route('courses.show', $course->slug) }}" class="hover:text-blue-600 transition-colors">{{ $course->title }}</a></h3>
                            <p class="text-slate-500 text-xs line-clamp-2 leading-relaxed">{{ strip_tags($course->description) }}</p>
                        </div>
                    </div>

                    <!-- Footer Details -->
                    <div class="p-5 border-t border-slate-200 flex items-center justify-between text-xs font-semibold text-slate-500 mt-2">
                        <span>Duration: {{ $course->duration_hours ?? 0 }} hrs</span>
                        <div class="flex items-center gap-3">
                            <span class="text-slate-800 font-bold">
                                @if($course->is_free)
                                    <span class="text-emerald-700 font-bold uppercase tracking-wider text-[10px] bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-200">Free</span>
                                @else
                                    ₹{{ number_format($course->price) }}
                                @endif
                            </span>
                            <a href="{{ route('courses.show', $course->slug) }}" class="bg-blue-600 hover:bg-blue-500 px-3 py-1.5 rounded text-white font-bold transition-colors">View Course</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-6">
            {{ $courses->links() }}
        </div>
    @endif

</div>
@endsection
