@extends('layouts.app')

@section('title', 'Notifications - THISAI')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Notifications</h1>
            <p class="text-xs text-slate-500 mt-1">Stay updated with course announcements, quiz schedules, and academy news.</p>
        </div>
        
        @if($notifications->count() > 0 && auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="bg-slate-100 hover:bg-slate-200 border border-slate-200 hover:border-slate-300 text-xs font-bold text-blue-600 hover:text-blue-700 py-1.5 px-3 rounded-lg transition-colors">
                    Mark All as Read
                </button>
            </form>
        @endif
    </div>

    <!-- Notifications List -->
    @if($notifications->isEmpty())
        <div class="glass-card p-12 rounded-xl text-center space-y-4 border border-dashed border-slate-200">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 border border-slate-200">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            </div>
            <div class="space-y-1">
                <p class="text-slate-800 font-bold text-sm">All caught up!</p>
                <p class="text-slate-500 text-xs">You have no new notifications from the academy.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex bg-blue-600 hover:bg-blue-500 px-4 py-1.5 text-xs font-bold text-white rounded-full transition-all">Go to Dashboard</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($notifications as $n)
                @php $isUnread = is_null($n->read_at); @endphp
                <div class="glass-card p-5 rounded-xl border border-slate-200 shadow-md flex items-start justify-between gap-4 transition-all hover:border-slate-300 relative
                     {{ $isUnread ? 'border-l-4 border-l-blue-500 bg-blue-500/[0.02]' : '' }}">
                    
                    <div class="flex gap-3">
                        <!-- Notification Icon based on type or generic -->
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5
                             {{ $isUnread ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        
                        <div class="space-y-1">
                            <p class="text-sm leading-relaxed {{ $isUnread ? 'text-slate-800 font-bold' : 'text-slate-600' }}">
                                {{ $n->data['message'] ?? $n->data['title'] ?? 'System Announcement' }}
                            </p>
                            <div class="flex items-center gap-2 text-[10px] text-slate-500 font-semibold">
                                <span>{{ $n->created_at->diffForHumans() }}</span>
                                @if($n->data['action_url'] ?? null)
                                    <span>&bull;</span>
                                    <a href="{{ $n->data['action_url'] }}" class="text-blue-600 hover:text-blue-700">View details</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($isUnread)
                        <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="shrink-0">
                            @csrf
                            <button type="submit" title="Mark as read" class="text-slate-500 hover:text-slate-700 p-1 rounded hover:bg-slate-100 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="pt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
