@extends('layouts.app')

@section('title', 'Leaderboard & Rankings - THISAI')

@section('content')
<div class="space-y-8">

    <!-- Hero Header -->
    <div class="text-center py-6">
        <span class="text-xs uppercase font-bold tracking-widest text-blue-600 bg-blue-100 px-3 py-1 rounded-full border border-blue-200">Hall of Fame</span>
        <h1 class="text-3xl font-extrabold text-slate-800 mt-2">Rankings & Leaderboard</h1>
        <p class="text-slate-500 text-sm mt-2 max-w-lg mx-auto">Compare your scores, metrics, and exam speed with fellow aspirants preparing across India.</p>
    </div>

    <!-- Filter Controls Bar -->
    <div class="glass-card p-6 rounded-xl space-y-6">
        <form action="{{ route('leaderboard.index') }}" method="GET" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Period Tabs -->
            <div class="flex bg-slate-100 p-1 rounded-lg border border-slate-200 self-start">
                @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'overall' => 'Overall'] as $key => $label)
                    <a href="{{ route('leaderboard.index', array_merge(request()->query(), ['period' => $key])) }}"
                       class="px-4 py-1.5 rounded-md text-xs font-bold transition-all
                       {{ $period === $key 
                           ? 'bg-blue-600 text-white shadow' 
                           : 'text-slate-500 hover:text-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Subject Dropdown Filter -->
            <div class="flex items-center gap-2 w-full md:w-auto">
                <div class="flex-1 md:w-64">
                    <select id="subject" name="subject" class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ request('subject') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition-colors">
                    Filter
                </button>
                <a href="{{ route('leaderboard.index', ['period' => $period]) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2 px-3 rounded-lg text-xs transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($podium->isEmpty() && $remaining->isEmpty())
        <div class="glass-card p-12 rounded-xl text-center border border-dashed border-slate-300">
            <p class="text-slate-400 text-sm">No exam attempts recorded for this period yet.</p>
        </div>
    @else
        <!-- Podium Section (Top 3 Candidates) -->
        <div class="flex flex-col items-center justify-center py-8">
            <div class="flex items-end justify-center gap-4 sm:gap-8 max-w-2xl w-full px-4">
                
                <!-- 2nd Place -->
                @if($podium->count() > 1)
                    @php $second = $podium->get(1); @endphp
                    <div class="flex flex-col items-center flex-1 max-w-[150px] sm:max-w-[180px]">
                        <div class="relative mb-2">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-slate-200 border-2 border-slate-400 flex items-center justify-center overflow-hidden">
                                @if($second->user->avatar)
                                    <img src="{{ asset('storage/' . $second->user->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="font-extrabold text-slate-600 text-base">{{ strtoupper(substr($second->user->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-slate-400 text-[10px] font-bold text-white rounded-full flex items-center justify-center border-2 border-white shadow">2</span>
                        </div>
                        <span class="font-bold text-slate-700 text-xs sm:text-sm text-center truncate w-full">{{ $second->user->name }}</span>
                        <span class="text-[10px] text-slate-500 font-medium">{{ number_format($second->total_score) }} pts</span>
                        <div class="w-full bg-gradient-to-b from-slate-100 to-slate-200 border-t-2 border-slate-300 rounded-t-lg h-24 sm:h-28 mt-4 flex items-center justify-center shadow-lg">
                            <span class="text-2xl font-black text-slate-400/30">II</span>
                        </div>
                    </div>
                @endif

                <!-- 1st Place -->
                @if($podium->count() > 0)
                    @php $first = $podium->get(0); @endphp
                    <div class="flex flex-col items-center flex-1 max-w-[160px] sm:max-w-[200px] relative -translate-y-4">
                        <div class="text-amber-500 mb-1">
                            <svg class="w-6 h-6 fill-amber-400 animate-bounce" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <div class="relative mb-2">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-amber-50 border-4 border-amber-400 flex items-center justify-center overflow-hidden shadow-lg">
                                @if($first->user->avatar)
                                    <img src="{{ asset('storage/' . $first->user->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="font-extrabold text-amber-500 text-lg">{{ strtoupper(substr($first->user->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="absolute -bottom-1 -right-1 w-7 h-7 bg-amber-400 text-xs font-bold text-white rounded-full flex items-center justify-center border-2 border-white shadow-md">1</span>
                        </div>
                        <span class="font-extrabold text-slate-800 text-xs sm:text-base text-center truncate w-full">{{ $first->user->name }}</span>
                        <span class="text-xs text-amber-500 font-bold">{{ number_format($first->total_score) }} pts</span>
                        <div class="w-full bg-gradient-to-b from-amber-50 to-amber-100 border-t-4 border-amber-400 rounded-t-lg h-32 sm:h-36 mt-4 flex items-center justify-center shadow-xl">
                            <span class="text-3xl font-black text-amber-400/30">I</span>
                        </div>
                    </div>
                @endif

                <!-- 3rd Place -->
                @if($podium->count() > 2)
                    @php $third = $podium->get(2); @endphp
                    <div class="flex flex-col items-center flex-1 max-w-[150px] sm:max-w-[180px]">
                        <div class="relative mb-2">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-orange-50 border-2 border-orange-400 flex items-center justify-center overflow-hidden">
                                @if($third->user->avatar)
                                    <img src="{{ asset('storage/' . $third->user->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="font-extrabold text-orange-600 text-base">{{ strtoupper(substr($third->user->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-orange-500 text-[10px] font-bold text-white rounded-full flex items-center justify-center border-2 border-white shadow">3</span>
                        </div>
                        <span class="font-bold text-slate-700 text-xs sm:text-sm text-center truncate w-full">{{ $third->user->name }}</span>
                        <span class="text-[10px] text-slate-500 font-medium">{{ number_format($third->total_score) }} pts</span>
                        <div class="w-full bg-gradient-to-b from-orange-50 to-orange-100/80 border-t-2 border-orange-300 rounded-t-lg h-20 sm:h-24 mt-4 flex items-center justify-center shadow-lg">
                            <span class="text-2xl font-black text-orange-400/30">III</span>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <!-- Full Ranking Details Table -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Candidate Standings</h2>
                <span class="text-xs font-semibold text-slate-400">Period: {{ ucfirst($period) }}</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                            <th class="px-6 py-4 w-20">Rank</th>
                            <th class="px-6 py-4">Aspirant Name</th>
                            <th class="px-6 py-4 text-center">Exams Taken</th>
                            <th class="px-6 py-4 text-center">Avg Accuracy</th>
                            <th class="px-6 py-4 text-right">Total Score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($podium as $row)
                            @php $isCurrentUser = $row->user_id === auth()->id(); @endphp
                            <tr class="hover:bg-blue-50 transition-colors {{ $isCurrentUser ? 'bg-blue-50 border-l-4 border-blue-500 font-bold' : '' }}">
                                <td class="px-6 py-4">
                                    <span class="flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold 
                                    {{ $row->rank === 1 ? 'bg-amber-400 text-white' : ($row->rank === 2 ? 'bg-slate-300 text-slate-700' : 'bg-orange-400 text-white') }}">
                                        {{ $row->rank }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center overflow-hidden border border-blue-200">
                                            @if($row->user->avatar)
                                                <img src="{{ asset('storage/' . $row->user->avatar) }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-[10px] font-extrabold text-blue-600">{{ strtoupper(substr($row->user->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="block text-slate-800 font-semibold">{{ $row->user->name }}</span>
                                            @if($row->user->studentProfile && $row->user->studentProfile->target_exam)
                                                <span class="block text-[9px] text-slate-400 font-normal">Target: {{ $row->user->studentProfile->target_exam }} ({{ $row->user->studentProfile->target_year }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-slate-600">{{ $row->total_exams }}</td>
                                <td class="px-6 py-4 text-center font-semibold text-slate-600">{{ number_format($row->average_accuracy, 1) }}%</td>
                                <td class="px-6 py-4 text-right font-extrabold text-blue-600">{{ number_format($row->total_score) }}</td>
                            </tr>
                        @endforeach

                        @foreach($remaining as $row)
                            @php $isCurrentUser = $row->user_id === auth()->id(); @endphp
                            <tr class="hover:bg-blue-50 transition-colors {{ $isCurrentUser ? 'bg-blue-50 border-l-4 border-blue-500 font-bold' : '' }}">
                                <td class="px-6 py-4 font-extrabold text-slate-500 text-center w-20">{{ $row->rank }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200">
                                            @if($row->user->avatar)
                                                <img src="{{ asset('storage/' . $row->user->avatar) }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-[10px] font-extrabold text-slate-500">{{ strtoupper(substr($row->user->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="block text-slate-800 font-semibold">{{ $row->user->name }}</span>
                                            @if($row->user->studentProfile && $row->user->studentProfile->target_exam)
                                                <span class="block text-[9px] text-slate-400 font-normal">Target: {{ $row->user->studentProfile->target_exam }} ({{ $row->user->studentProfile->target_year }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-slate-600">{{ $row->total_exams }}</td>
                                <td class="px-6 py-4 text-center font-semibold text-slate-600">{{ number_format($row->average_accuracy, 1) }}%</td>
                                <td class="px-6 py-4 text-right font-extrabold text-slate-700">{{ number_format($row->total_score) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
