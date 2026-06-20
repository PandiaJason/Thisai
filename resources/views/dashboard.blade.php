@extends('layouts.app')

@section('title', 'Student Dashboard - THISAI')

@section('content')
<div class="space-y-8">

    <!-- Welcome Banner with Streak & Live Alert -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 p-8 border border-blue-500/20 shadow-2xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="absolute w-64 h-64 bg-white/10 rounded-full blur-3xl -top-24 -left-24"></div>
        <div class="absolute w-64 h-64 bg-purple-500/10 rounded-full blur-3xl -bottom-24 -right-24"></div>

        <div class="relative z-10 space-y-2">
            <span class="text-xs uppercase font-bold tracking-widest text-white bg-white/20 px-3 py-1 rounded-full border border-white/30">Candidate Portal</span>
            <h1 class="text-3xl font-extrabold text-white">Hello, {{ auth()->user()->name }}</h1>
            <p class="text-blue-100 text-sm max-w-xl">"Success is not final, failure is not fatal: it is the courage to continue that counts." Access your daily IAS syllabus preparation modules below.</p>
        </div>

        @if($liveTelecast)
            <div class="relative z-10 flex items-center gap-4 bg-white/10 border border-white/20 p-4 rounded-xl max-w-sm">
                <span class="w-3.5 h-3.5 bg-red-500 rounded-full pulse-red-dot shrink-0"></span>
                <div class="text-xs space-y-1">
                    <span class="font-bold text-white block uppercase tracking-wider">Morning Telecast is Live!</span>
                    <p class="text-blue-100">Join the discussion now. This video will expire tonight at {{ $liveTelecast->auto_delete_at->format('h:i A') }}.</p>
                    <a href="#live-session" class="inline-block mt-1 font-bold text-white hover:text-blue-200 transition-colors">Tune in now &rarr;</a>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card-blue glass-card p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-blue-100 tracking-wider">Enrolled Courses</span>
                <span class="text-3xl font-black block text-white mt-0.5">{{ $analytics['enrolled_courses'] }}</span>
            </div>
        </div>

        <div class="stat-card-purple glass-card p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-purple-100 tracking-wider">Videos Watched</span>
                <span class="text-3xl font-black block text-white mt-0.5">{{ $analytics['videos_watched'] }}</span>
            </div>
        </div>

        <div class="stat-card-green glass-card p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-emerald-100 tracking-wider">Tests Taken</span>
                <span class="text-3xl font-black block text-white mt-0.5">{{ $analytics['exams_attempted'] }}</span>
            </div>
        </div>

        <div class="stat-card-amber glass-card p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-amber-100 tracking-wider">Average Score</span>
                <span class="text-3xl font-black block text-white mt-0.5">{{ $analytics['avg_score'] }}%</span>
            </div>
        </div>
    </div>


    <!-- Live Telecast Section -->
    @if($liveTelecast)
        <div id="live-session" class="glass-card p-6 rounded-2xl border border-red-500/20 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-0.5 rounded bg-red-600 text-[10px] font-bold text-white uppercase tracking-wider pulse-red-dot flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-white rounded-full"></span> Live
                    </span>
                    <h2 class="text-lg font-bold text-slate-800">{{ $liveTelecast->title }}</h2>
                </div>
                <span class="text-xs font-semibold text-slate-500">Expiring tonight at {{ $liveTelecast->auto_delete_at->format('h:i A') }}</span>
            </div>
            <div class="aspect-video w-full rounded-xl overflow-hidden bg-black border border-slate-100">
                <iframe src="{{ $liveTelecast->stream_url }}" class="w-full h-full" allowfullscreen allow="autoplay; encrypted-media"></iframe>
            </div>
            @if($liveTelecast->description)
                <p class="text-slate-600 text-sm leading-relaxed">{{ $liveTelecast->description }}</p>
            @endif
        </div>
    @endif

    <!-- Content Split Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Continue Learning & Current Affairs (2/3 width) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Continue Learning -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1 h-5 bg-blue-500 rounded-full"></span> Continue Learning
                </h3>
                @if($recentVideos->isEmpty())
                    <div class="glass-card p-8 rounded-xl text-center space-y-3">
                        <p class="text-slate-400 text-sm">No videos in progress. Start exploring courses.</p>
                        <a href="{{ route('courses.index') }}" class="inline-flex bg-blue-600 hover:bg-blue-500 px-4 py-1.5 text-xs font-bold text-white rounded-full transition-colors">Browse Catalog</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($recentVideos as $video)
                            @php $progress = $video->getUserProgress(auth()->user()); @endphp
                            <div class="glass-card p-4 rounded-xl flex flex-col justify-between gap-4 border-l-4 border-blue-500">
                                <div>
                                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">{{ $video->course->title }}</span>
                                    <h4 class="font-bold text-slate-800 mt-1 text-sm line-clamp-1">{{ $video->title }}</h4>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs font-semibold text-slate-400">
                                        <span>Progress</span>
                                        <span>{{ $progress ? $progress->progress_percent : 0 }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-500" style="width: {{ $progress ? $progress->progress_percent : 0 }}%"></div>
                                    </div>
                                    <a href="{{ route('videos.watch', $video->id) }}" class="inline-flex w-full items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 rounded-lg text-xs transition-colors">Resume Lesson</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Daily Current Affairs Carousel -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-1 h-5 bg-purple-500 rounded-full"></span> Today's Current Affairs
                    </h3>
                    <a href="{{ route('current-affairs.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-850">View Archive &rarr;</a>
                </div>

                @if($currentAffairs->isEmpty())
                    <div class="glass-card p-8 rounded-xl text-center">
                        <p class="text-slate-400 text-sm">No articles published for today yet. Check back later.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($currentAffairs as $article)
                            <div class="glass-card p-5 rounded-xl space-y-3 relative flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-purple-600 uppercase tracking-widest bg-purple-50 px-2.5 py-0.5 rounded border border-purple-100">{{ $article->type->label() }}</span>
                                        @if($article->subject)
                                            <span class="text-[10px] font-semibold text-slate-500">{{ $article->subject->name }}</span>
                                        @endif
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-sm line-clamp-2"><a href="{{ route('current-affairs.show', $article->slug) }}" class="hover:text-purple-500 transition-colors">{{ $article->title }}</a></h4>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-200 pt-3 mt-2 text-xs">
                                    <span class="text-slate-500">{{ $article->publish_date->format('M d, Y') }}</span>
                                    <a href="{{ route('current-affairs.show', $article->slug) }}" class="font-bold text-blue-600 hover:text-blue-800 transition-colors">Read Article &rarr;</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Weekly Progress Graph -->
            <div class="glass-card p-6 rounded-2xl">
                <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-emerald-500 rounded-full"></span> Recent Test Score Analytics
                </h3>
                <div class="h-64">
                    <canvas id="weeklyProgressChart"></canvas>
                </div>
            </div>

        </div>

        <!-- Right: Rankings & Upcoming Tests Sidebar (1/3 width) -->
        <div class="space-y-8">
            
            <!-- Upcoming / Available Exams -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1 h-5 bg-amber-500 rounded-full"></span> Available Test Series
                </h3>
                <div class="space-y-3">
                    @foreach($upcomingTests as $exam)
                        @php $attempt = $exam->getUserAttempt(auth()->user()); @endphp
                        <div class="glass-card p-4 rounded-xl space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-100">{{ $exam->type->label() }}</span>
                                <span class="text-xs font-semibold text-slate-500">{{ $exam->duration_minutes }} Mins</span>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm line-clamp-1">{{ $exam->title }}</h4>
                            <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200">
                                <span class="text-slate-500">Marks: {{ $exam->total_marks }}</span>
                                @if($attempt)
                                    <span class="text-emerald-700 font-bold text-[10px] uppercase bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Attempted</span>
                                @else
                                    <form action="{{ route('exams.start', $exam->slug) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="font-bold text-blue-600 hover:text-blue-800 transition-colors">Start Test &rarr;</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Leaderboard Leader previews -->
            <div class="glass-card p-6 rounded-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-1 h-5 bg-blue-500 rounded-full"></span> Weekly Ranks
                    </h3>
                    <a href="{{ route('leaderboard.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">View All &rarr;</a>
                </div>
                <div class="space-y-3">
                    @forelse($topStudents as $student)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs {{ $student->rank == 1 ? 'bg-yellow-100 text-yellow-800 border border-yellow-300' : ($student->rank == 2 ? 'bg-slate-100 text-slate-800 border border-slate-300' : ($student->rank == 3 ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-50 text-slate-600 border border-slate-200')) }}">
                                    {{ $student->rank }}
                                </span>
                                <span class="text-sm text-slate-700 font-medium">{{ $student->user->name }}</span>
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ round($student->total_score) }} pts</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">No rankings generated yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Chart Script -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('weeklyProgressChart').getContext('2d');
        
        // Prepare chart labels and data
        const attempts = {!! json_encode($weeklyAttempts->map(fn($a) => [
            'date' => $a->submitted_at->format('M d'),
            'score' => $a->score
        ])) !!};
        
        const labels = attempts.map(a => a.date);
        const data = attempts.map(a => a.score);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['No Test Data'],
                datasets: [{
                    label: 'Marks Obtained',
                    data: data.length ? data : [0],
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgb(16, 185, 129)',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });
    });
</script>
@endsection
