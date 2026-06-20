@extends('layouts.app')

@section('title', 'Search Results - THISAI')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="space-y-2 border-b border-slate-200 pb-4">
        <h1 class="text-2xl font-extrabold text-slate-800">Search Results</h1>
        <p class="text-xs text-slate-500">
            Showing matches for <strong class="text-slate-800">"{{ $query }}"</strong>
            &bull; found <strong class="text-slate-800">{{ $results['total_count'] ?? 0 }}</strong> matching records
        </p>
    </div>

    <!-- Check for Empty Results -->
    @if(($results['total_count'] ?? 0) === 0)
        <div class="glass-card p-12 rounded-xl text-center space-y-4 border border-dashed border-slate-200 max-w-xl mx-auto">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 border border-slate-200">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <div class="space-y-1">
                <p class="text-slate-800 font-bold text-sm">No matches found</p>
                <p class="text-slate-500 text-xs">We couldn't find anything matching your query. Try different keywords or double check spelling.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex bg-blue-600 hover:bg-blue-500 px-4 py-1.5 text-xs font-bold text-white rounded-full transition-all">Go to Dashboard</a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Primary Sections (Courses & Videos) -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Courses matches -->
                 @if($results['courses']->isNotEmpty())
                    <div class="space-y-4">
                        <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-1 h-4 bg-blue-500 rounded-full"></span> Courses ({{ $results['courses']->count() }})
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($results['courses'] as $course)
                                <div class="glass-card p-4 rounded-xl flex flex-col justify-between gap-4 hover:border-slate-300 transition-colors">
                                    <div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[9px] uppercase font-bold tracking-widest text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                                                {{ $course->subject->name ?? 'General Studies' }}
                                            </span>
                                            <span class="text-[9px] uppercase font-bold tracking-widest text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                                {{ $course->difficulty->label() }}
                                            </span>
                                        </div>
                                        <h3 class="font-extrabold text-slate-800 mt-2 text-sm line-clamp-1"><a href="{{ route('courses.show', $course->slug) }}" class="hover:text-blue-600 transition-colors">{{ $course->title }}</a></h3>
                                        <p class="text-slate-600 text-xs mt-1 line-clamp-2 leading-relaxed">{{ strip_tags($course->description) }}</p>
                                    </div>
                                    <div class="flex items-center justify-between text-xs border-t border-slate-200 pt-3 mt-1">
                                        <span class="text-slate-500 font-semibold">{{ $course->duration_hours ?? 0 }} hrs</span>
                                        <a href="{{ route('courses.show', $course->slug) }}" class="text-blue-600 hover:text-blue-700 font-bold">View Course &rarr;</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Videos matches -->
                 @if($results['videos']->isNotEmpty())
                    <div class="space-y-4">
                        <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-1 h-4 bg-purple-500 rounded-full"></span> Lessons & Videos ({{ $results['videos']->count() }})
                        </h2>
                        <div class="glass-card divide-y divide-slate-200 rounded-xl overflow-hidden shadow-lg">
                            @foreach($results['videos'] as $video)
                                <div class="p-4 flex items-start sm:items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[8px] uppercase font-bold tracking-widest text-purple-600 bg-purple-50 px-2 py-0.5 rounded">Lesson</span>
                                            @if($video->course)
                                                <span class="text-[9px] text-slate-500 font-semibold truncate max-w-[120px]">{{ $video->course->title }}</span>
                                            @endif
                                        </div>
                                        <h3 class="font-bold text-slate-800 text-sm"><a href="{{ route('videos.watch', $video->id) }}" class="hover:text-blue-600 transition-colors">{{ $video->title }}</a></h3>
                                    </div>
                                    <a href="{{ route('videos.watch', $video->id) }}" class="bg-slate-100 hover:bg-slate-200 border border-slate-200 hover:border-slate-300 text-slate-700 font-bold py-1.5 px-3 rounded-lg text-xs transition-colors whitespace-nowrap">
                                        Watch Video
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            <!-- Right Column: Secondary Sections (Current Affairs & Exams) -->
            <div class="space-y-8">
                
                <!-- Current Affairs matches -->
                 @if($results['current_affairs']->isNotEmpty())
                    <div class="space-y-4">
                        <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-1 h-4 bg-emerald-500 rounded-full"></span> Current Affairs ({{ $results['current_affairs']->count() }})
                        </h2>
                        <div class="glass-card divide-y divide-slate-200 rounded-xl overflow-hidden shadow-lg">
                            @foreach($results['current_affairs'] as $article)
                                <div class="p-4 space-y-2 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[8px] uppercase font-bold tracking-widest text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">
                                            {{ $article->type->label() }}
                                        </span>
                                        <span class="text-[9px] text-slate-500 font-semibold">{{ $article->publish_date->format('M d, Y') }}</span>
                                    </div>
                                    <h3 class="font-bold text-slate-800 text-sm line-clamp-1"><a href="{{ route('current-affairs.show', $article->slug) }}" class="hover:text-blue-600 transition-colors">{{ $article->title }}</a></h3>
                                    <p class="text-slate-600 text-[11px] line-clamp-2 leading-relaxed">{{ strip_tags($article->content) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Exams matches -->
                 @if($results['exams']->isNotEmpty())
                    <div class="space-y-4">
                        <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <span class="w-1 h-4 bg-amber-500 rounded-full"></span> Test Series & Quizzes ({{ $results['exams']->count() }})
                        </h2>
                        <div class="glass-card divide-y divide-slate-200 rounded-xl overflow-hidden shadow-lg">
                            @foreach($results['exams'] as $exam)
                                <div class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
                                    <div class="space-y-1">
                                        <span class="text-[8px] uppercase font-bold tracking-widest text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-100">
                                            {{ $exam->type->label() }}
                                        </span>
                                        <h3 class="font-bold text-slate-800 text-sm leading-snug"><a href="{{ route('exams.index') }}" class="hover:text-blue-600 transition-colors">{{ $exam->title }}</a></h3>
                                    </div>
                                    <a href="{{ route('exams.index') }}" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-1.5 px-3 rounded-lg text-xs transition-colors whitespace-nowrap">
                                        View Tests
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

        </div>
    @endif
</div>
@endsection
