@extends('layouts.app')

@section('title', $article->title . ' - THISAI')

@section('content')
<div class="space-y-6">
    <!-- Back Navigation -->
    <div>
        <a href="{{ route('current-affairs.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-850 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Back to Archive
        </a>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Article Content (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <article class="glass-card p-8 rounded-2xl border border-slate-200 shadow-xl space-y-6">
                <!-- Header Metadata -->
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                            {{ $article->subject->name ?? 'General Studies' }}
                        </span>
                        <span class="text-[10px] uppercase font-bold tracking-widest text-purple-600 bg-purple-50 px-3 py-1 rounded-full border border-purple-100">
                            {{ $article->type->label() }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 leading-tight">
                        {{ $article->title }}
                    </h1>

                    <div class="flex items-center justify-between border-y border-slate-200 py-3 mt-4 text-xs text-slate-500">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200">
                                <span class="font-bold text-slate-600">{{ strtoupper(substr($article->author->name ?? 'U', 0, 1)) }}</span>
                            </div>
                            <div>
                                <span class="block font-bold text-slate-700">{{ $article->author->name ?? 'Faculty Mentor' }}</span>
                                <span class="block text-[10px] text-slate-500">Published on {{ $article->publish_date->format('F d, Y') }}</span>
                            </div>
                        </div>

                        <!-- Bookmark Button -->
                        @php
                            $isBookmarked = $article->isBookmarkedByUser(auth()->user());
                        @endphp
                        <button onclick="toggleBookmark('App\\Models\\CurrentAffairs', {{ $article->id }}, this)" 
                                class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 hover:border-slate-300 text-slate-600 hover:text-slate-800 px-3 py-1.5 rounded-lg transition-colors text-xs font-semibold">
                            <svg class="w-4 h-4 {{ $isBookmarked ? 'text-yellow-400 fill-yellow-400' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                            </svg>
                            <span>{{ $isBookmarked ? 'Saved' : 'Bookmark' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Article Body Content -->
                <div class="prose max-w-none text-slate-700 text-sm leading-relaxed space-y-4">
                    {!! $article->content !!}
                </div>

                <!-- Tags Section -->
                @if($article->tags && count($article->tags) > 0)
                    <div class="border-t border-slate-200 pt-6">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">Topic Tags</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach($article->tags as $tag)
                                <span class="text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-1 rounded-full border border-slate-200 transition-colors">
                                    #{{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </article>
        </div>

        <!-- Right: Related Articles Sidebar (1/3 width) -->
        <div class="space-y-6">
            <div class="glass-card p-6 rounded-2xl border border-slate-200 shadow-xl space-y-4">
                <h3 class="text-xs uppercase font-extrabold text-slate-800 tracking-widest border-b border-slate-200 pb-2.5">Related Updates</h3>
                
                @if($related->isEmpty())
                    <p class="text-slate-500 text-xs py-4 text-center">No related articles found.</p>
                @else
                    <div class="space-y-4">
                        @foreach($related as $rel)
                            <div class="space-y-2 border-b border-slate-200 last:border-b-0 pb-3 last:pb-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[8px] uppercase font-bold tracking-widest text-purple-600 bg-purple-50 px-2 py-0.5 rounded">
                                        {{ $rel->type->label() }}
                                    </span>
                                    <span class="text-[9px] text-slate-500 font-semibold">{{ $rel->publish_date->format('M d, Y') }}</span>
                                </div>
                                <h4 class="font-extrabold text-sm text-slate-800 hover:text-blue-600 transition-colors line-clamp-2 leading-snug">
                                    <a href="{{ route('current-affairs.show', $rel->slug) }}">
                                        {{ $rel->title }}
                                    </a>
                                </h4>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Preparation Tip Box -->
            <div class="glass-card p-6 rounded-2xl border border-blue-100 bg-gradient-to-tr from-blue-50/50 to-indigo-50/30 shadow-xl space-y-3">
                <div class="flex items-center gap-2 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                    <span class="text-xs uppercase font-extrabold tracking-widest">Mentor Advice</span>
                </div>
                <p class="text-slate-600 text-xs leading-relaxed">
                    Read the Editorial Analyses thoroughly. We recommend making short notes on your bookmarked articles for quick reference during mains revision exams.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
