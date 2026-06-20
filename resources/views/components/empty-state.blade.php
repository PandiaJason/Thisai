@props(['title', 'description', 'actionUrl' => null, 'actionText' => null])

<div class="glass-card p-12 rounded-xl text-center space-y-4 border border-dashed border-slate-200">
    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 border border-slate-200">
        @if($slot->isEmpty())
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v2m16 0V5a2 2 0 00-2-2H6a2 2 0 00-2 2v2" />
            </svg>
        @else
            {{ $slot }}
        @endif
    </div>
    <div class="space-y-1">
        <p class="text-slate-800 font-bold text-sm">{{ $title }}</p>
        <p class="text-slate-500 text-xs">{{ $description }}</p>
    </div>
    @if($actionUrl && $actionText)
        <a href="{{ $actionUrl }}" class="inline-flex bg-blue-600 hover:bg-blue-500 px-4 py-1.5 text-xs font-bold text-white rounded-full transition-all">
            {{ $actionText }}
        </a>
    @endif
</div>
