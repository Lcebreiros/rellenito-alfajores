@props([
    'icon' => 'box',
    'title' => '',
    'description' => '',
    'actionUrl' => null,
    'actionText' => null,
    'actionIcon' => 'plus'
])

<div {{ $attributes->merge(['class' => 'text-center py-16 px-4']) }}>
    {{-- Ícono ilustrativo con fondo --}}
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-neutral-100 dark:bg-neutral-800/50 mb-4">
        <x-svg-icon :name="$icon" size="8" class="text-neutral-400 dark:text-neutral-500" />
    </div>

    {{-- Título --}}
    @if($title)
        <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">
            {{ $title }}
        </h3>
    @endif

    {{-- Descripción --}}
    @if($description)
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-6 max-w-md mx-auto">
            {{ $description }}
        </p>
    @endif

    {{-- Slot para contenido custom --}}
    @if($slot->isNotEmpty())
        <div class="mb-6">
            {{ $slot }}
        </div>
    @endif

    {{-- Acción principal --}}
    @if($actionUrl && $actionText)
        <a href="{{ $actionUrl }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-colors">
            <x-svg-icon :name="$actionIcon" size="5" />
            {{ $actionText }}
        </a>
    @endif
</div>
