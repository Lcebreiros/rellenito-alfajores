@props([
    'type' => 'success', // success, error, warning, info
    'title' => '',
    'message' => '',
    'duration' => 5000, // milliseconds
])

@php
$typeStyles = [
    'success' => [
        'bg' => 'bg-emerald-50 dark:bg-emerald-900/20',
        'border' => 'border-emerald-200 dark:border-emerald-800',
        'icon' => 'text-emerald-600 dark:text-emerald-400',
        'iconName' => 'check',
    ],
    'error' => [
        'bg' => 'bg-rose-50 dark:bg-rose-900/20',
        'border' => 'border-rose-200 dark:border-rose-800',
        'icon' => 'text-rose-600 dark:text-rose-400',
        'iconName' => 'x',
    ],
    'warning' => [
        'bg' => 'bg-yellow-50 dark:bg-yellow-900/20',
        'border' => 'border-yellow-200 dark:border-yellow-800',
        'icon' => 'text-yellow-600 dark:text-yellow-400',
        'iconName' => 'exclamation',
    ],
    'info' => [
        'bg' => 'bg-indigo-50 dark:bg-indigo-900/20',
        'border' => 'border-indigo-200 dark:border-indigo-800',
        'icon' => 'text-indigo-600 dark:text-indigo-400',
        'iconName' => 'info',
    ],
];

$styles = $typeStyles[$type] ?? $typeStyles['info'];
@endphp

<div x-data="{
        show: false,
        init() {
            this.$nextTick(() => {
                this.show = true;
                setTimeout(() => this.show = false, {{ $duration }});
            });
        }
     }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-x-full"
     x-transition:enter-end="opacity-100 translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-x-0"
     x-transition:leave-end="opacity-0 translate-x-full"
     {{ $attributes->merge(['class' => "max-w-sm w-full pointer-events-auto"]) }}
     style="display: none;">

    <div class="flex items-start gap-3 p-4 rounded-lg border {{ $styles['bg'] }} {{ $styles['border'] }} shadow-lg">
        {{-- Ícono --}}
        <div class="flex-shrink-0">
            <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $styles['icon'] }}">
                <x-svg-icon :name="$styles['iconName']" size="5" />
            </div>
        </div>

        {{-- Contenido --}}
        <div class="flex-1 min-w-0">
            @if($title)
                <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-1">
                    {{ $title }}
                </p>
            @endif

            @if($message)
                <p class="text-sm text-neutral-700 dark:text-neutral-300">
                    {{ $message }}
                </p>
            @endif

            @if($slot->isNotEmpty())
                <div class="text-sm text-neutral-700 dark:text-neutral-300">
                    {{ $slot }}
                </div>
            @endif
        </div>

        {{-- Botón cerrar --}}
        <button @click="show = false"
                class="flex-shrink-0 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition-colors">
            <x-svg-icon name="x" size="5" />
        </button>
    </div>
</div>
