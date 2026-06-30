@extends('layouts.app')

@section('title', 'Exam Results - THISAI')

@section('content')
<div class="space-y-8">

    {{-- Hero Header --}}
    <div class="text-center py-6">
        <h1 class="text-3xl font-extrabold text-slate-800">Performance Scorecard</h1>
        <p class="text-slate-500 text-sm mt-2">Test: {{ $exam->title }} &bull; Submitted on {{ $attempt->submitted_at->format('M d, Y h:i A') }}</p>
    </div>

    {{-- Analytics Stats Cards --}}
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

    {{-- ============================================================ --}}
    {{-- SECTION A: Subject-wise Performance --}}
    {{-- ============================================================ --}}
    @if(!empty($subjectBreakdown) && count($subjectBreakdown) > 0)
    <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-6">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            Subject-wise Performance
        </h3>

        {{-- Horizontal Bar Chart --}}
        <div class="h-64">
            <canvas id="subjectAccuracyChart"></canvas>
        </div>

        {{-- Subject Cards Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($subjectBreakdown as $subj)
                @php
                    $acc = $subj['accuracy'] ?? 0;
                    $accColor = $acc >= 70 ? 'text-emerald-700 bg-emerald-50 border-emerald-100' : ($acc >= 50 ? 'text-amber-700 bg-amber-50 border-amber-100' : 'text-red-700 bg-red-50 border-red-100');
                    $barColor = $acc >= 70 ? 'bg-emerald-500' : ($acc >= 50 ? 'bg-amber-500' : 'bg-red-500');
                @endphp
                <div class="p-4 rounded-xl border border-slate-200 space-y-2">
                    <span class="text-xs font-bold text-slate-800 block">{{ $subj['name'] ?? 'Unknown' }}</span>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span>{{ $subj['correct'] ?? 0 }}/{{ $subj['total'] ?? 0 }} correct</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $accColor }}">{{ round($acc) }}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="{{ $barColor }} h-full rounded-full" style="width: {{ $acc }}%"></div>
                    </div>
                    <span class="text-[10px] text-slate-400 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Avg: {{ round($subj['avg_time'] ?? 0) }}s/question
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Summary line --}}
        @if(!empty($analyticsSummary))
        <div class="flex flex-wrap items-center gap-4 text-xs font-semibold border-t border-slate-200 pt-4">
            @if(!empty($analyticsSummary['strongest_subject']))
            <span class="inline-flex items-center gap-1.5 text-emerald-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                Strongest: {{ $analyticsSummary['strongest_subject'] }}
            </span>
            @endif
            @if(!empty($analyticsSummary['weakest_subject']))
            <span class="inline-flex items-center gap-1.5 text-red-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                Needs Work: {{ $analyticsSummary['weakest_subject'] }}
            </span>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- SECTION B: Time Intelligence --}}
    {{-- ============================================================ --}}
    @if(!empty($timeDistribution) || !empty($analyticsSummary))
    <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-6">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            Time Intelligence
        </h3>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Left: Time Distribution Donut --}}
            <div>
                <div class="h-64">
                    <canvas id="timeDistributionChart"></canvas>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-4 text-[10px] font-semibold text-center">
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> Quick Correct</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Quick Wrong</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span> Medium Correct</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-400"></span> Medium Wrong</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-teal-400"></span> Slow Correct</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span> Slow Wrong</div>
                </div>
            </div>

            {{-- Right: Summary Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Fastest Subject --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-amber-50/50 space-y-1">
                    <div class="flex items-center gap-2 text-amber-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        <span class="text-[10px] font-bold uppercase tracking-widest">Fastest Subject</span>
                    </div>
                    <span class="text-sm font-bold text-slate-800 block">{{ $analyticsSummary['fastest_subject'] ?? 'N/A' }}</span>
                    <span class="text-xs text-slate-500">Avg {{ round($analyticsSummary['fastest_subject_time'] ?? 0) }}s/question</span>
                </div>

                {{-- Slowest Subject --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-1">
                    <div class="flex items-center gap-2 text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-[10px] font-bold uppercase tracking-widest">Slowest Subject</span>
                    </div>
                    <span class="text-sm font-bold text-slate-800 block">{{ $analyticsSummary['slowest_subject'] ?? 'N/A' }}</span>
                    <span class="text-xs text-slate-500">Avg {{ round($analyticsSummary['slowest_subject_time'] ?? 0) }}s/question</span>
                </div>

                {{-- Quick Solves --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-emerald-50/50 space-y-1">
                    <div class="flex items-center gap-2 text-emerald-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-[10px] font-bold uppercase tracking-widest">Quick Solves</span>
                    </div>
                    <span class="text-2xl font-black text-slate-800 block">{{ $analyticsSummary['quick_solve_count'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500">Fast & accurate answers</span>
                </div>

                {{-- Careless Errors --}}
                <div class="p-4 rounded-xl border border-slate-200 bg-red-50/50 space-y-1">
                    <div class="flex items-center gap-2 text-red-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                        <span class="text-[10px] font-bold uppercase tracking-widest">Careless Errors</span>
                    </div>
                    <span class="text-2xl font-black text-slate-800 block">{{ $analyticsSummary['careless_error_count'] ?? 0 }}</span>
                    <span class="text-xs text-slate-500">Quick but wrong answers</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- Stats Breakdown Chart row + Answer Review --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left: Attempt Distribution (1/3 width) --}}
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

        {{-- Right: Answers Key review (2/3 width) --}}
        <div class="lg:col-span-2 space-y-6">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest flex items-center gap-2">
                <span class="w-1 h-5 bg-blue-500 rounded-full"></span> Key Answers & Explanations
            </h3>

            <div class="space-y-4">
                @foreach($questions as $index => $question)
                    @php
                        $answer = $answers->get($question->id);
                        $qAnalytics = $questionAnalytics[$question->id] ?? null;
                        $subjectName = $question->subject->name ?? null;
                        $topicName = $qAnalytics['topic'] ?? null;
                        $timeSpent = $qAnalytics['time_spent'] ?? ($answer->time_spent_seconds ?? null);
                        $timeCategory = $qAnalytics['time_category'] ?? null;
                        $insightType = $qAnalytics['insight'] ?? null;
                        $insightLabel = $qAnalytics['insight_label'] ?? null;
                    @endphp
                    <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-4 relative overflow-hidden">

                        {{-- Status indicator strip on left border --}}
                        @if($answer && $answer->is_correct === true)
                            <div class="absolute top-0 bottom-0 left-0 w-1.5 bg-emerald-500"></div>
                        @elseif($answer && $answer->is_correct === false)
                            <div class="absolute top-0 bottom-0 left-0 w-1.5 bg-red-500"></div>
                        @else
                            <div class="absolute top-0 bottom-0 left-0 w-1.5 bg-slate-300"></div>
                        @endif

                        {{-- Question Meta --}}
                        <div class="flex items-start justify-between text-xs font-semibold text-slate-500 border-b border-slate-200 pb-2 gap-2">
                            <div class="flex items-center flex-wrap gap-2">
                                <span>Question {{ $index + 1 }}</span>

                                {{-- Subject pill badge --}}
                                @if($subjectName)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 border border-purple-100 text-purple-700">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                    {{ $subjectName }}
                                </span>
                                @endif

                                {{-- Topic pill --}}
                                @if($topicName)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 border border-indigo-100 text-indigo-700">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                    {{ $topicName }}
                                </span>
                                @endif

                                {{-- Time badge --}}
                                @if($timeSpent !== null && $timeCategory)
                                    @php
                                        $timeBadgeColors = match($timeCategory) {
                                            'quick' => 'bg-emerald-50 border-emerald-100 text-emerald-700',
                                            'medium' => 'bg-blue-50 border-blue-100 text-blue-700',
                                            'slow' => 'bg-orange-50 border-orange-100 text-orange-700',
                                            default => 'bg-slate-50 border-slate-200 text-slate-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border {{ $timeBadgeColors }}">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        {{ $timeSpent }}s &mdash; {{ ucfirst($timeCategory) }}
                                    </span>
                                @endif

                                {{-- Insight badge --}}
                                @if($insightType && $insightLabel)
                                    @php
                                        $insightConfig = match($insightType) {
                                            'quick_solve' => ['color' => 'bg-emerald-50 border-emerald-100 text-emerald-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />'],
                                            'careless_error' => ['color' => 'bg-red-50 border-red-100 text-red-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />'],
                                            'well_solved' => ['color' => 'bg-blue-50 border-blue-100 text-blue-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                                            'near_miss' => ['color' => 'bg-amber-50 border-amber-100 text-amber-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                                            'deep_thinking' => ['color' => 'bg-teal-50 border-teal-100 text-teal-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />'],
                                            'overthinking' => ['color' => 'bg-orange-50 border-orange-100 text-orange-700', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                                            default => ['color' => 'bg-slate-50 border-slate-200 text-slate-600', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border {{ $insightConfig['color'] }}">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $insightConfig['icon'] !!}</svg>
                                        {{ $insightLabel }}
                                    </span>
                                @endif
                            </div>
                            <span class="flex items-center gap-2 shrink-0">
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

                        {{-- Question Text --}}
                        <div class="text-sm font-bold text-slate-800">{!! $question->question_text !!}</div>

                        {{-- Question Diagram/Image --}}
                        @if($question->image_path)
                            <div class="my-3 flex justify-start">
                                <img src="{{ $question->image_url }}" class="max-h-80 w-auto rounded-xl border border-slate-200 shadow-sm" alt="Question Diagram">
                            </div>
                        @endif

                        {{-- Option Listing --}}
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
                                    @if($isCorrect)
                                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    @elseif($isSelected)
                                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    @else
                                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="1.5" stroke-width="2" /></svg>
                                    @endif
                                    <span>{{ $opt->option_text }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Explanation --}}
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

{{-- Chart Scripts --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Attempt Distribution Doughnut
        const distCtx = document.getElementById('attemptDistributionChart').getContext('2d');
        new Chart(distCtx, {
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
                plugins: { legend: { display: false } },
                cutout: '75%'
            }
        });

        // Subject Accuracy Chart
        @if(!empty($subjectBreakdown) && count($subjectBreakdown) > 0)
        const subjCtx = document.getElementById('subjectAccuracyChart').getContext('2d');
        const subjData = @json($subjectBreakdown);
        const subjLabels = subjData.map(s => s.name || 'Unknown');
        const subjAccuracies = subjData.map(s => s.accuracy || 0);
        const subjColors = subjAccuracies.map(a => a >= 70 ? '#10b981' : (a >= 50 ? '#f59e0b' : '#ef4444'));

        new Chart(subjCtx, {
            type: 'bar',
            data: {
                labels: subjLabels,
                datasets: [{
                    label: 'Accuracy %',
                    data: subjAccuracies,
                    backgroundColor: subjColors,
                    borderRadius: 6,
                    barThickness: 28
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.raw.toFixed(1) + '% accuracy'
                        }
                    }
                },
                scales: {
                    x: {
                        max: 100,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { color: '#64748b', callback: v => v + '%' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#334155', font: { weight: '600', size: 12 } }
                    }
                }
            }
        });
        @endif

        // Time Distribution Donut
        @if(!empty($timeDistribution))
        const timeCtx = document.getElementById('timeDistributionChart').getContext('2d');
        const td = @json($timeDistribution);
        new Chart(timeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Quick Correct', 'Quick Wrong', 'Medium Correct', 'Medium Wrong', 'Slow Correct', 'Slow Wrong'],
                datasets: [{
                    data: [
                        td.quick_correct || 0,
                        td.quick_wrong || 0,
                        td.medium_correct || 0,
                        td.medium_wrong || 0,
                        td.slow_correct || 0,
                        td.slow_wrong || 0
                    ],
                    backgroundColor: ['#34d399', '#f87171', '#60a5fa', '#fb923c', '#2dd4bf', '#94a3b8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.label + ': ' + ctx.raw + ' questions'
                        }
                    }
                },
                cutout: '65%'
            }
        });
        @endif
    });
</script>
@endsection
