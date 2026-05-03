<div class="flex flex-col gap-3 min-h-[calc(100svh-9rem)]">

    {{-- ── Barra de búsqueda + controles ─────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">

        {{-- Buscador --}}
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
                placeholder="{{ __('orders.create.search_placeholder') }}"
                autocomplete="off"
                class="w-full rounded-lg border border-neutral-200 dark:border-neutral-700
                       bg-white dark:bg-neutral-900 pl-9 pr-9 py-2 text-sm
                       text-neutral-800 dark:text-neutral-100 placeholder-neutral-400
                       focus:outline-none focus:ring-2 focus:ring-neutral-900/10 dark:focus:ring-white/10
                       transition"
            >
            @if($search !== '')
                <button
                    wire:click="$set('search', '')"
                    class="absolute inset-y-0 right-2 flex items-center px-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition"
                    aria-label="Limpiar"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>

        {{-- Ordenador --}}
        <div class="relative shrink-0">
            <select
                wire:model.live="sort"
                class="appearance-none rounded-lg border border-neutral-200 dark:border-neutral-700
                       bg-white dark:bg-neutral-900 pl-3 pr-8 py-2 text-sm
                       text-neutral-700 dark:text-neutral-200
                       focus:outline-none focus:ring-2 focus:ring-neutral-900/10 dark:focus:ring-white/10
                       cursor-pointer transition"
            >
                <option value="name_asc">A → Z</option>
                <option value="name_desc">Z → A</option>
                <option value="price_asc">{{ __('orders.create.sort_price_asc') }}</option>
                <option value="price_desc">{{ __('orders.create.sort_price_desc') }}</option>
                <option value="newest">{{ __('orders.create.sort_newest') }}</option>
                <option value="oldest">{{ __('orders.create.sort_oldest') }}</option>
                <option value="top">{{ __('orders.create.sort_top') }}</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center">
                <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Filtros ─────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-1.5">
        @foreach([
            ['all',       __('orders.create.filter_all'),       null],
            ['favorites', __('orders.create.filter_favorites'), 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ['top',       __('orders.create.filter_top'),       'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        ] as [$value, $label, $icon])
            <button
                wire:click="$set('filter', '{{ $value }}')"
                @class([
                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold transition',
                    'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900' => $filter === $value,
                    'bg-neutral-100 text-neutral-600 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700' => $filter !== $value,
                ])
            >
                @if($icon)
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                    </svg>
                @endif
                {{ $label }}
            </button>
        @endforeach

        {{-- Contador de resultados --}}
        <span class="ml-auto text-xs text-neutral-400 tabular-nums">
            {{ $products->total() }} {{ __('orders.create.results') }}
        </span>
    </div>

    {{-- ── Grid de productos ───────────────────────────────────────────── --}}
    <div wire:loading.class="opacity-50" class="transition-opacity duration-150">
        @if($products->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 gap-3">
                <div class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                    <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </div>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    @if($filter === 'favorites')
                        {{ __('orders.create.no_favorites') }}
                    @elseif($filter === 'top')
                        {{ __('orders.create.no_top') }}
                    @else
                        {{ __('orders.create.no_results') }}
                    @endif
                </p>
                @if($search !== '' || $filter !== 'all')
                    <button
                        wire:click="$set('search', ''); $set('filter', 'all')"
                        class="text-xs text-blue-600 hover:underline"
                    >
                        {{ __('orders.create.clear_filters') }}
                    </button>
                @endif
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 relative">
                {{-- Spinner de carga --}}
                <div wire:loading
                     class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/60 dark:bg-neutral-900/60 backdrop-blur-sm">
                    <svg class="w-6 h-6 animate-spin text-neutral-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </div>

                @foreach($products as $product)
                    <div class="relative group">
                        {{-- Botón favorito --}}
                        <button
                            wire:click="toggleFavorite({{ $product->id }})"
                            wire:key="fav-{{ $product->id }}"
                            class="absolute top-1.5 right-1.5 z-10 w-6 h-6 flex items-center justify-center rounded-full transition
                                   {{ in_array($product->id, $favoriteIds)
                                        ? 'text-rose-500 bg-rose-50 dark:bg-rose-900/30'
                                        : 'text-neutral-300 bg-white/80 dark:bg-neutral-800/80 opacity-0 group-hover:opacity-100' }}"
                            title="{{ in_array($product->id, $favoriteIds) ? __('orders.create.remove_favorite') : __('orders.create.add_favorite') }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="{{ in_array($product->id, $favoriteIds) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>

                        <livewire:product-card
                            :product-id="$product->id"
                            :key="'pc-'.$product->id"
                        />
                    </div>
                @endforeach
            </div>

            {{-- Paginación --}}
            @if($products->hasPages())
                <div class="mt-4 flex justify-center">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
