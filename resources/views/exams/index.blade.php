@extends('layouts.app')

@section('title', 'Online Test Series - THISAI')

@section('content')
<div class="space-y-8">

    <!-- Hero Header -->
    <div class="text-center py-6">
        <h1 class="text-3xl font-extrabold text-slate-800">IAS Mock Test Series</h1>
        <p class="text-slate-600 text-sm mt-2 max-w-lg mx-auto">Prepare for UPSC Prelims with timed test series, daily quizzes, and full length mock tests with negative marking.</p>
    </div>

    <!-- Category Tabs -->
    <div class="flex items-center justify-center border-b border-slate-200 pb-px">
        <div class="flex gap-6 text-sm font-bold">
            <a href="{{ route('exams.index') }}" class="pb-3 border-b-2 {{ !request('type') ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }} transition-colors">All Tests</a>
            <a href="{{ route('exams.index', ['type' => 'daily_quiz']) }}" class="pb-3 border-b-2 {{ request('type') === 'daily_quiz' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }} transition-colors">Daily Quiz</a>
            <a href="{{ route('exams.index', ['type' => 'section_test']) }}" class="pb-3 border-b-2 {{ request('type') === 'section_test' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }} transition-colors">Section Tests</a>
            <a href="{{ route('exams.index', ['type' => 'mock_test']) }}" class="pb-3 border-b-2 {{ request('type') === 'mock_test' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-800' }} transition-colors">Full Mock Tests</a>
        </div>
    </div>

    <!-- Exams Grid -->
    @if($exams->isEmpty())
        <div class="glass-card p-12 rounded-xl text-center">
            <p class="text-slate-400 text-sm">No test series available for this category yet.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($exams as $exam)
                @php $attempt = $exam->getUserAttempt(auth()->user()); @endphp
                <div class="glass-card p-6 rounded-xl flex flex-col justify-between gap-5 border border-slate-200/80 glass-card-hover">
                    <div class="space-y-3.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-bold text-blue-600 uppercase tracking-widest bg-blue-50 px-2.5 py-0.5 rounded border border-blue-100">{{ $exam->type->label() }}</span>
                            <span class="text-xs font-semibold text-slate-500">{{ $exam->duration_minutes }} Mins</span>
                        </div>
                        <h3 class="font-extrabold text-slate-800 text-base leading-tight">{{ $exam->title }}</h3>
                        <p class="text-slate-600 text-xs line-clamp-2 leading-relaxed">{{ $exam->description ?? 'No description provided.' }}</p>
                    </div>

                    <div class="border-t border-slate-200 pt-4 flex items-center justify-between mt-2">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider space-y-1">
                            <div>Marks: <span class="text-slate-800">{{ $exam->total_marks }}</span></div>
                            @if($exam->negative_marking > 0)
                                <div class="text-red-400">Negative: -{{ round($exam->negative_marking * 100) }}%</div>
                            @endif
                        </div>
                        
                        @if($attempt)
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-slate-600">Score: {{ $attempt->score }} pts</span>
                                <a href="{{ route('results.show', $attempt->session_token) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors">Results</a>
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
            @endforeach
        </div>

        <div class="pt-6">
            {{ $exams->links() }}
        </div>
    @endif

</div>
@endsection
