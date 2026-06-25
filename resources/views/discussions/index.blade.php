@extends('layouts.app')

@section('title', 'Discussion Forum - THISAI')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Discussion Forum</h1>
            <p class="text-slate-500 text-sm mt-1">Ask doubts, share knowledge, help each other</p>
        </div>
        <a href="{{ route('discussions.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-blue-500/20">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Ask a Question
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('discussions.index') }}" class="glass-card p-4 rounded-2xl border border-slate-200 flex flex-wrap items-end gap-4">
        <div class="flex-1 min-w-[150px]">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Subject</label>
            <select name="subject_id" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[120px]">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Status</label>
            <select name="is_resolved" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                <option value="">All</option>
                <option value="no" {{ request('is_resolved') === 'no' ? 'selected' : '' }}>Unresolved</option>
                <option value="yes" {{ request('is_resolved') === 'yes' ? 'selected' : '' }}>Resolved</option>
            </select>
        </div>
        <div class="min-w-[120px]">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-widest">Sort</label>
            <select name="sort" class="mt-1 w-full rounded-lg border-slate-300 text-sm">
                <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Recent</option>
                <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Popular</option>
            </select>
        </div>
        <button type="submit" class="bg-slate-800 text-white font-bold px-4 py-2 rounded-lg text-sm hover:bg-slate-700 transition-all">Filter</button>
    </form>

    {{-- Discussion List --}}
    <div class="space-y-3">
        @forelse($discussions as $discussion)
            <a href="{{ route('discussions.show', $discussion->id) }}" class="block glass-card p-5 rounded-2xl border border-slate-200 hover:border-blue-200 hover:shadow-md transition-all group">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            @if($discussion->is_resolved)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 border border-emerald-100 text-emerald-700">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Resolved
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 border border-amber-100 text-amber-700">Open</span>
                            @endif
                            @if($discussion->subject)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 border border-purple-100 text-purple-700">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    {{ $discussion->subject->name }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition-colors truncate">{{ $discussion->title }}</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ Str::limit(strip_tags($discussion->body), 150) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 mt-3 text-xs text-slate-400">
                    <span class="font-semibold text-slate-600">{{ $discussion->user?->name ?? 'Anonymous' }}</span>
                    <span>{{ $discussion->created_at->diffForHumans() }}</span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        {{ $discussion->reply_count }}
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        {{ $discussion->upvotes }}
                    </span>
                </div>
            </a>
        @empty
            <div class="glass-card p-12 rounded-2xl border border-slate-200 text-center">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="text-sm font-semibold text-slate-500">No discussions yet</p>
                <p class="text-xs text-slate-400 mt-1">Be the first to ask a question!</p>
            </div>
        @endforelse
    </div>

    {{ $discussions->links() }}
</div>
@endsection
