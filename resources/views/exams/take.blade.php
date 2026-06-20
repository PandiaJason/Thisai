@extends('layouts.exam')

@section('title', 'Exam: ' . $exam->title)

@section('exam-title', $exam->title)

@section('timer-section')
@php
    $elapsedSeconds = now()->diffInSeconds($attempt->started_at);
    $durationSeconds = $exam->duration_minutes * 60;
    $remainingSeconds = max(0, $durationSeconds - $elapsedSeconds);
@endphp
<div x-data="examTimer('{{ $attempt->session_token }}', {{ $remainingSeconds }})" x-init="startTimer()" class="flex items-center gap-2 bg-blue-50 border border-blue-100 px-4 py-1.5 rounded-lg text-sm font-extrabold text-blue-600">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <span x-text="timeString">--:--:--</span>
</div>
@endsection

@section('content')
<!-- Fullscreen Secure Exam Modal Overlay -->
<div id="fullscreen-start-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/95 backdrop-blur-md">
    <div class="max-w-md w-full p-8 rounded-2xl border border-slate-800 bg-slate-900 text-slate-100 text-center shadow-2xl space-y-6">
        <div class="w-16 h-16 bg-blue-600/10 border border-blue-500/35 rounded-full flex items-center justify-center mx-auto text-blue-400">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
        </div>
        <div class="space-y-2">
            <h3 class="text-xl font-bold text-white">Secure Exam Mode</h3>
            <p class="text-xs text-slate-200 leading-relaxed">
                This exam is conducted in a secure environment. You are required to enter Fullscreen Mode. 
                Exiting fullscreen, switching tabs, or resizing the window will be logged as violations.
            </p>
        </div>
        <button id="start-fullscreen-btn" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-extrabold py-3 px-4 rounded-xl text-sm transition-all shadow-lg shadow-blue-500/25 active:scale-[0.98]">
            Enter Fullscreen & Start Exam
        </button>
    </div>
</div>

<div x-data="examEngine()" class="flex-1 flex flex-col md:flex-row overflow-hidden w-full">
    
    <!-- Left: Question Display (3/4 width) -->
    <div class="flex-1 flex flex-col justify-between overflow-y-auto p-6 space-y-6">
        
        <!-- Question Block -->
        <div class="space-y-6">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Question <span x-text="currentQuestionIndex + 1">1</span> of {{ $questions->count() }}</span>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded border border-slate-200">Marks: +<span x-text="currentQuestion().marks">1</span> | Negative: -<span x-text="currentQuestion().negative_marks">0</span></span>
            </div>

            <!-- Question Text -->
            <div class="text-base sm:text-lg font-extrabold text-slate-800 leading-relaxed" x-html="currentQuestion().question_text"></div>

            <!-- Option Selections -->
            <div class="space-y-3 pt-4">
                <template x-for="(option, idx) in currentQuestion().options" :key="option.id">
                    <label :class="{
                        'border-blue-500 bg-blue-50/50': isSelected(option.id),
                        'border-slate-200 bg-white hover:bg-slate-50': !isSelected(option.id)
                    }" class="flex items-center gap-3 p-4 rounded-xl border cursor-pointer select-none transition-all">
                        <input :type="currentQuestion().type === 'single_correct' ? 'radio' : 'checkbox'" 
                               :name="'question_' + currentQuestion().id" 
                               :value="option.id" 
                               :checked="isSelected(option.id)"
                               @change="toggleOption(option.id)"
                               class="w-4 h-4 bg-white border-slate-300 text-blue-600 focus:ring-0 focus:ring-offset-0">
                        <span class="text-sm font-semibold text-slate-800" x-html="option.option_text"></span>
                    </label>
                </template>
            </div>
        </div>

        <!-- Controls footer -->
        <div class="flex flex-wrap items-center justify-between border-t border-slate-200 pt-6 gap-3">
            <div class="flex items-center gap-2">
                <button @click="prevQuestion()" :disabled="currentQuestionIndex === 0" class="bg-slate-100 hover:bg-slate-200 disabled:opacity-40 disabled:hover:bg-slate-100 text-slate-700 font-bold py-2 px-4 rounded-lg text-xs transition-colors">
                    Previous
                </button>
                <button @click="nextQuestion()" :disabled="currentQuestionIndex === totalQuestions - 1" class="bg-slate-100 hover:bg-slate-200 disabled:opacity-40 disabled:hover:bg-slate-100 text-slate-700 font-bold py-2 px-4 rounded-lg text-xs transition-colors">
                    Next
                </button>
            </div>
            
            <div class="flex items-center gap-2">
                <button @click="clearResponse()" class="text-slate-400 hover:text-slate-800 font-bold py-2 px-3 text-xs transition-colors">
                    Clear Response
                </button>
                <button @click="saveAndNext()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg text-xs transition-colors shadow-lg hover:shadow-blue-500/10">
                    Save & Next
                </button>
            </div>
        </div>
    </div>

    <!-- Right: Question Palette sidebar (1/4 width) -->
    <div class="w-full md:w-80 bg-white border-l border-slate-200 flex flex-col justify-between p-6 space-y-6">
        
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Question Palette</h3>
            
            <!-- Palette Grid -->
            <div class="grid grid-cols-5 gap-2 max-h-60 overflow-y-auto pr-1">
                <template x-for="(q, idx) in questions" :key="q.id">
                    <button @click="jumpToQuestion(idx)" 
                            :class="getPaletteClass(idx)"
                            class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-xs transition-colors"
                            x-text="idx + 1">
                    </button>
                </template>
            </div>
        </div>

        <!-- Legend with Dynamic Counts -->
        <div class="grid grid-cols-2 gap-3 text-[10px] font-bold text-slate-500 border-t border-slate-200 pt-4">
            <div class="flex items-center justify-between bg-slate-50 border border-slate-100 p-2 rounded-lg">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-emerald-600 shrink-0"></span> Answered</div>
                <span class="text-emerald-600 font-extrabold" x-text="getAnsweredCount()">0</span>
            </div>
            <div class="flex items-center justify-between bg-slate-50 border border-slate-100 p-2 rounded-lg">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-red-600 shrink-0"></span> Skipped</div>
                <span class="text-red-600 font-extrabold" x-text="getSkippedCount()">0</span>
            </div>
            <div class="flex items-center justify-between bg-slate-50 border border-slate-100 p-2 rounded-lg">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-blue-600 shrink-0"></span> Current</div>
                <span class="text-blue-600 font-extrabold">1</span>
            </div>
            <div class="flex items-center justify-between bg-slate-50 border border-slate-100 p-2 rounded-lg">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-slate-200 shrink-0"></span> Unvisited</div>
                <span class="text-slate-500 font-extrabold" x-text="getUnvisitedCount()">0</span>
            </div>
        </div>

        <!-- Final Submit Block -->
        <div class="pt-6 border-t border-slate-200">
            <form id="submit-exam-form" action="{{ route('exams.submit', $attempt->session_token) }}" method="POST">
                @csrf
                <button type="button" @click="confirmSubmit()" class="w-full bg-red-600 hover:bg-red-500 text-white font-extrabold py-2.5 px-4 rounded-lg text-sm transition-colors shadow-lg shadow-red-600/10">
                    Submit Exam
                </button>
            </form>
        </div>
    </div>

</div>

<!-- Exam engine scripts -->
<script>
    // Timer component logic
    function examTimer(token, duration) {
        return {
            remainingSeconds: Math.floor(duration),
            timeString: '00:00:00',
            intervalId: null,
            
            startTimer() {
                // Initial check
                this.updateTimeString();
                
                this.intervalId = setInterval(() => {
                    this.remainingSeconds--;
                    this.updateTimeString();
                    
                    if (this.remainingSeconds <= 0) {
                        clearInterval(this.intervalId);
                        // Auto submit exam
                        if (window.examEngineInstance) {
                            window.examEngineInstance.saveAnswerState().finally(() => {
                                document.getElementById('submit-exam-form').submit();
                            });
                        } else {
                            document.getElementById('submit-exam-form').submit();
                        }
                    }
                }, 1000);
            },

            updateTimeString() {
                const totalSecs = Math.max(0, Math.floor(this.remainingSeconds));
                const hrs = Math.floor(totalSecs / 3600);
                const mins = Math.floor((totalSecs % 3600) / 60);
                const secs = totalSecs % 60;
                
                this.timeString = [
                    hrs.toString().padStart(2, '0'),
                    mins.toString().padStart(2, '0'),
                    secs.toString().padStart(2, '0')
                ].join(':');
            }
        };
    }

    // Exam engine engine state
    function examEngine() {
        return {
            currentQuestionIndex: 0,
            questions: {!! json_encode($questions->map(fn($q) => [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'type' => $q->type->value,
                'marks' => $q->marks,
                'negative_marks' => $q->negative_marks,
                'options' => $q->options->map(fn($o) => ['id' => $o->id, 'option_text' => $o->option_text])
            ])) !!},
            answers: {!! json_encode($answers->map(fn($a) => [
                'question_id' => $a->question_id,
                'selected_option_ids' => array_map('intval', $a->selected_option_ids ?? [])
            ])->values()) !!}.reduce((acc, curr) => {
                acc[curr.question_id] = (curr.selected_option_ids || []).map(id => parseInt(id, 10));
                return acc;
            }, {}),
            
            visitedQuestions: new Set([{{ $questions->first() ? $questions->first()->id : '' }}]),
            timeSpent: {}, // question_id -> seconds
            timerInterval: null,

            init() {
                window.examEngineInstance = this;
                this.totalQuestions = this.questions.length;
                this.startQuestionTimer();
            },

            currentQuestion() {
                return this.questions[this.currentQuestionIndex];
            },

            getAnsweredCount() {
                return this.questions.filter(q => this.answers[q.id] && this.answers[q.id].length > 0).length;
            },

            getSkippedCount() {
                return this.questions.filter((q, idx) => {
                    const isAnswered = this.answers[q.id] && this.answers[q.id].length > 0;
                    const isVisited = this.visitedQuestions.has(q.id);
                    const isCurrent = this.currentQuestionIndex === idx;
                    return isVisited && !isAnswered && !isCurrent;
                }).length;
            },

            getUnvisitedCount() {
                return this.questions.filter(q => !this.visitedQuestions.has(q.id)).length;
            },

            startQuestionTimer() {
                if (this.timerInterval) clearInterval(this.timerInterval);
                const q = this.currentQuestion();
                if (!q) return;
                const qId = q.id;
                if (!this.timeSpent[qId]) this.timeSpent[qId] = 0;

                this.timerInterval = setInterval(() => {
                    this.timeSpent[qId]++;
                }, 1000);
            },

            isSelected(optionId) {
                const qId = this.currentQuestion().id;
                return this.answers[qId] && this.answers[qId].includes(optionId);
            },

            toggleOption(optionId) {
                const q = this.currentQuestion();
                if (!this.answers[q.id]) this.answers[q.id] = [];

                if (q.type === 'single_correct') {
                    this.answers[q.id] = [optionId];
                } else {
                    const idx = this.answers[q.id].indexOf(optionId);
                    if (idx > -1) {
                        this.answers[q.id].splice(idx, 1);
                    } else {
                        this.answers[q.id].push(optionId);
                    }
                }
            },

            clearResponse() {
                const qId = this.currentQuestion().id;
                this.answers[qId] = [];
                this.saveAnswerState();
            },

            saveAnswerState() {
                const q = this.currentQuestion();
                if (!q) return Promise.resolve();
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                return fetch('/api/exam/save-answer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        session_token: '{{ $attempt->session_token }}',
                        question_id: q.id,
                        selected_option_ids: this.answers[q.id] || [],
                        time_spent_seconds: this.timeSpent[q.id] || 0
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success === false) {
                        alert(data.message || 'Error saving answer');
                    }
                })
                .catch(err => console.error(err));
            },

            prevQuestion() {
                if (this.currentQuestionIndex > 0) {
                    this.saveAnswerState();
                    this.currentQuestionIndex--;
                    this.visitedQuestions.add(this.currentQuestion().id);
                    this.startQuestionTimer();
                }
            },

            nextQuestion() {
                if (this.currentQuestionIndex < this.totalQuestions - 1) {
                    this.saveAnswerState();
                    this.currentQuestionIndex++;
                    this.visitedQuestions.add(this.currentQuestion().id);
                    this.startQuestionTimer();
                }
            },

            saveAndNext() {
                this.saveAnswerState();
                if (this.currentQuestionIndex < this.totalQuestions - 1) {
                    this.currentQuestionIndex++;
                    this.visitedQuestions.add(this.currentQuestion().id);
                    this.startQuestionTimer();
                } else {
                    this.currentQuestionIndex = 0;
                    this.visitedQuestions.add(this.currentQuestion().id);
                    this.startQuestionTimer();
                    showToast('Last question saved. Returning to Question 1.');
                }
            },

            jumpToQuestion(index) {
                this.saveAnswerState();
                this.currentQuestionIndex = index;
                this.visitedQuestions.add(this.currentQuestion().id);
                this.startQuestionTimer();
            },

            getPaletteClass(index) {
                const q = this.questions[index];
                const isCurrent = this.currentQuestionIndex === index;
                const isAnswered = this.answers[q.id] && this.answers[q.id].length > 0;
                const isVisited = this.visitedQuestions.has(q.id);

                if (isCurrent) return 'bg-blue-600 text-white shadow-lg shadow-blue-500/25';
                if (isAnswered) return 'bg-emerald-600 text-white';
                if (isVisited) return 'bg-red-600 text-white';
                return 'bg-slate-100 border border-slate-200 text-slate-600 hover:bg-slate-200';
            },

            confirmSubmit() {
                if (confirm('Are you sure you want to submit the exam? You will not be able to change your answers.')) {
                    const btn = document.querySelector('#submit-exam-form button');
                    if (btn) {
                        btn.disabled = true;
                        btn.textContent = 'Submitting...';
                    }
                    this.saveAnswerState().then(() => {
                        document.getElementById('submit-exam-form').submit();
                    });
                }
            }
        };
    }

    // Fullscreen enforcement
    (function() {
        const startModal = document.getElementById('fullscreen-start-modal');
        const startBtn = document.getElementById('start-fullscreen-btn');
        let violationsCount = 0;

        function requestFullscreen() {
            const doc = document.documentElement;
            if (doc.requestFullscreen) {
                doc.requestFullscreen();
            } else if (doc.mozRequestFullScreen) { // Firefox
                doc.mozRequestFullScreen();
            } else if (doc.webkitRequestFullscreen) { // Chrome, Safari and Opera
                doc.webkitRequestFullscreen();
            } else if (doc.msRequestFullscreen) { // IE/Edge
                doc.msRequestFullscreen();
            }
        }

        startBtn.addEventListener('click', function() {
            requestFullscreen();
        });

        function onFullscreenChange() {
            const isFullscreen = document.fullscreenElement || 
                                 document.webkitFullscreenElement || 
                                 document.mozFullScreenElement || 
                                 document.msFullscreenElement;

            if (isFullscreen) {
                startModal.classList.add('hidden');
            } else {
                violationsCount++;
                startModal.classList.remove('hidden');
                
                const titleEl = startModal.querySelector('h3');
                const descEl = startModal.querySelector('p');
                const btnEl = startModal.querySelector('button');
                
                titleEl.textContent = 'Fullscreen Violation!';
                titleEl.className = 'text-xl font-bold text-red-500';
                
                descEl.innerHTML = `You have exited fullscreen mode. This is violation <strong>${violationsCount}</strong>. Please re-enter fullscreen immediately to resume the exam.`;
                btnEl.textContent = 'Re-enter Fullscreen';
            }
        }

        document.addEventListener('fullscreenchange', onFullscreenChange);
        document.addEventListener('webkitfullscreenchange', onFullscreenChange);
        document.addEventListener('mozfullscreenchange', onFullscreenChange);
        document.addEventListener('MSFullscreenChange', onFullscreenChange);
    })();
</script>
@endsection
