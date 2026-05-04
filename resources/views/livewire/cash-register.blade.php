<div>

{{-- ======== MODAL CIERRE DE CAJA (fixed, shared for both modes) ======== --}}
@if($showCloseModal)
<div class="fixed inset-0 z-[200] flex items-center justify-center p-4" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <div class="relative z-10 w-full max-w-md rounded-2xl bg-white dark:bg-neutral-900 shadow-2xl overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h2 class="text-base font-bold text-neutral-800 dark:text-neutral-100">{{ __('cash.close_cash') }}</h2>
            </div>
            <button wire:click="$set('showCloseModal', false)"
                    class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition rounded-lg p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Resumen de sesión --}}
        @if($closeStats)
        <div class="px-6 py-4 space-y-3">
            <p class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">
                Resumen de la sesión · {{ $closeStats['salesCount'] }} {{ $closeStats['salesCount'] === 1 ? 'venta' : 'ventas' }}
            </p>

            <div class="grid grid-cols-2 gap-2">

                {{-- Total vendido --}}
                <div class="col-span-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 px-4 py-3 flex items-center justify-between">
                    <span class="text-xs font-medium text-indigo-700 dark:text-indigo-300">Total vendido</span>
                    <span class="text-lg font-bold font-mono text-indigo-800 dark:text-indigo-200">
                        ${{ number_format($closeStats['totalSold'], 2, ',', '.') }}
                    </span>
                </div>

                {{-- Ingresos manuales --}}
                @if($closeStats['ingresos'] > 0)
                <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 px-3 py-2.5 flex items-center justify-between">
                    <span class="text-xs text-blue-700 dark:text-blue-300">{{ __('cash.income') }}</span>
                    <span class="text-sm font-semibold font-mono text-blue-700 dark:text-blue-300">+${{ number_format($closeStats['ingresos'], 2, ',', '.') }}</span>
                </div>
                @endif

                {{-- Egresos manuales --}}
                @if($closeStats['egresos'] > 0)
                <div class="rounded-xl bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 px-3 py-2.5 flex items-center justify-between">
                    <span class="text-xs text-orange-700 dark:text-orange-300">{{ __('cash.expense') }}</span>
                    <span class="text-sm font-semibold font-mono text-orange-700 dark:text-orange-300">-${{ number_format($closeStats['egresos'], 2, ',', '.') }}</span>
                </div>
                @endif

                {{-- Efectivo esperado en caja --}}
                <div class="{{ ($closeStats['ingresos'] > 0 || $closeStats['egresos'] > 0) ? '' : 'col-span-2' }} rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-3 py-2.5 flex items-center justify-between">
                    <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">Esperado en caja</span>
                    <span class="text-sm font-bold font-mono text-emerald-700 dark:text-emerald-300">
                        ${{ number_format($closeStats['expectedCash'], 2, ',', '.') }}
                    </span>
                </div>

                {{-- Ganancia estimada (si hay datos de costo) --}}
                @if($closeStats['hasCostData'])
                <div class="col-span-2 rounded-xl bg-violet-50 dark:bg-violet-900/20 border border-violet-100 dark:border-violet-800 px-4 py-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-violet-700 dark:text-violet-300">Ganancia estimada</span>
                        <span class="text-lg font-bold font-mono {{ $closeStats['profit'] >= 0 ? 'text-violet-800 dark:text-violet-200' : 'text-rose-600' }}">
                            ${{ number_format($closeStats['profit'], 2, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs text-violet-500 dark:text-violet-400">
                        <span>Costo mercadería: ${{ number_format($closeStats['totalCost'], 2, ',', '.') }}</span>
                        @if($closeStats['totalSold'] > 0)
                            <span>{{ number_format(($closeStats['profit'] / $closeStats['totalSold']) * 100, 1) }}% margen</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="border-t border-neutral-100 dark:border-neutral-800"></div>
        @endif

        {{-- Formulario de cierre --}}
        <div class="px-6 py-4 space-y-3">
            <p class="text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('cash.closing_amount') }}</p>
            <div>
                <input type="number" wire:model="closingAmount" min="0" step="0.01"
                       class="w-full rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800
                              px-4 py-2.5 text-sm text-right font-mono focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                @error('closingAmount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <textarea wire:model="closingNote" rows="2" placeholder="{{ __('cash.closing_note_ph') }}"
                      class="w-full rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800
                             px-4 py-2.5 text-sm resize-none focus:ring-2 focus:ring-rose-500 focus:border-transparent"></textarea>
            <div class="flex gap-2 pt-1">
                <button wire:click="closeSession"
                        class="flex-1 rounded-xl bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 text-sm font-semibold transition">
                    {{ __('cash.close_btn') }}
                </button>
                <button wire:click="$set('showCloseModal', false)"
                        class="rounded-xl border border-neutral-300 dark:border-neutral-600 px-4 py-2.5 text-sm
                               text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                    Cancelar
                </button>
            </div>
        </div>

    </div>
</div>
@endif

{{-- ======== COMPACT MODE: contenedor siempre visible en el header ======== --}}
@if($compact)
<div class="relative"
     x-data="{ closingModal: @entangle('showCloseModal') }"
     x-on:click.outside="if (!closingModal) { $wire.set('showOpenForm', false); $wire.set('showMovementForm', false); }">

    {{-- Contenedor principal (siempre visible) --}}
    <div class="flex items-center gap-2 px-2.5 py-1.5 min-h-14 rounded-xl border shadow-sm text-xs
                {{ $session?->isOpen()
                    ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/20'
                    : 'border-neutral-200 bg-neutral-50 dark:border-neutral-700 dark:bg-neutral-800/60' }}">

        {{-- Icono caja --}}
        <svg class="w-3.5 h-3.5 shrink-0 {{ $session?->isOpen() ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-400 dark:text-neutral-500' }}"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>

        {{-- Nombre "Caja" --}}
        <span class="font-semibold {{ $session?->isOpen() ? 'text-emerald-800 dark:text-emerald-300' : 'text-neutral-600 dark:text-neutral-400' }}">
            {{ __('cash.nav_label') }}
        </span>

        {{-- Operario (solo si es empleado) --}}
        @if($operatorName)
            <span class="text-neutral-400 dark:text-neutral-500">· {{ $operatorName }}</span>
        @endif

        {{-- Indicador saldo cuando está abierta --}}
        @if($session?->isOpen())
            <span class="w-px h-3.5 bg-emerald-200 dark:bg-emerald-700 shrink-0"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
            <span class="font-mono font-bold text-emerald-700 dark:text-emerald-300 tabular-nums">
                ${{ number_format($balance, 2, ',', '.') }}
            </span>
        @else
            <span class="w-px h-3.5 bg-neutral-200 dark:bg-neutral-700 shrink-0"></span>
            <span class="text-neutral-400 dark:text-neutral-500">{{ __('cash.closed') }}</span>
        @endif

        {{-- Separador --}}
        <span class="w-px h-3.5 bg-neutral-200 dark:bg-neutral-600 shrink-0"></span>

        {{-- Botones de acción --}}
        @if(!$session || !$session->isOpen())
            <button wire:click="$set('showOpenForm', true)"
                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg font-semibold transition
                           bg-emerald-600 hover:bg-emerald-700 text-white text-xs">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('cash.open_cash') }}
            </button>
        @else
            {{-- Ingreso --}}
            <button wire:click="$set('movementType', 'ingreso'); $set('showMovementForm', true)"
                    title="{{ __('cash.income') }}"
                    class="inline-flex items-center justify-center w-6 h-6 rounded-md font-bold transition text-sm
                           {{ $showMovementForm && $movementType === 'ingreso' ? 'ring-2 ring-blue-400 ' : '' }}bg-blue-100 hover:bg-blue-200 text-blue-700 dark:bg-blue-900/30 dark:hover:bg-blue-900/50 dark:text-blue-300">
                +
            </button>
            {{-- Egreso --}}
            <button wire:click="$set('movementType', 'egreso'); $set('showMovementForm', true)"
                    title="{{ __('cash.expense') }}"
                    class="inline-flex items-center justify-center w-6 h-6 rounded-md font-bold transition text-sm
                           {{ $showMovementForm && $movementType === 'egreso' ? 'ring-2 ring-orange-400 ' : '' }}bg-orange-100 hover:bg-orange-200 text-orange-700 dark:bg-orange-900/30 dark:hover:bg-orange-900/50 dark:text-orange-300">
                −
            </button>
            {{-- Cerrar caja → abre modal con resumen --}}
            <button wire:click="openCloseModal"
                    title="{{ __('cash.close_cash') }}"
                    class="inline-flex items-center justify-center w-6 h-6 rounded-md transition
                           bg-neutral-100 hover:bg-neutral-200 text-neutral-500 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-neutral-300">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>

    {{-- Panel flotante (abrir caja + ingreso/egreso) --}}
    @if($showOpenForm || $showMovementForm)
    <div class="absolute left-0 top-full mt-2 z-50 w-72 rounded-xl border border-neutral-200 dark:border-neutral-700
                bg-white dark:bg-neutral-900 shadow-xl overflow-hidden">

        @if($errors->any())
            <div class="mx-4 mt-3 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-xs
                        dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        @if($showOpenForm)
            <div class="p-4 space-y-3">
                <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">{{ __('cash.opening_amount') }}</p>
                <div>
                    <input type="number" wire:model="openingAmount" min="0" step="0.01" placeholder="0.00"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800
                                  px-3 py-2 text-sm text-right font-mono focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           autofocus>
                    <p class="mt-1 text-xs text-neutral-500">{{ __('cash.opening_amount_hint') }}</p>
                    @error('openingAmount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex gap-2">
                    <button wire:click="openSession"
                            class="flex-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 text-sm font-semibold transition">
                        {{ __('cash.open_btn') }}
                    </button>
                    <button wire:click="$set('showOpenForm', false)"
                            class="rounded-lg border border-neutral-300 dark:border-neutral-700 px-3 py-2 text-sm
                                   text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                        ✕
                    </button>
                </div>
            </div>
        @endif

        @if($showMovementForm)
            <div class="p-4 space-y-2">
                <div class="flex gap-2">
                    <button wire:click="$set('movementType', 'ingreso')"
                            class="flex-1 rounded-lg px-2 py-1.5 text-xs font-semibold transition border
                                   {{ $movementType === 'ingreso' ? 'bg-blue-600 text-white border-blue-600' : 'border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300' }}">
                        + {{ __('cash.income') }}
                    </button>
                    <button wire:click="$set('movementType', 'egreso')"
                            class="flex-1 rounded-lg px-2 py-1.5 text-xs font-semibold transition border
                                   {{ $movementType === 'egreso' ? 'bg-orange-500 text-white border-orange-500' : 'border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300' }}">
                        − {{ __('cash.expense') }}
                    </button>
                </div>
                <input type="number" wire:model="movementAmount" min="0.01" step="0.01" placeholder="0.00"
                       class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800
                              px-3 py-2 text-sm text-right font-mono">
                @error('movementAmount') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                <input type="text" wire:model="movementDesc" placeholder="{{ __('cash.movement_desc_ph') }}"
                       class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800
                              px-3 py-2 text-sm">
                @error('movementDesc') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                <div class="flex gap-2">
                    <button wire:click="addMovement"
                            class="flex-1 rounded-lg bg-neutral-800 dark:bg-neutral-100 text-white dark:text-neutral-900
                                   px-3 py-2 text-sm font-semibold transition hover:bg-neutral-700 dark:hover:bg-neutral-200">
                        {{ __('cash.save_movement') }}
                    </button>
                    <button wire:click="$set('showMovementForm', false)"
                            class="rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-sm
                                   text-neutral-600 dark:text-neutral-300">
                        ✕
                    </button>
                </div>
            </div>
        @endif
    </div>
    @endif

</div>

@else
{{-- ======== FULL MODE: card ======== --}}
<div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">{{ __('cash.nav_label') }}</span>
        </div>

        @if($session?->isOpen())
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ __('cash.open') }}
            </span>
        @else
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400 text-xs font-semibold">
                {{ __('cash.closed') }}
            </span>
        @endif
    </div>

    {{-- Flash messages --}}
    @if(session('ok'))
        <div class="mx-4 mt-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-xs
                    dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('ok') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mx-4 mt-3 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-xs
                    dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    @if(!$session || !$session->isOpen())
        {{-- CAJA CERRADA --}}
        <div class="p-4">
            @if(!$showOpenForm)
                <button wire:click="$set('showOpenForm', true)"
                        class="w-full flex items-center justify-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('cash.open_cash') }}
                </button>
            @else
                <div class="space-y-3">
                    <p class="text-sm font-medium text-neutral-700 dark:text-neutral-200">{{ __('cash.opening_amount') }}</p>
                    <div>
                        <input type="number" wire:model="openingAmount" min="0" step="0.01" placeholder="0.00"
                               class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800
                                      px-3 py-2 text-sm text-right font-mono focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               autofocus>
                        <p class="mt-1 text-xs text-neutral-500">{{ __('cash.opening_amount_hint') }}</p>
                        @error('openingAmount') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="openSession"
                                class="flex-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 text-sm font-semibold transition">
                            {{ __('cash.open_btn') }}
                        </button>
                        <button wire:click="$set('showOpenForm', false)"
                                class="rounded-lg border border-neutral-300 dark:border-neutral-700 px-3 py-2 text-sm
                                       text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                            ✕
                        </button>
                    </div>
                </div>
            @endif
        </div>

    @else
        {{-- CAJA ABIERTA --}}
        <div class="px-4 pt-3 pb-2">
            <div class="rounded-lg bg-neutral-50 dark:bg-neutral-800 px-4 py-3 flex items-center justify-between">
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('cash.balance') }}</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 font-mono">
                        ${{ number_format($balance, 2, ',', '.') }}
                    </p>
                </div>
                <div class="text-right text-xs text-neutral-500 dark:text-neutral-400 space-y-0.5">
                    <div>{{ __('cash.opening') }}: <span class="font-semibold">${{ number_format($session->opening_amount, 2, ',', '.') }}</span></div>
                    <div>{{ __('cash.sales_total') }}: <span class="font-semibold text-emerald-600">${{ number_format($session->salesTotal(), 2, ',', '.') }}</span></div>
                    <div>{{ __('cash.sales_count', ['n' => $session->salesCount()]) }}</div>
                </div>
            </div>
        </div>

        @if(!$showMovementForm)
            <div class="px-4 pb-3 flex gap-2">
                <button wire:click="$set('movementType', 'ingreso'); $set('showMovementForm', true)"
                        class="flex-1 flex items-center justify-center gap-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 text-sm font-semibold transition">
                    <span class="text-lg leading-none">+</span> {{ __('cash.income') }}
                </button>
                <button wire:click="$set('movementType', 'egreso'); $set('showMovementForm', true)"
                        class="flex-1 flex items-center justify-center gap-1 rounded-lg bg-orange-500 hover:bg-orange-600 text-white px-3 py-2 text-sm font-semibold transition">
                    <span class="text-lg leading-none">−</span> {{ __('cash.expense') }}
                </button>
                <button wire:click="openCloseModal"
                        class="rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-xs
                               text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                        title="{{ __('cash.close_cash') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @if($showMovementForm)
            <div class="px-4 pb-3 space-y-2 border-t border-neutral-200 dark:border-neutral-700 pt-3">
                <div class="flex gap-2">
                    <button wire:click="$set('movementType', 'ingreso')"
                            class="flex-1 rounded-lg px-2 py-1.5 text-xs font-semibold transition border
                                   {{ $movementType === 'ingreso' ? 'bg-blue-600 text-white border-blue-600' : 'border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300' }}">
                        + {{ __('cash.income') }}
                    </button>
                    <button wire:click="$set('movementType', 'egreso')"
                            class="flex-1 rounded-lg px-2 py-1.5 text-xs font-semibold transition border
                                   {{ $movementType === 'egreso' ? 'bg-orange-500 text-white border-orange-500' : 'border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300' }}">
                        − {{ __('cash.expense') }}
                    </button>
                </div>
                <input type="number" wire:model="movementAmount" min="0.01" step="0.01" placeholder="0.00"
                       class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 px-3 py-2 text-sm text-right font-mono">
                @error('movementAmount') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                <input type="text" wire:model="movementDesc" placeholder="{{ __('cash.movement_desc_ph') }}"
                       class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 px-3 py-2 text-sm">
                @error('movementDesc') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                <div class="flex gap-2">
                    <button wire:click="addMovement"
                            class="flex-1 rounded-lg bg-neutral-800 dark:bg-neutral-100 text-white dark:text-neutral-900 px-3 py-2 text-sm font-semibold transition hover:bg-neutral-700 dark:hover:bg-neutral-200">
                        {{ __('cash.save_movement') }}
                    </button>
                    <button wire:click="$set('showMovementForm', false)"
                            class="rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-sm text-neutral-600 dark:text-neutral-300">
                        ✕
                    </button>
                </div>
            </div>
        @endif

        {{-- Últimos movimientos --}}
        @if(!$showMovementForm && $movements->count())
            <div class="border-t border-neutral-200 dark:border-neutral-700">
                <div class="px-4 py-2 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">
                    {{ __('cash.movements') }}
                </div>
                <div class="divide-y divide-neutral-100 dark:divide-neutral-800 max-h-48 overflow-y-auto">
                    @foreach($movements as $m)
                        <div class="flex items-center justify-between px-4 py-2">
                            <div class="flex items-center gap-2 min-w-0">
                                @if($m->isPositive())
                                    <span class="w-5 h-5 flex items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 text-xs font-bold shrink-0">+</span>
                                @else
                                    <span class="w-5 h-5 flex items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-600 text-xs font-bold shrink-0">−</span>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-neutral-700 dark:text-neutral-200 truncate">{{ $m->description }}</p>
                                    <p class="text-xs text-neutral-400">{{ $m->created_at->format('H:i') }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold font-mono shrink-0 ml-2 {{ $m->isPositive() ? 'text-emerald-600' : 'text-orange-600' }}">
                                {{ $m->isPositive() ? '+' : '−' }}${{ number_format($m->amount, 2, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="px-4 py-2 text-center">
                    <a href="{{ route('cash.index') }}" class="text-xs text-blue-600 hover:underline">
                        Ver historial completo →
                    </a>
                </div>
            </div>
        @endif
    @endif
</div>
@endif

</div>
