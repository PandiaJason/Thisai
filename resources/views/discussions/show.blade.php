@extends('layouts.app')

@section('title', $discussion->title . ' - Discussion - THISAI')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    {{-- Back Link --}}
    <a href="{{ route('discussions.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-blue-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Discussions
    </a>

    {{-- Main Question Card --}}
    <div class="glass-card p-6 rounded-2xl border border-slate-200">
        <div class="flex items-center gap-2 mb-3">
            @if($discussion->is_resolved)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Resolved
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 border border-amber-200 text-amber-700">Open</span>
            @endif
            @if($discussion->subject)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 border border-purple-100 text-purple-700">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    {{ $discussion->subject->name }}
                </span>
            @endif
        </div>

        <h1 class="text-xl font-extrabold text-slate-800 mb-3">{{ $discussion->title }}</h1>
        <div class="prose prose-sm max-w-none text-slate-600">
            {!! nl2br(e($discussion->body)) !!}
        </div>

        <div class="flex items-center justify-between mt-5 pt-4 border-t border-slate-100">
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-bold text-sm">
                    {{ strtoupper(substr($discussion->user?->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <span class="font-bold text-slate-700">{{ $discussion->user?->name ?? 'Anonymous' }}</span>
                    <span class="block text-slate-400">{{ $discussion->created_at->diffForHumans() }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if(!$discussion->is_resolved && (auth()->id() === $discussion->user_id || auth()->user()->role === \App\Enums\UserRole::FACULTY || auth()->user()->role === \App\Enums\UserRole::SUPER_ADMIN))
                    <form method="POST" action="{{ route('discussions.resolve', $discussion->id) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Mark Resolved
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Replies --}}
    <div class="space-y-3">
        <h2 class="text-sm font-extrabold text-slate-600 uppercase tracking-widest">{{ $discussion->replies->count() }} {{ Str::plural('Reply', $discussion->replies->count()) }}</h2>

        @foreach($discussion->replies as $reply)
            <div class="glass-card p-5 rounded-2xl border {{ $reply->is_accepted ? 'border-emerald-300 bg-emerald-50/30' : 'border-slate-200' }}">
                @if($reply->is_accepted)
                    <div class="flex items-center gap-1.5 mb-2 text-xs font-bold text-emerald-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Accepted Answer
                    </div>
                @endif
                <div class="prose prose-sm max-w-none text-slate-600">
                    {!! nl2br(e($reply->body)) !!}
                </div>
                <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <div class="flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-600 font-bold text-xs">
                            {{ strtoupper(substr($reply->user?->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="font-bold text-slate-600">{{ $reply->user?->name ?? 'Anonymous' }}</span>
                        <span>{{ $reply->created_at->diffForHumans() }}</span>
                    </div>
                    <button onclick="voteReply({{ $reply->id }})" class="inline-flex items-center gap-1 text-xs text-slate-400 hover:text-blue-600 transition-colors" id="vote-reply-{{ $reply->id }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        <span>{{ $reply->upvotes }}</span>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Reply Form --}}
    <div class="glass-card p-6 rounded-2xl border border-slate-200">
        <h3 class="text-sm font-extrabold text-slate-700 mb-4">Post a Reply</h3>
        <form method="POST" action="{{ route('discussions.reply', $discussion->id) }}">
            @csrf
            <textarea name="body" rows="4" required placeholder="Share your thoughts or answer this question..." class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm placeholder:text-slate-400 resize-none"></textarea>
            @error('body')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
            <div class="flex justify-end mt-3">
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl text-sm transition-all shadow-lg shadow-blue-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Post Reply
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function voteReply(replyId) {
    fetch(`/api/discussions/${replyId}/vote`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({type: 'reply'})
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const el = document.getElementById('vote-reply-' + replyId);
            el.querySelector('span').textContent = data.upvote_count;
        }
    });
}
</script>
@endpush
@endsection
