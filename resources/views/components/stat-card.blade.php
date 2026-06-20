@props(['value', 'label', 'color' => 'blue'])

@php
    $iconColor = match($color) {
        'blue'            => 'text-blue-600 bg-blue-100',
        'purple'          => 'text-purple-600 bg-purple-100',
        'emerald', 'green'=> 'text-emerald-600 bg-emerald-100',
        'amber', 'yellow' => 'text-amber-600 bg-amber-100',
        default           => 'text-slate-600 bg-slate-100'
    };
@endphp

<div class="glass-card p-5 flex items-center gap-4">
    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 {{ $iconColor }}">
        {{ $slot }}
    </div>
    <div>
        <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block">{{ $label }}</span>
        <span class="text-2xl font-bold block text-slate-800 mt-0.5">{{ $value }}</span>
    </div>
</div>
