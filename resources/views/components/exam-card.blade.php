@props(['exam'])

@php
    $attempt = $exam->getUserAttempt(auth()->user());
@endphp

<div class="glass-card p-6 flex flex-col justify-between gap-5">
    <div class="space-y-3.5">
        <div class="flex items-center justify-between">
            <span class="text-[9px] font-bold text-blue-600 uppercase tracking-widest bg-blue-50 px-2.5 py-0.5 rounded border border-blue-100">
                {{ $exam->type->label() }}
            </span>
            <span class="text-xs font-semibold text-slate-500">{{ $exam->duration_minutes }} Mins</span>
        </div>
        <h3 class="font-extrabold text-slate-800 text-base leading-tight">{{ $exam->title }}</h3>
        <p class="text-slate-500 text-xs line-clamp-2 leading-relaxed">{{ $exam->description ?? 'No description provided.' }}</p>
    </div>

    <div class="border-t border-slate-200 pt-4 flex items-center justify-between mt-2">
        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider space-y-1">
            <div>Marks: <span class="text-slate-800 font-bold">{{ $exam->total_marks }}</span></div>
            @if($exam->negative_marking > 0)
                <div class="text-red-600">Negative: -{{ round($exam->negative_marking * 100) }}%</div>
            @endif
        </div>
        
        @if($attempt)
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-600">Score: {{ $attempt->score }} pts</span>
                <a href="{{ route('results.show', $attempt->session_token) }}" class="bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors">Results</a>
            </div>
        @else
            <form action="{{ route('exams.start', $exam->slug) }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-lg text-xs font-bold text-white transition-colors shadow-md active:scale-[0.98]">
                    Start Test
                </button>
            </form>
        @endif
    </div>
</div>
