<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Exam Selector --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <div class="flex items-center gap-4">
                <label class="text-sm font-bold text-gray-700 dark:text-gray-300">Select Exam</label>
                <select wire:model.live="selectedExamId" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm flex-1 max-w-md">
                    @foreach($examOptions as $id => $title)
                        <option value="{{ $id }}">{{ $title }}</option>
                    @endforeach
                </select>
                @if($selectedExamId)
                    <button wire:click="exportResultsCsv" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition-colors">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4" style="width: 16px; height: 16px; flex-shrink: 0;"/>
                        Export CSV
                    </button>
                @endif
            </div>
        </div>

        @if(empty($examOptions))
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-12 text-center">
                <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto text-gray-400 mb-3" style="width: 48px; height: 48px; flex-shrink: 0;"/>
                <p class="text-sm font-medium text-gray-500">No published exams found</p>
            </div>
        @else
            {{-- Summary Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @php 
                    $cards = [
                        ['Total Attempts', $examSummary['total_attempts'] ?? 0, 'heroicon-o-users', 'blue'],
                        ['Avg Score', ($examSummary['avg_score'] ?? 0) . '/' . ($examSummary['total_marks'] ?? 0), 'heroicon-o-chart-bar', 'emerald'],
                        ['Avg Accuracy', ($examSummary['avg_accuracy'] ?? 0) . '%', 'heroicon-o-check-circle', 'purple'],
                        ['Highest Score', $examSummary['highest_score'] ?? 0, 'heroicon-o-arrow-trending-up', 'green'],
                        ['Lowest Score', $examSummary['lowest_score'] ?? 0, 'heroicon-o-arrow-trending-down', 'red'],
                        ['Total Marks', $examSummary['total_marks'] ?? 0, 'heroicon-o-academic-cap', 'amber'],
                    ];
                    $colorClasses = [
                        'blue' => 'text-blue-500 dark:text-blue-400',
                        'emerald' => 'text-emerald-500 dark:text-emerald-400',
                        'purple' => 'text-purple-500 dark:text-purple-400',
                        'green' => 'text-green-500 dark:text-green-400',
                        'red' => 'text-red-500 dark:text-red-400',
                        'amber' => 'text-amber-500 dark:text-amber-400',
                    ];
                @endphp
                @foreach($cards as [$label, $value, $icon, $color])
                    @php $colorClass = $colorClasses[$color] ?? 'text-gray-500'; @endphp
                    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                                <x-dynamic-component :component="$icon" class="w-5 h-5 {{ $colorClass }}" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" />
                            </div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ $label }}</span>
                        </div>
                        <p class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Score Distribution --}}
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <h3 class="text-sm font-extrabold text-gray-800 dark:text-gray-200 mb-4">Score Distribution</h3>
                    <canvas id="scoreDistributionChart" height="200"></canvas>
                </div>

                {{-- Subject Performance --}}
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <h3 class="text-sm font-extrabold text-gray-800 dark:text-gray-200 mb-4">Subject Performance</h3>
                    <canvas id="subjectPerformanceChart" height="200"></canvas>
                </div>
            </div>

            {{-- Question Difficulty Table --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                <h3 class="text-sm font-extrabold text-gray-800 dark:text-gray-200 mb-4">Question Difficulty Analysis</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 px-3 text-xs font-bold text-gray-500 uppercase">#</th>
                                <th class="text-left py-2 px-3 text-xs font-bold text-gray-500 uppercase">Question</th>
                                <th class="text-left py-2 px-3 text-xs font-bold text-gray-500 uppercase">Subject</th>
                                <th class="text-center py-2 px-3 text-xs font-bold text-gray-500 uppercase">Correct Rate</th>
                                <th class="text-center py-2 px-3 text-xs font-bold text-gray-500 uppercase">Avg Time</th>
                                <th class="text-center py-2 px-3 text-xs font-bold text-gray-500 uppercase">Difficulty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($questionDifficulty as $q)
                                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="py-2 px-3 font-bold text-gray-700 dark:text-gray-300">{{ $q['number'] }}</td>
                                    <td class="py-2 px-3 text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $q['text'] }}</td>
                                    <td class="py-2 px-3">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">{{ $q['subject'] }}</span>
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full {{ $q['correct_rate'] >= 70 ? 'bg-emerald-500' : ($q['correct_rate'] >= 40 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $q['correct_rate'] }}%"></div>
                                            </div>
                                            <span class="text-xs font-bold {{ $q['correct_rate'] >= 70 ? 'text-emerald-600' : ($q['correct_rate'] >= 40 ? 'text-amber-600' : 'text-red-600') }}">{{ $q['correct_rate'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3 text-center text-xs font-semibold text-gray-500">{{ $q['avg_time'] }}s</td>
                                    <td class="py-2 px-3 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $q['difficulty_label'] === 'Easy' ? 'bg-emerald-50 text-emerald-700' : ($q['difficulty_label'] === 'Medium' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">
                                            {{ $q['difficulty_label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Most Missed Questions --}}
            @if(!empty($mostMissedQuestions))
                <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
                    <h3 class="text-sm font-extrabold text-gray-800 dark:text-gray-200 mb-4">Top 10 Most Missed Questions</h3>
                    <div class="space-y-2">
                        @foreach($mostMissedQuestions as $q)
                            <div class="flex items-center gap-3 p-3 rounded-lg bg-red-50/50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30">
                                <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-xs font-extrabold text-red-700 dark:text-red-400">Q{{ $q['number'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $q['text'] }}</p>
                                    <span class="text-xs font-semibold text-purple-600">{{ $q['subject'] }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-extrabold text-red-600">{{ $q['wrong_rate'] }}%</span>
                                    <p class="text-xs text-gray-400">got it wrong</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Score Distribution Chart
            const distCtx = document.getElementById('scoreDistributionChart');
            if (distCtx) {
                const distData = @json($scoreDistribution);
                new Chart(distCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(distData),
                        datasets: [{
                            label: 'Students',
                            data: Object.values(distData),
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Subject Performance Chart
            const subCtx = document.getElementById('subjectPerformanceChart');
            if (subCtx) {
                const subData = @json($subjectPerformance);
                new Chart(subCtx, {
                    type: 'bar',
                    data: {
                        labels: subData.map(s => s.name),
                        datasets: [
                            { label: 'Correct', data: subData.map(s => s.correct), backgroundColor: 'rgba(16, 185, 129, 0.7)', borderRadius: 4 },
                            { label: 'Wrong', data: subData.map(s => s.wrong), backgroundColor: 'rgba(239, 68, 68, 0.7)', borderRadius: 4 },
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, beginAtZero: true } }
                    }
                });
            }
        });
    </script>
    @endpush
</x-filament-panels::page>
