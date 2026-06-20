@props(['article'])

@php
    $isBookmarked = auth()->user()->bookmarks()
        ->where('bookmarkable_type', \App\Models\CurrentAffairs::class)
        ->where('bookmarkable_id', $article->id)
        ->exists();
@endphp

<div class="glass-card p-6 relative group flex flex-col justify-between gap-4">
    <div>
        <div class="flex items-center justify-between gap-4">
            <!-- Badges -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[9px] uppercase font-bold tracking-widest text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">
                    {{ $article->subject->name ?? 'General Studies' }}
                </span>
                <span class="text-[9px] uppercase font-bold tracking-widest text-purple-600 bg-purple-50 px-2 py-0.5 rounded border border-purple-100">
                    {{ $article->type->label() }}
                </span>
            </div>
            <!-- Bookmark Button -->
            <button onclick="toggleBookmark('App\\Models\\CurrentAffairs', {{ $article->id }}, this)" 
                    class="text-slate-400 hover:text-blue-600 p-1 rounded hover:bg-blue-50 transition-colors">
                <svg class="w-5 h-5 {{ $isBookmarked ? 'text-yellow-400 fill-yellow-400' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
            </button>
        </div>

        <h2 class="text-lg font-extrabold text-slate-800 mt-3 group-hover:text-blue-600 transition-colors leading-snug">
            <a href="{{ route('current-affairs.show', $article->slug) }}">
                {{ $article->title }}
            </a>
        </h2>

        <p class="text-slate-500 text-xs mt-2 line-clamp-3 leading-relaxed">
            {{ strip_tags($article->content) }}
        </p>
    </div>

    <div class="flex items-center justify-between border-t border-slate-200 pt-4 mt-2">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center border border-blue-200">
                <span class="text-[10px] font-bold text-blue-600">{{ strtoupper(substr($article->author->name ?? 'U', 0, 1)) }}</span>
            </div>
            <span class="text-[10px] text-slate-600 font-semibold">{{ $article->author->name ?? 'Faculty Mentor' }}</span>
        </div>
        <a href="{{ route('current-affairs.show', $article->slug) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
            Read Article &rarr;
        </a>
    </div>
</div>
