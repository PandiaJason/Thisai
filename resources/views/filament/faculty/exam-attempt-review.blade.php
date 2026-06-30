<div class="space-y-6 max-h-[80vh] overflow-y-auto px-4 py-2">
    <!-- Header summary cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
            <span class="text-[10px] uppercase font-bold text-blue-500 tracking-wider">Marks / Score</span>
            <span class="text-xl font-black text-blue-800 block mt-1">{{ $record->score }} / {{ $record->total_marks }}</span>
        </div>
        <div class="bg-purple-50 border border-purple-100 rounded-xl p-4 text-center">
            <span class="text-[10px] uppercase font-bold text-purple-500 tracking-wider">Accuracy</span>
            <span class="text-xl font-black text-purple-800 block mt-1">{{ $record->accuracy }}%</span>
        </div>
        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-center">
            <span class="text-[10px] uppercase font-bold text-emerald-500 tracking-wider">Correct / Wrong</span>
            <span class="text-xl font-black text-emerald-800 block mt-1">{{ $record->correct_count }} / {{ $record->wrong_count }}</span>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
            <span class="text-[10px] uppercase font-bold text-amber-500 tracking-wider">Unanswered</span>
            <span class="text-xl font-black text-amber-800 block mt-1">{{ $record->unanswered_count }}</span>
        </div>
    </div>

    <!-- Questions Loop -->
    <div class="space-y-4">
        @php
            $exam = $record->exam;
            $answers = $record->answers()->get()->keyBy('question_id');
            // Resolve time spent category helper
            $timeAnalyticsService = app(\App\Services\TimeAnalyticsService::class);
        @endphp

        @foreach($exam->questions()->with('options', 'subject', 'topic')->get() as $index => $question)
            @php
                $answer = $answers->get($question->id);
                $timeSpent = $answer ? $answer->time_spent_seconds : 0;
                
                // Categorize time
                $timeCategory = 'medium';
                if ($timeSpent < 30) {
                    $timeCategory = 'quick';
                } elseif ($timeSpent > 90) {
                    $timeCategory = 'slow';
                }

                // Identify insight
                $insightType = null;
                $insightLabel = null;
                if ($answer) {
                    if ($answer->is_correct === true) {
                        if ($timeSpent < 30) {
                            $insightType = 'quick_solve';
                            $insightLabel = 'Quick Solve ⚡';
                        } elseif ($timeSpent > 90) {
                            $insightType = 'deep_thinking';
                            $insightLabel = 'Deep Thinking 🐢';
                        } else {
                            $insightType = 'well_solved';
                            $insightLabel = 'Well Solved ✓';
                        }
                    } elseif ($answer->is_correct === false) {
                        if ($timeSpent < 30) {
                            $insightType = 'careless_error';
                            $insightLabel = 'Careless Error ⚠️';
                        } elseif ($timeSpent > 90) {
                            $insightType = 'overthinking';
                            $insightLabel = 'Overthinking 🐢';
                        } else {
                            $insightType = 'near_miss';
                            $insightLabel = 'Near Miss ⏱️';
                        }
                    }
                }

                $timeBadgeColors = match($timeCategory) {
                    'quick' => 'bg-emerald-50 border-emerald-100 text-emerald-700',
                    'medium' => 'bg-blue-50 border-blue-100 text-blue-700',
                    'slow' => 'bg-orange-50 border-orange-100 text-orange-700',
                    default => 'bg-slate-50 border-slate-200 text-slate-600',
                };

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

            <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-4">
                <!-- Meta header -->
                <div class="flex flex-wrap items-center justify-between text-xs font-semibold text-slate-500 border-b border-slate-100 pb-2 gap-2">
                    <div class="flex items-center flex-wrap gap-2">
                        <span class="text-slate-800 font-bold">Question {{ $index + 1 }}</span>
                        @if($question->subject)
                            <span class="px-2.5 py-0.5 rounded-full bg-purple-50 border border-purple-100 text-purple-700 text-[10px] font-bold">
                                {{ $question->subject->name }}
                            </span>
                        @endif
                        @if($question->topic)
                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-[10px] font-bold">
                                {{ $question->topic->name }}
                            </span>
                        @endif
                        <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-bold {{ $timeBadgeColors }}">
                            {{ $timeSpent }}s &bull; {{ ucfirst($timeCategory) }}
                        </span>
                        @if($insightType)
                            <span class="px-2.5 py-0.5 rounded-full border text-[10px] font-bold {{ $insightConfig['color'] }} inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $insightConfig['icon'] !!}</svg>
                                {{ $insightLabel }}
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if($answer && $answer->is_correct === true)
                            <span class="text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 text-[10px] uppercase font-black">Correct</span>
                        @elseif($answer && $answer->is_correct === false)
                            <span class="text-red-700 bg-red-50 px-2 py-0.5 rounded border border-red-100 text-[10px] uppercase font-black">Wrong</span>
                        @else
                            <span class="text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 text-[10px] uppercase font-black">Unanswered</span>
                        @endif
                        <span class="font-bold text-slate-700">Marks: {{ $answer ? $answer->marks_obtained : 0 }}</span>
                    </div>
                </div>

                <!-- Text -->
                <div class="text-sm font-semibold text-slate-800 leading-relaxed">
                    {!! $question->question_text !!}
                </div>

                <!-- Question Diagram/Image -->
                @if($question->image_path)
                    <div class="my-3 flex justify-start">
                        <img src="{{ $question->image_url }}" class="max-h-80 w-auto rounded-xl border border-slate-200 shadow-sm" alt="Question Diagram">
                    </div>
                @endif

                <!-- Options -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @foreach($question->options as $opt)
                        @php
                            $isSelected = $answer && is_array($answer->selected_option_ids) && in_array($opt->id, $answer->selected_option_ids);
                            $isCorrect = $opt->is_correct;
                        @endphp
                        <div class="p-3 rounded-lg border text-xs leading-relaxed flex items-start gap-3 {{
                            $isCorrect ? 'bg-emerald-50 border-emerald-200 text-emerald-800 font-medium shadow-sm' :
                            ($isSelected ? 'bg-red-50 border-red-200 text-red-800 font-medium' : 'bg-slate-50 border-slate-200 text-slate-600')
                        }}">
                            <span class="mt-0.5 shrink-0">
                                @if($isCorrect)
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                @elseif($isSelected)
                                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                @else
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="1.5" stroke-width="2" /></svg>
                                @endif
                            </span>
                            <span>{{ $opt->option_text }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Explanation -->
                @if($question->explanation)
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs text-slate-600 space-y-1.5 mt-2">
                        <span class="font-extrabold text-slate-800 block">Explanation:</span>
                        <p class="leading-relaxed">{!! $question->explanation !!}</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
