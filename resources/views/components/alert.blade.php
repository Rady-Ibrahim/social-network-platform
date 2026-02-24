@props([
    'type' => 'info', // success, error, warning, info
    'rounded' => 'xl',
])

@php
    $roundedClasses = $rounded === 'full' ? 'rounded-full' : 'rounded-xl';

    $map = [
        'success' => 'bg-emerald-50/90 border-emerald-200 text-emerald-700',
        'error' => 'bg-red-50/90 border-red-200 text-red-700',
        'warning' => 'bg-amber-50/90 border-amber-200 text-amber-700',
        'info' => 'bg-sky-50/90 border-sky-200 text-sky-800',
    ];

    $classes = $map[$type] ?? $map['info'];
@endphp

<div {{ $attributes->merge([
    'class' => "px-4 py-2 text-sm font-medium border {$roundedClasses} shadow-sm {$classes}",
]) }}>
    {{ $slot }}
</div>

