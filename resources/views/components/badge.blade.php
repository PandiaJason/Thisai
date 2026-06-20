@props(['color' => 'blue'])

@php
    $classes = match($color) {
        'blue' => 'text-blue-700 bg-blue-50 border-blue-200',
        'emerald', 'green' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
        'purple' => 'text-purple-700 bg-purple-50 border-purple-200',
        'amber', 'yellow' => 'text-amber-800 bg-amber-50 border-amber-200',
        'red' => 'text-red-700 bg-red-50 border-red-200',
        default => 'text-slate-600 bg-slate-100 border-slate-200'
    };
@endphp

<span {{ $attributes->merge(['class' => 'text-[9px] uppercase font-bold tracking-widest px-2.5 py-0.5 rounded border ' . $classes]) }}>
    {{ $slot }}
</span>
