@extends('layouts.app')

@section('title', 'Watching: ' . $video->title . ' - THISAI')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
    
    <!-- Left: Video Player Area (3/4 width) -->
    <div class="lg:col-span-3 space-y-6">
        <!-- Interactive Player Shell -->
        <div class="aspect-video w-full rounded-2xl overflow-hidden bg-black border border-slate-200 shadow-2xl relative">
            <iframe id="bunny-player" src="{{ $embedUrl }}" class="w-full h-full" allowfullscreen allow="autoplay; encrypted-media"></iframe>
        </div>

        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded border border-blue-100 uppercase tracking-wider">{{ $video->subject->name }}</span>
                <span class="text-xs font-semibold text-slate-500">Duration: {{ $video->duration_formatted }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800">{{ $video->title }}</h1>
            @if($video->description)
                <p class="text-slate-600 text-sm leading-relaxed border-t border-slate-200 pt-4">{{ $video->description }}</p>
            @endif
        </div>
    </div>

    <!-- Right: Curriculum Sidebar (1/4 width) -->
    <div class="space-y-4">
        <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg> Course Curriculum
        </h3>
        
        <div class="glass-card rounded-2xl overflow-hidden border border-slate-200 max-h-[600px] overflow-y-auto">
            @foreach($course->sections as $index => $section)
                <div class="border-b border-slate-200 last:border-b-0">
                    <span class="block px-4 py-2.5 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $section->title }}</span>
                    <div class="p-2 space-y-1">
                        @foreach($section->videos as $v)
                            <a href="{{ route('videos.watch', $v->id) }}" class="flex items-start gap-2.5 p-2 rounded-lg transition-colors text-xs {{ $v->id === $video->id ? 'bg-blue-50 border border-blue-100 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">
                                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <div class="flex-1 min-w-0">
                                    <span class="block truncate">{{ $v->title }}</span>
                                    <span class="text-[10px] font-semibold text-slate-500 block mt-0.5">{{ $v->duration_formatted }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

<!-- PlayerJS Event Tracking API script -->
<script src="https://assets.mediadelivery.net/playerjs/playerjs-latest.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const iframe = document.getElementById('bunny-player');
        const player = new playerjs.Player(iframe);

        let lastSavedSeconds = 0;
        const videoId = {{ $video->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        player.on('ready', () => {
            console.log('Player is ready');
            
            // If there's previous progress saved, we seek to it once the player starts playing
            @if($currentProgress)
                player.setCurrentTime({{ $currentProgress->watched_seconds }});
            @endif

            // Set up timeupdate event
            player.on('timeupdate', (data) => {
                const currentSeconds = Math.floor(data.seconds);
                const totalSeconds = Math.floor(data.duration);

                // Save progress every 10 seconds or on significant progress jumps
                if (currentSeconds - lastSavedSeconds >= 10) {
                    lastSavedSeconds = currentSeconds;
                    saveProgress(currentSeconds, totalSeconds);
                }
            });
        });

        function saveProgress(watched, total) {
            fetch('/api/video/progress', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    video_id: videoId,
                    watched_seconds: watched,
                    total_seconds: total
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.completed) {
                    showToast('Lesson completed!');
                }
            })
            .catch(err => console.error('Error saving progress:', err));
        }
    });
</script>
@endsection
