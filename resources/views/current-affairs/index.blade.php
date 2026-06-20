@extends('layouts.app')

@section('title', 'Daily Current Affairs - THISAI')

@section('content')
<div class="space-y-8">

    <!-- Hero Header -->
    <div class="text-center py-6">
        <span class="text-xs uppercase font-bold tracking-widest text-purple-600 bg-purple-50 px-3 py-1 rounded-full border border-purple-100">Knowledge Hub</span>
        <h1 class="text-3xl font-extrabold text-slate-800 mt-2">Current Affairs & Editorial Analysis</h1>
        <p class="text-slate-600 text-sm mt-2 max-w-lg mx-auto">Get daily updates, government scheme reviews, PIB briefs, and editorial analyses designed for UPSC/IAS preparation.</p>
    </div>

    <!-- Date Navigation Strip & Archive Lookup -->
    <div class="glass-card p-6 rounded-xl space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- 7-Day Date Picker Carousel -->
            <div class="flex-1">
                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2.5">Select Date</span>
                <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin">
                    @php
                        $dates = [];
                        for ($i = 0; $i < 7; $i++) {
                            $dates[] = \Carbon\Carbon::today()->subDays($i);
                        }
                        $dates = array_reverse($dates);
                    @endphp

                    @foreach($dates as $d)
                        @php
                            $dateStr = $d->format('Y-m-d');
                            $isSelected = $selectedDate === $dateStr;
                        @endphp
                        <a href="{{ route('current-affairs.index', array_merge(request()->query(), ['date' => $dateStr])) }}"
                           class="flex flex-col items-center justify-center min-w-[70px] py-2 px-3 rounded-lg border transition-all select-none
                           {{ $isSelected 
                                ? 'bg-gradient-to-b from-blue-600 to-indigo-700 border-blue-500 shadow-lg text-white scale-105' 
                                : 'bg-white border-slate-200 text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
                            <span class="text-[10px] uppercase font-bold tracking-wider">{{ $d->format('D') }}</span>
                            <span class="text-lg font-extrabold">{{ $d->format('d') }}</span>
                            <span class="text-[9px] font-semibold opacity-80">{{ $d->format('M') }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Archive Lookup (Date Input) -->
            <div class="w-full lg:w-auto min-w-[200px]">
                <form action="{{ route('current-affairs.index') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-2">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    @if(request('subject'))
                        <input type="hidden" name="subject" value="{{ request('subject') }}">
                    @endif
                    <div class="flex-1 w-full">
                        <label for="archive_date" class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2.5">Archive Lookup</label>
                        <input type="date" id="archive_date" name="date" value="{{ $selectedDate }}"
                               class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-slate-800 focus:outline-none focus:border-blue-500/50">
                    </div>
                    <button type="submit" class="w-full sm:w-auto bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 font-bold py-1.5 px-4 rounded-lg text-xs transition-colors whitespace-nowrap">
                        Go
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Filters and Results Row -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left: Filters Sidebar -->
        <div class="space-y-6">
            <!-- Article Type Filter -->
            <div class="glass-card p-5 rounded-xl space-y-4">
                <h3 class="text-xs uppercase font-extrabold text-slate-800 tracking-widest border-b border-slate-200 pb-2">Filter by Type</h3>
                <div class="flex flex-col gap-1">
                    <a href="{{ route('current-affairs.index', array_merge(request()->query(), ['type' => ''])) }}"
                       class="flex items-center justify-between text-xs font-semibold px-3 py-2 rounded-lg transition-colors
                       {{ !request('type') ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">
                        <span>All Types</span>
                    </a>
                    @foreach(\App\Enums\CurrentAffairsType::cases() as $type)
                        <a href="{{ route('current-affairs.index', array_merge(request()->query(), ['type' => $type->value])) }}"
                           class="flex items-center justify-between text-xs font-semibold px-3 py-2 rounded-lg transition-colors
                           {{ request('type') === $type->value ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">
                            <span>{{ $type->label() }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Subject Filter -->
            <div class="glass-card p-5 rounded-xl space-y-4">
                <h3 class="text-xs uppercase font-extrabold text-slate-800 tracking-widest border-b border-slate-200 pb-2">Filter by Subject</h3>
                <div class="flex flex-col gap-1">
                    <a href="{{ route('current-affairs.index', array_merge(request()->query(), ['subject' => ''])) }}"
                       class="flex items-center justify-between text-xs font-semibold px-3 py-2 rounded-lg transition-colors
                       {{ !request('subject') ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">
                        <span>All Subjects</span>
                    </a>
                    @foreach($subjects as $sub)
                        <a href="{{ route('current-affairs.index', array_merge(request()->query(), ['subject' => $sub->id])) }}"
                           class="flex items-center justify-between text-xs font-semibold px-3 py-2 rounded-lg transition-colors
                           {{ request('subject') == $sub->id ? 'bg-blue-50 text-blue-600 border border-blue-100' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">
                            <span>{{ $sub->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Articles List -->
        <div class="lg:col-span-3 space-y-6">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Showing updates for <strong class="text-slate-800">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</strong></span>
                <span class="text-xs text-slate-500">Total articles: <strong class="text-slate-800">{{ $articles->total() }}</strong></span>
            </div>

            @if($articles->isEmpty())
                <div class="glass-card p-12 rounded-xl text-center space-y-4 border border-dashed border-slate-200">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 border border-slate-200">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    </div>
                    <div class="space-y-1">
                        <p class="text-slate-800 font-bold text-sm">No articles found</p>
                        <p class="text-slate-500 text-xs">There are no updates published on this day for the selected filters.</p>
                    </div>
                    <a href="{{ route('current-affairs.index', ['date' => \Carbon\Carbon::today()->format('Y-m-d')]) }}" class="inline-flex bg-blue-600 hover:bg-blue-500 px-4 py-1.5 text-xs font-bold text-white rounded-full transition-all">Go to Today</a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($articles as $article)
                        @php
                            $isBookmarked = auth()->user()->bookmarks()
                                ->where('bookmarkable_type', \App\Models\CurrentAffairs::class)
                                ->where('bookmarkable_id', $article->id)
                                ->exists();
                        @endphp
                        <div class="glass-card p-6 rounded-xl border border-slate-200 shadow-lg relative group flex flex-col justify-between gap-4">
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
                                            class="text-slate-500 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition-colors">
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
                                    <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200">
                                        <span class="text-[10px] font-bold text-slate-600">{{ strtoupper(substr($article->author->name ?? 'U', 0, 1)) }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-600 font-semibold">{{ $article->author->name ?? 'Faculty Mentor' }}</span>
                                </div>
                                <a href="{{ route('current-affairs.show', $article->slug) }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                    Read Article &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-6">
                    {{ $articles->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
