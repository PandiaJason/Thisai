@props(['course'])

<div class="glass-card overflow-hidden flex flex-col justify-between">
    <div>
        <!-- Thumbnail -->
        <div class="aspect-video w-full bg-slate-100 relative">
            @if($course->thumbnail)
                <img src="{{ asset('storage/' . $course->thumbnail) }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-tr from-blue-50 to-indigo-50 flex items-center justify-center border-b border-blue-100/60">
                    <span class="text-xs font-bold text-blue-600/80 uppercase tracking-widest">{{ $course->subject->name ?? 'General Studies' }}</span>
                </div>
            @endif
            <span class="absolute top-3 left-3 text-[9px] font-bold text-white uppercase tracking-widest px-2.5 py-0.5 rounded {{ $course->difficulty->value == 'beginner' ? 'bg-emerald-600' : ($course->difficulty->value == 'intermediate' ? 'bg-amber-600' : 'bg-red-600') }}">
                {{ $course->difficulty->label() }}
            </span>
        </div>

        <!-- Card Body -->
        <div class="p-5 space-y-3">
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest">{{ $course->subject->name ?? 'General Studies' }}</span>
            <h3 class="font-extrabold text-slate-800 text-base line-clamp-1">
                <a href="{{ route('courses.show', $course->slug) }}" class="hover:text-blue-600 transition-colors">{{ $course->title }}</a>
            </h3>
            <p class="text-slate-500 text-xs line-clamp-2 leading-relaxed">{{ strip_tags($course->description) }}</p>
        </div>
    </div>

    <!-- Footer Details -->
    <div class="p-5 border-t border-slate-200 flex items-center justify-between text-xs font-semibold text-slate-500 mt-2">
        <span>Duration: {{ $course->duration_hours ?? 0 }} hrs</span>
        <div class="flex items-center gap-3">
            <span class="text-slate-800 font-bold">
                @if($course->is_free)
                    <span class="text-emerald-700 font-bold uppercase tracking-wider text-[10px] bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-200">Free</span>
                @else
                    ₹{{ number_format($course->price) }}
                @endif
            </span>
            <a href="{{ route('courses.show', $course->slug) }}" class="bg-blue-600 hover:bg-blue-500 px-3 py-1.5 rounded text-white font-bold transition-colors">View Course</a>
        </div>
    </div>
</div>
