@extends('layouts.app')

@section('title', 'My Analytics - THISAI')

@section('content')
<div class="space-y-8">

    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-blue-700 to-purple-800 p-8 border border-blue-500/20 shadow-2xl">
        <div class="absolute w-64 h-64 bg-white/10 rounded-full blur-3xl -top-24 -left-24"></div>
        <div class="absolute w-64 h-64 bg-purple-500/10 rounded-full blur-3xl -bottom-24 -right-24"></div>
        <div class="relative z-10 space-y-2">
            <span class="text-xs uppercase font-bold tracking-widest text-white bg-white/20 px-3 py-1 rounded-full border border-white/30">Performance Hub</span>
            <h1 class="text-3xl font-extrabold text-white">Performance Analytics</h1>
            <p class="text-blue-100 text-sm max-w-xl">Track your progress, identify weak spots, and benchmark against your batch. Data-driven preparation for {{ auth()->user()->name }}.</p>
        </div>
    </div>

    {{-- Quick Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card p-5 rounded-2xl border border-slate-200 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total Exams</span>
                <span class="text-3xl font-black block text-slate-800 mt-0.5">{{ $stats['exams_attempted'] ?? 0 }}</span>
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-slate-200 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Avg Accuracy</span>
                <span class="text-3xl font-black block text-slate-800 mt-0.5">{{ $stats['avg_score'] ?? 0 }}%</span>
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-slate-200 flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Improvement</span>
                @php $impPct = $improvement['change_percent'] ?? 0; @endphp
                <span class="text-3xl font-black block mt-0.5 {{ $impPct >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $impPct >= 0 ? '+' : '' }}{{ round($impPct) }}%</span>
            </div>
        </div>

        <div class="glass-card p-5 rounded-2xl border border-slate-200 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Weak Subjects</span>
                <span class="text-3xl font-black block text-slate-800 mt-0.5">{{ is_array($weakAreas) ? count($weakAreas) : 0 }}</span>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Row 1: Score Trend (full width) --}}
    {{-- ============================================================ --}}
    <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-4">
        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
            Score Trend
        </h3>
        <div class="h-72">
            <canvas id="scoreTrendChart"></canvas>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Row 2: Subject Performance (2 cols) --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Left: Radar Chart --}}
        <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                Subject Radar
            </h3>
            <div class="h-72">
                <canvas id="subjectRadarChart"></canvas>
            </div>
        </div>

        {{-- Right: Subject Cards --}}
        <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                Subject Breakdown
            </h3>
            <div class="space-y-3">
                @if(!empty($subjectHeatmap))
                    @foreach($subjectHeatmap as $subj)
                        @php
                            $acc = $subj['accuracy'] ?? 0;
                            $barColor = $acc >= 70 ? 'bg-emerald-500' : ($acc >= 50 ? 'bg-amber-500' : 'bg-red-500');
                            $labelColor = $acc >= 70 ? 'text-emerald-600' : ($acc >= 50 ? 'text-amber-600' : 'text-red-600');
                        @endphp
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-700">{{ $subj['name'] ?? 'Unknown' }}</span>
                                <span class="font-bold {{ $labelColor }}">{{ round($acc) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="{{ $barColor }} h-full rounded-full transition-all duration-500" style="width: {{ min($acc, 100) }}%"></div>
                            </div>
                            <span class="text-[10px] text-slate-400">{{ $subj['attempts'] ?? 0 }} attempts &bull; {{ $subj['correct'] ?? 0 }}/{{ $subj['total'] ?? 0 }} correct</span>
                        </div>
                    @endforeach
                @else
                    <p class="text-xs text-slate-400 text-center py-8">No subject data available yet. Take some exams to see your breakdown.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Row 3: Time Management (2 cols) --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Left: Avg Time per Subject --}}
        <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Your Avg Time Per Subject
            </h3>
            <div class="h-64">
                <canvas id="timePerSubjectChart"></canvas>
            </div>
        </div>

        {{-- Right: Your Time vs Batch Avg --}}
        <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                You vs Batch (Time)
            </h3>
            <div class="h-64">
                <canvas id="timeBatchCompareChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Row 4: Batch Comparison (full width) --}}
    {{-- ============================================================ --}}
    <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-4">
        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            Batch Accuracy Comparison
        </h3>
        <div class="h-72">
            <canvas id="batchComparisonChart"></canvas>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Row 5: Improvement Tracker + Weak Areas --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Left: Improvement Tracker --}}
        <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-6">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                Improvement Tracker
            </h3>
            @php
                $prevAvg = $improvement['previous_avg'] ?? 0;
                $recentAvg = $improvement['recent_avg'] ?? 0;
                $changePct = $improvement['change_percent'] ?? 0;
                $improved = $changePct >= 0;
            @endphp
            <div class="flex items-center gap-8 justify-center py-4">
                {{-- Previous --}}
                <div class="text-center space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Previous Avg</span>
                    <span class="text-4xl font-black text-slate-400">{{ round($prevAvg) }}%</span>
                </div>

                {{-- Arrow --}}
                <div class="flex flex-col items-center gap-1">
                    @if($improved)
                        <svg class="w-10 h-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                    @else
                        <svg class="w-10 h-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    @endif
                    <span class="text-sm font-bold {{ $improved ? 'text-emerald-600' : 'text-red-600' }}">{{ $improved ? '+' : '' }}{{ round($changePct) }}%</span>
                </div>

                {{-- Recent --}}
                <div class="text-center space-y-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Recent Avg</span>
                    <span class="text-4xl font-black {{ $improved ? 'text-emerald-600' : 'text-red-600' }}">{{ round($recentAvg) }}%</span>
                </div>
            </div>
            <p class="text-center text-xs text-slate-400">Comparing your last 5 exams against the previous 5</p>
        </div>

        {{-- Right: Weak Areas --}}
        <div class="glass-card p-6 rounded-2xl border border-slate-200 space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                Weak Areas
            </h3>
            @if(!empty($weakAreas) && count($weakAreas) > 0)
                <div class="space-y-3">
                    @foreach($weakAreas as $weak)
                        @php $weakAcc = $weak['accuracy'] ?? 0; @endphp
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-700">{{ $weak['name'] ?? 'Unknown' }}</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 border border-red-100 text-red-700">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                    Practice Recommended
                                </span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="bg-red-500 h-full rounded-full" style="width: {{ min($weakAcc, 100) }}%"></div>
                            </div>
                            <span class="text-[10px] text-slate-400">{{ round($weakAcc) }}% accuracy &bull; {{ $weak['attempts'] ?? 0 }} questions attempted</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <svg class="w-12 h-12 text-emerald-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="text-sm font-semibold text-emerald-600">No weak areas detected</p>
                    <p class="text-xs text-slate-400 mt-1">All subjects are above 70% accuracy. Keep it up!</p>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Chart Scripts --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    const chartColors = {
        blue: '#3b82f6',
        emerald: '#10b981',
        amber: '#f59e0b',
        red: '#ef4444',
        purple: '#8b5cf6',
        slate: '#64748b'
    };

    // ── Score Trend Line Chart ──
    const scoreTrendData = @json($scoreTrend ?? []);
    const stLabels = scoreTrendData.map(d => d.label || d.date || '');
    const stScores = scoreTrendData.map(d => d.score || d.accuracy || 0);

    // Compute moving average (window 3)
    const movingAvg = stScores.map((val, idx, arr) => {
        if (idx < 2) return null;
        return ((arr[idx - 2] + arr[idx - 1] + val) / 3).toFixed(1);
    });

    new Chart(document.getElementById('scoreTrendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: stLabels.length ? stLabels : ['No data'],
            datasets: [
                {
                    label: 'Score %',
                    data: stScores.length ? stScores : [0],
                    borderColor: chartColors.blue,
                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: chartColors.blue,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                {
                    label: 'Moving Avg',
                    data: movingAvg,
                    borderColor: chartColors.slate,
                    borderWidth: 1.5,
                    borderDash: [6, 4],
                    tension: 0.35,
                    fill: false,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { size: 11, weight: '600' } } },
                tooltip: {
                    callbacks: {
                        title: ctx => {
                            const i = ctx[0].dataIndex;
                            const d = scoreTrendData[i];
                            return d ? (d.exam_name || d.label || 'Exam') : '';
                        },
                        afterTitle: ctx => {
                            const i = ctx[0].dataIndex;
                            const d = scoreTrendData[i];
                            return d && d.rank ? 'Rank: #' + d.rank : '';
                        },
                        label: ctx => ctx.dataset.label + ': ' + ctx.raw + '%'
                    }
                }
            },
            scales: {
                y: { min: 0, max: 100, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#64748b', callback: v => v + '%' } },
                x: { grid: { display: false }, ticks: { color: '#64748b', maxRotation: 45 } }
            }
        }
    });

    // ── Subject Radar Chart ──
    const radarData = @json($subjectHeatmap ?? []);
    const radarLabels = radarData.map(s => s.name || 'Unknown');
    const radarAccuracy = radarData.map(s => s.accuracy || 0);

    if (radarLabels.length > 0) {
        new Chart(document.getElementById('subjectRadarChart').getContext('2d'), {
            type: 'radar',
            data: {
                labels: radarLabels,
                datasets: [{
                    label: 'Your Accuracy %',
                    data: radarAccuracy,
                    backgroundColor: 'rgba(139, 92, 246, 0.15)',
                    borderColor: chartColors.purple,
                    borderWidth: 2,
                    pointBackgroundColor: chartColors.purple,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    r: {
                        min: 0, max: 100,
                        ticks: { stepSize: 25, color: '#94a3b8', backdropColor: 'transparent', font: { size: 10 } },
                        grid: { color: 'rgba(0,0,0,0.06)' },
                        pointLabels: { color: '#334155', font: { size: 11, weight: '600' } }
                    }
                }
            }
        });
    }

    // ── Time per Subject (Horizontal Bar) ──
    const tmData = @json($timeManagement ?? []);
    const tmLabels = tmData.map(t => t.name || 'Unknown');
    const tmYourTime = tmData.map(t => t.your_avg_time || 0);

    new Chart(document.getElementById('timePerSubjectChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: tmLabels.length ? tmLabels : ['No data'],
            datasets: [{
                label: 'Your Avg (sec)',
                data: tmYourTime.length ? tmYourTime : [0],
                backgroundColor: chartColors.amber,
                borderRadius: 6,
                barThickness: 24
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.raw + 's avg' } } },
            scales: {
                x: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#64748b', callback: v => v + 's' } },
                y: { grid: { display: false }, ticks: { color: '#334155', font: { weight: '600', size: 11 } } }
            }
        }
    });

    // ── Time Batch Compare (Paired Bars) ──
    const tmBatchTime = tmData.map(t => t.batch_avg_time || 0);

    new Chart(document.getElementById('timeBatchCompareChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: tmLabels.length ? tmLabels : ['No data'],
            datasets: [
                {
                    label: 'Your Time (sec)',
                    data: tmYourTime.length ? tmYourTime : [0],
                    backgroundColor: chartColors.blue,
                    borderRadius: 4,
                    barThickness: 18
                },
                {
                    label: 'Batch Avg (sec)',
                    data: tmBatchTime.length ? tmBatchTime : [0],
                    backgroundColor: chartColors.slate,
                    borderRadius: 4,
                    barThickness: 18
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { size: 11, weight: '600' } } },
                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.raw + 's' } }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b' } },
                y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#64748b', callback: v => v + 's' } }
            }
        }
    });

    // ── Batch Comparison (Grouped Bar) ──
    const bcData = @json($batchComparison ?? []);
    const bcLabels = bcData.map(b => b.name || 'Unknown');
    const bcYours = bcData.map(b => b.your_accuracy || 0);
    const bcBatch = bcData.map(b => b.batch_accuracy || 0);

    new Chart(document.getElementById('batchComparisonChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: bcLabels.length ? bcLabels : ['No data'],
            datasets: [
                {
                    label: 'Your Accuracy',
                    data: bcYours.length ? bcYours : [0],
                    backgroundColor: chartColors.blue,
                    borderRadius: 6,
                    barThickness: 28
                },
                {
                    label: 'Batch Average',
                    data: bcBatch.length ? bcBatch : [0],
                    backgroundColor: '#cbd5e1',
                    borderRadius: 6,
                    barThickness: 28
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { size: 11, weight: '600' } } },
                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': ' + ctx.raw + '%' } }
            },
            scales: {
                y: { max: 100, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#64748b', callback: v => v + '%' } },
                x: { grid: { display: false }, ticks: { color: '#334155', font: { weight: '600' } } }
            }
        }
    });
});
</script>
@endsection
