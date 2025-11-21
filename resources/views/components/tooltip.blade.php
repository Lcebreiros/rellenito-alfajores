@props([
    'text' => '',
    'position' => 'top', // top, bottom, left, right
])

@php
$positionClasses = [
    'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
    'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
    'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
    'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
];

$arrowClasses = [
    'top' => 'top-full left-1/2 -translate-x-1/2 border-l-transparent border-r-transparent border-b-transparent border-t-neutral-800 dark:border-t-neutral-700',
    'bottom' => 'bottom-full left-1/2 -translate-x-1/2 border-l-transparent border-r-transparent border-t-transparent border-b-neutral-800 dark:border-b-neutral-700',
    'left' => 'left-full top-1/2 -translate-y-1/2 border-t-transparent border-b-transparent border-r-transparent border-l-neutral-800 dark:border-l-neutral-700',
    'right' => 'right-full top-1/2 -translate-y-1/2 border-t-transparent border-b-transparent border-l-transparent border-r-neutral-800 dark:border-r-neutral-700',
];

$positionClass = $positionClasses[$position] ?? $positionClasses['top'];
$arrowClass = $arrowClasses[$position] ?? $arrowClasses['top'];
@endphp

<span x-data="{ tooltip: false }" class="relative inline-flex items-center">
    {{-- Trigger element --}}
    <span @mouseenter="tooltip = true"
          @mouseleave="tooltip = false"
          @focus="tooltip = true"
          @blur="tooltip = false"
          class="cursor-help">
        {{ $slot }}
    </span>

    {{-- Tooltip --}}
    <span x-show="tooltip"
          x-transition:enter="transition ease-out duration-200"
          x-transition:enter-start="opacity-0 scale-95"
          x-transition:enter-end="opacity-100 scale-100"
          x-transition:leave="transition ease-in duration-150"
          x-transition:leave-start="opacity-100 scale-100"
          x-transition:leave-end="opacity-0 scale-95"
          x-cloak
          class="absolute {{ $positionClass }} z-50 px-3 py-2 text-xs font-medium text-white bg-neutral-800 dark:bg-neutral-700 rounded-lg shadow-lg whitespace-nowrap pointer-events-none"
          style="display: none;">
        {{ $text }}

        {{-- Arrow --}}
        <span class="absolute w-0 h-0 border-4 {{ $arrowClass }}"></span>
    </span>
</span>
