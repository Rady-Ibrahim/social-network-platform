@props([
    'user',
    'size' => 'md', // xs | sm | md | lg
])

@php
    $sizeClasses = match ($size) {
        'xs' => 'h-6 w-6 text-[0.625rem]',
        'sm' => 'h-8 w-8 text-xs',
        'lg' => 'h-12 w-12 text-base',
        default => 'h-10 w-10 text-sm',
    };
@endphp

@if ($user->avatarUrl())
    <img
        src="{{ $user->avatarUrl() }}"
        alt="{{ $user->name }}"
        {{ $attributes->merge(['class' => "{$sizeClasses} rounded-full object-cover inline-block ring-2 ring-slate-100 shadow-sm"]) }}
    />
@else
    <div
        {{ $attributes->merge([
            'class' =>
                "{$sizeClasses} rounded-full bg-slate-200 flex items-center justify-center font-semibold text-slate-600 ring-2 ring-slate-100 shadow-sm",
        ]) }}
    >
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
@endif

