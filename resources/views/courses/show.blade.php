@extends('layouts.app')

@section('title', $course->title . ' - THISAI')

@section('content')
<div class="space-y-8">

    <!-- Course Hero Section -->
    <div class="glass-card p-6 sm:p-8 rounded-2xl border border-slate-200 relative flex flex-col md:flex-row gap-8 items-center overflow-hidden">
        <div class="absolute w-96 h-96 bg-blue-500/5 rounded-full blur-3xl -top-12 -left-12"></div>
        
        <!-- Thumbnail -->
        <div class="w-full md:w-1/3 aspect-video bg-slate-100 rounded-xl overflow-hidden relative border border-slate-200 shrink-0">
            @if($course->thumbnail)
                <img src="{{ asset('storage/' . $course->thumbnail) }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-tr from-slate-100 to-slate-200 flex items-center justify-center">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">{{ $course->subject->name }}</span>
                </div>
            @endif
        </div>

        <!-- Course Meta -->
        <div class="flex-1 space-y-4 relative z-10">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded border border-blue-100 uppercase tracking-wider">{{ $course->subject->name }}</span>
                <span class="text-xs font-bold text-slate-800 bg-slate-100 px-2.5 py-0.5 rounded border border-slate-200 uppercase tracking-wider">{{ $course->difficulty->label() }}</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 leading-tight">{{ $course->title }}</h1>
            
            <div class="flex items-center gap-4 text-xs font-semibold text-slate-400">
                <span>Instructor: Dr. Ramesh Kumar</span>
                <span>&bull;</span>
                <span>Duration: {{ $course->duration_hours ?? 0 }} Hours</span>
            </div>

            <div class="flex items-center gap-6 pt-4 border-t border-slate-200">
                @if($isEnrolled)
                    <div class="flex-1 max-w-xs space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                            <span>Syllabus Completed</span>
                            <span class="text-blue-600">{{ $progress }}%</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    @if($course->sections->isNotEmpty() && $course->sections->first()->videos->isNotEmpty())
                        <a href="{{ route('videos.watch', $course->sections->first()->videos->first()->id) }}" class="bg-blue-600 hover:bg-blue-500 px-6 py-2 rounded-lg text-sm font-bold text-white transition-colors text-center shadow-lg hover:shadow-blue-500/10 active:scale-[0.98]">
                            Continue Learning
                        </a>
                    @endif
                @else
                    <div class="flex items-center gap-4">
                        <span class="text-2xl font-black text-slate-800">
                            @if($course->is_free)
                                <span class="text-emerald-700 font-bold uppercase tracking-wider text-xs bg-emerald-50 px-3 py-1 rounded border border-emerald-200">Free</span>
                            @else
                                ₹{{ number_format($course->price) }}
                            @endif
                        </span>
                        <form action="{{ route('courses.enroll', $course->slug) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 px-8 py-2.5 rounded-lg text-sm font-extrabold text-white transition-colors shadow-lg hover:shadow-blue-500/10 active:scale-[0.98]">
                                Enroll Now
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Syllabus Details Accordion -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Curriculum (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span> Course Syllabus Modules
            </h2>

            @if($course->sections->isEmpty())
                <div class="glass-card p-8 rounded-xl text-center">
                    <p class="text-slate-400 text-sm">Syllabus modules are currently being prepared by faculty mentors.</p>
                </div>
            @else
                <div class="space-y-4" x-data="{ activeSection: 0 }">
                    @foreach($course->sections as $index => $section)
                        <div class="glass-card rounded-xl overflow-hidden border border-slate-200">
                            <!-- Section Header -->
                            <button @click="activeSection = (activeSection === {{ $index }} ? null : {{ $index }})" class="w-full p-5 text-left flex items-center justify-between font-bold text-sm text-slate-800 focus:outline-none hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 bg-slate-100 border border-slate-200 text-xs font-semibold rounded flex items-center justify-center text-slate-600">{{ $index + 1 }}</span>
                                    <span>{{ $section->title }}</span>
                                </div>
                                <svg class="w-5 h-5 text-slate-400 transform transition-transform duration-200" :class="{ 'rotate-180': activeSection === {{ $index }} }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <!-- Section Content -->
                            <div x-show="activeSection === {{ $index }}" class="border-t border-slate-200 p-4 space-y-2 bg-slate-50/50">
                                @if($section->videos->isEmpty())
                                    <p class="text-xs text-slate-500 p-2">No videos available in this section yet.</p>
                                @else
                                    @foreach($section->videos as $video)
                                        <div class="flex items-center justify-between p-2.5 hover:bg-slate-100 rounded-lg transition-colors text-xs">
                                            <div class="flex items-center gap-3">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                <span class="font-medium text-slate-700">{{ $video->title }}</span>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <span class="text-slate-500">{{ $video->duration_formatted }}</span>
                                                @if($isEnrolled)
                                                    <a href="{{ route('videos.watch', $video->id) }}" class="text-blue-600 hover:text-blue-800 font-bold">Watch &rarr;</a>
                                                @else
                                                    @if($video->is_free)
                                                        <a href="{{ route('videos.watch', $video->id) }}" class="text-emerald-700 font-bold uppercase tracking-wider text-[9px] bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Preview</a>
                                                    @else
                                                        <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Description / About (1/3 width) -->
        <div class="space-y-6">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                                <span class="w-1 h-5 bg-purple-500 rounded-full"></span> About Course
                            </h2>
                            <div class="glass-card p-6 rounded-2xl space-y-4 text-sm text-slate-600 leading-relaxed">
                {!! $course->description !!}
            </div>
        </div>

    </div>

</div>
@endsection
