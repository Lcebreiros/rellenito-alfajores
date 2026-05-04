<div class="flex flex-col gap-3">

    {{-- Barra búsqueda + ordenamiento --}}
    <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">

        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                </svg>
            </div>
            <input
                type="search"
                wire:model.live.debounce.250ms="search"
                placeholder="{{ __('orders.create.search_services_ph') }}"
                autocomplete="off"
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-700
                       bg-white dark:bg-neutral-900 pl-9 pr-9 py-2 text-sm
                       text-neutral-800 dark:text-neutral-100 placeholder-neutral-400
                       focus:outline-none focus:ring-2 focus:ring-neutral-900/10 dark:focus:ring-white/10 transition"
            >
            @if($search !== '')
                <button wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-2 flex items-center px-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>

        <div class="relative shrink-0">
            <select wire:model.live="sort"
                    class="appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700
                           bg-white dark:bg-neutral-900 pl-3 pr-8 py-2 text-sm
                           text-neutral-700 dark:text-neutral-200
                           focus:outline-none focus:ring-2 focus:ring-neutral-900/10 dark:focus:ring-white/10
                           cursor-pointer transition">
                <option value="name_asc">{{ __('orders.create.sort_services_name_asc') }}</option>
                <option value="name_desc">{{ __('orders.create.sort_services_name_desc') }}</option>
                <option value="price_asc">{{ __('orders.create.sort_services_price_asc') }}</option>
                <option value="price_desc">{{ __('orders.create.sort_services_price_desc') }}</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center">
                <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Contador --}}
    <div class="flex items-center">
        <span class="text-xs text-neutral-400 tabular-nums">
            {{ $services->total() }} {{ __('orders.create.results') }}
        </span>
    </div>

    {{-- Grid --}}
    <div wire:loading.class="opacity-50" class="transition-opacity duration-150">
        @if($services->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 gap-3">
                <div class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                    <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('orders.create.no_services') }}</p>
                @if($search !== '')
                    <button wire:click="$set('search', '')" class="text-xs text-blue-600 hover:underline">
                        {{ __('orders.create.clear_filters') }}
                    </button>
                @endif
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 relative">
                <div wire:loading
                     class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/60 dark:bg-neutral-900/60 backdrop-blur-sm">
                    <svg class="w-6 h-6 animate-spin text-neutral-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>
                @foreach($services as $service)
                    <livewire:service-card :service-id="$service->id" :key="'sc-'.$service->id" />
                @endforeach
            </div>

            @if($services->hasPages())
                <div class="mt-4 flex justify-center">
                    {{ $services->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
