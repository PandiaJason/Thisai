@props(['active' => false])

@php
$classes = $active
            ? 'text-sm font-semibold text-blue-600 transition-colors'
            : 'text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
