@props(['show' => false, 'message' => 'Cargando...'])

<div x-show="{{ $show }}"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-neutral-900/50 dark:bg-neutral-950/70 backdrop-blur-sm"></div>

    <!-- Content -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl px-8 py-6 max-w-sm w-full">
            <div class="flex flex-col items-center space-y-4">
                <!-- Spinner -->
                <x-loading-spinner size="lg" />

                <!-- Message -->
                <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300 text-center">
                    {{ $message }}
                </p>
            </div>
        </div>
    </div>
</div>
