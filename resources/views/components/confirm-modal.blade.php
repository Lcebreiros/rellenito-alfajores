@props([
    'id' => 'confirm-modal-' . uniqid(),
    'title' => '¿Confirmar acción?',
    'description' => 'Esta acción no se puede deshacer.',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'icon' => 'exclamation',
    'type' => 'danger', // 'danger', 'warning', 'info'
])

@php
$iconColors = [
    'danger' => 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20',
    'warning' => 'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
    'info' => 'text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20',
];

$buttonColors = [
    'danger' => 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500',
    'warning' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
    'info' => 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
];

$iconColorClass = $iconColors[$type] ?? $iconColors['danger'];
$buttonColorClass = $buttonColors[$type] ?? $buttonColors['danger'];
@endphp

<div x-data="{ open: false }"
     x-on:open-modal-{{ $id }}.window="open = true"
     x-on:keydown.escape.window="open = false"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">

    {{-- Backdrop --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="absolute inset-0 bg-neutral-900/50 dark:bg-black/70 backdrop-blur-sm">
    </div>

    {{-- Modal --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl max-w-md w-full border border-neutral-200 dark:border-neutral-800"
         @click.stop>

        <div class="p-6">
            {{-- Ícono --}}
            <div class="flex items-center justify-center w-12 h-12 rounded-full {{ $iconColorClass }} mb-4">
                <x-svg-icon :name="$icon" size="6" />
            </div>

            {{-- Título --}}
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">
                {{ $title }}
            </h3>

            {{-- Descripción --}}
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-6">
                {{ $description }}
            </p>

            {{-- Contenido custom (slot) --}}
            @if($slot->isNotEmpty())
                <div class="mb-6">
                    {{ $slot }}
                </div>
            @endif

            {{-- Botones de acción --}}
            <div class="flex gap-3">
                <button type="button"
                        @click="open = false"
                        class="flex-1 px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 font-medium hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
                    {{ $cancelText }}
                </button>

                <button type="button"
                        {{ $attributes->merge(['class' => "flex-1 px-4 py-2.5 rounded-lg text-white font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 {$buttonColorClass}"]) }}>
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
