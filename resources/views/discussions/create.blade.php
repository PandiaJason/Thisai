@extends('layouts.app')

@section('title', 'Ask a Question - THISAI')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <a href="{{ route('discussions.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-blue-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to Discussions
    </a>

    <div class="glass-card p-6 rounded-2xl border border-slate-200">
        <h1 class="text-xl font-extrabold text-slate-800 mb-6">Ask a Question</h1>

        <form method="POST" action="{{ route('discussions.store') }}" class="space-y-5">
            @csrf
            @if(request('question_id'))
                <input type="hidden" name="question_id" value="{{ request('question_id') }}">
            @endif

            <div>
                <label for="title" class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Title</label>
                <input type="text" name="title" id="title" required value="{{ old('title') }}" placeholder="e.g., Explain Article 14 of the Constitution" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                @error('title')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subject_id" class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Subject</label>
                <select name="subject_id" id="subject_id" required class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">Select a subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
                @error('subject_id')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="body" class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-1.5">Your Question</label>
                <textarea name="body" id="body" rows="6" required placeholder="Describe your doubt in detail. Include any specific concepts you're confused about..." class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm placeholder:text-slate-400 resize-none">{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl text-sm transition-all shadow-lg shadow-blue-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Post Question
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
