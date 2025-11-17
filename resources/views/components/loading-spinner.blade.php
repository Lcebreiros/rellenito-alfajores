@props(['size' => 'md', 'color' => 'indigo'])

@php
    $sizeClasses = [
        'sm' => 'h-4 w-4',
        'md' => 'h-8 w-8',
        'lg' => 'h-12 w-12',
        'xl' => 'h-16 w-16'
    ];

    $colorClasses = [
        'indigo' => 'text-indigo-600 dark:text-indigo-400',
        'white' => 'text-white',
        'gray' => 'text-neutral-400 dark:text-neutral-500',
        'primary' => 'text-indigo-600 dark:text-indigo-400'
    ];

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $colorClass = $colorClasses[$color] ?? $colorClasses['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'inline-block']) }}>
    <svg class="animate-spin {{ $sizeClass }} {{ $colorClass }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
</div>
