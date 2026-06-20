@extends('layouts.app')

@section('title', 'Exam Results - THISAI')

@section('content')
<div class="space-y-8">
    
    <!-- Hero Header -->
    <div class="text-center py-6">
        <h1 class="text-3xl font-extrabold text-slate-800">Performance Scorecard</h1>
        <p class="text-slate-500 text-sm mt-2">Test: {{ $exam->title }} &bull; Submitted on {{ $attempt->submitted_at->format('M d, Y h:i A') }}</p>
    </div>

    <!-- Analytics Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="glass-card p-6 rounded-2xl flex flex-col items-center text-center space-y-2 border border-slate-200">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Final Score</span>
            <span class="text-3xl font-black text-slate-800">{{ $attempt->score }} / {{ $attempt->total_marks }}</span>
            <span class="text-xs text-slate-500">Marks Obtained</span>
        </div>

        <div class="glass-card p-6 rounded-2xl flex flex-col items-center text-center space-y-2 border border-slate-200">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Class Rank</span>
            <span class="text-3xl font-black text-amber-600">#{{ $attempt->rank ?? 'N/A' }}</span>
            <span class="text-xs text-slate-500">Among peers</span>
        </div>

        <div class="glass-card p-6 rounded-2xl flex flex-col items-center text-center space-y-2 border border-slate-200">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Percentile Score</span>
            <span class="text-3xl font-black text-blue-600">{{ $attempt->percentile }}%</span>
            <span class="text-xs text-slate-500">Better than peers</span>
        </div>

        <div class="glass-card p-6 rounded-2xl flex flex-col items-center text-center space-y-2 border border-slate-200">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Test Accuracy</span>
            <span class="text-3xl font-black text-emerald-600">{{ $attempt->accuracy }}%</span>
            <span class="text-xs text-slate-500">Correct answers ratio</span>
        </div>
    </div>

    <!-- Stats Breakdown Chart row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Attempt Distribution (1/3 width) -->
        <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Question Stats</h3>
            <div class="h-48">
                <canvas id="attemptDistributionChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center text-xs font-semibold">
                <div class="bg-emerald-50 p-2.5 rounded border border-emerald-100 text-emerald-700"><span class="block text-emerald-700 text-lg font-bold">{{ $attempt->correct_count }}</span> Correct</div>
                <div class="bg-red-50 p-2.5 rounded border border-red-100 text-red-700"><span class="block text-red-700 text-lg font-bold">{{ $attempt->wrong_count }}</span> Wrong</div>
                <div class="bg-slate-100 p-2.5 rounded border border-slate-200"><span class="block text-slate-600 text-lg font-bold">{{ $attempt->unanswered_count }}</span> Left</div>
            </div>
        </div>

        <!-- Right: Answers Key review (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span> Key Answers & Explanations
            </h3>

            <div class="space-y-4">
                @foreach($questions as $index => $question)
                    @php $answer = $answers->get($question->id); @endphp
                    <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-4 relative overflow-hidden">
                        
                        <!-- Status indicator strip on left border -->
                        @if($answer && $answer->is_correct === true)
                            <div class="absolute top-0 bottom-0 left-0 w-1.5 bg-emerald-500"></div>
                        @elseif($answer && $answer->is_correct === false)
                            <div class="absolute top-0 bottom-0 left-0 w-1.5 bg-red-500"></div>
                        @else
                            <div class="absolute top-0 bottom-0 left-0 w-1.5 bg-slate-300"></div>
                        @endif

                        <!-- Question Meta -->
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-500 border-b border-slate-200 pb-2">
                            <span>Question {{ $index + 1 }}</span>
                            <span class="flex items-center gap-2">
                                @if($answer && $answer->is_correct === true)
                                    <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Correct</span>
                                @elseif($answer && $answer->is_correct === false)
                                    <span class="text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-100">Wrong</span>
                                @else
                                    <span class="text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">Unanswered</span>
                                @endif
                                <span>Marks: {{ $answer ? $answer->marks_obtained : 0 }}</span>
                            </span>
                        </div>

                        <!-- Question Text -->
                        <div class="text-sm font-bold text-slate-800">{!! $question->question_text !!}</div>

                        <!-- Option Listing -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                            @foreach($question->options as $opt)
                                @php 
                                    $isSelected = $answer && is_array($answer->selected_option_ids) && in_array($opt->id, $answer->selected_option_ids); 
                                    $isCorrect = $opt->is_correct;
                                @endphp
                                <div class="p-3 rounded-lg border {{ 
                                    $isCorrect ? 'bg-emerald-50 border-emerald-100 text-emerald-700 font-bold' : 
                                    ($isSelected ? 'bg-red-50 border-red-100 text-red-700 font-bold' : 'bg-slate-50 border-slate-200 text-slate-600')
                                }} flex items-start gap-2">
                                    <span class="font-bold">{{ $opt->is_correct ? '✓' : ($isSelected ? '✗' : '•') }}</span>
                                    <span>{{ $opt->option_text }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Explanation -->
                        @if($question->explanation)
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                                <span class="font-extrabold text-slate-800 block">Explanation:</span>
                                <p class="leading-relaxed">{!! $question->explanation !!}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

<!-- Chart Script -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('attemptDistributionChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Correct', 'Wrong', 'Unanswered'],
                datasets: [{
                    data: [{{ $attempt->correct_count }}, {{ $attempt->wrong_count }}, {{ $attempt->unanswered_count }}],
                    backgroundColor: ['#10b981', '#ef4444', '#475569'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '75%'
            }
        });
    });
</script>
@endsection
