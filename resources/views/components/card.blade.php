@props([
    'padding' => 'md', // none | sm | md | lg
    'elevated' => true,
    'hoverable' => false,
])

@php
    $paddingClasses = match ($padding) {
        'none' => 'p-0',
        'sm' => 'p-3 sm:p-4',
        'lg' => 'p-6 sm:p-8',
        default => 'p-4 sm:p-6',
    };

    $base = 'bg-white shadow sm:rounded-lg transition ' . $paddingClasses;

    if ($elevated) {
        $base .= ' shadow';
    }

    if ($hoverable) {
        $base .= ' hover:shadow-lg hover:-translate-y-0.5';
    }
@endphp

<div {{ $attributes->merge(['class' => $base]) }}>
    {{ $slot }}
</div>

