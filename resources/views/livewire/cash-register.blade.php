<div
    x-data="{ tab: 'movements' }"
    class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden"
>
    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">{{ __('cash.nav_label') }}</span>
        </div>

        @if($session && $session->isOpen())
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

    {{-- Mensajes flash --}}
    @if(session('ok'))
        <div class="mx-4 mt-3 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-xs dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('ok') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mx-4 mt-3 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-xs dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif

    @if(!$session || !$session->isOpen())
        {{-- CAJA CERRADA: mostrar botón de abrir --}}
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
                        <input type="number" wire:model="openingAmount" min="0" step="0.01"
                               placeholder="0.00"
                               class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 px-3 py-2 text-sm text-right font-mono focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
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
                                class="rounded-lg border border-neutral-300 dark:border-neutral-700 px-3 py-2 text-sm text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                            ✕
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @else
        {{-- CAJA ABIERTA --}}

        {{-- Balance --}}
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

        {{-- Acciones rápidas: + Ingreso / - Egreso --}}
        @if(!$showMovementForm && !$showCloseForm)
            <div class="px-4 pb-3 flex gap-2">
                <button wire:click="$set('movementType', 'ingreso'); $set('showMovementForm', true)"
                        class="flex-1 flex items-center justify-center gap-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 text-sm font-semibold transition">
                    <span class="text-lg leading-none">+</span> {{ __('cash.income') }}
                </button>
                <button wire:click="$set('movementType', 'egreso'); $set('showMovementForm', true)"
                        class="flex-1 flex items-center justify-center gap-1 rounded-lg bg-orange-500 hover:bg-orange-600 text-white px-3 py-2 text-sm font-semibold transition">
                    <span class="text-lg leading-none">−</span> {{ __('cash.expense') }}
                </button>
                <button wire:click="$set('showCloseForm', true)"
                        class="rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-xs text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                        title="{{ __('cash.close_cash') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Formulario movimiento manual --}}
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

        {{-- Formulario cierre --}}
        @if($showCloseForm)
            <div class="px-4 pb-3 space-y-2 border-t border-neutral-200 dark:border-neutral-700 pt-3">
                <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">{{ __('cash.close_cash') }}</p>
                <div>
                    <label class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('cash.closing_amount') }}</label>
                    <input type="number" wire:model="closingAmount" min="0" step="0.01"
                           class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 px-3 py-2 text-sm text-right font-mono mt-1">
                    @error('closingAmount') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <textarea wire:model="closingNote" rows="2" placeholder="{{ __('cash.closing_note_ph') }}"
                          class="w-full rounded-lg border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 px-3 py-2 text-sm resize-none"></textarea>
                <div class="flex gap-2">
                    <button wire:click="closeSession"
                            class="flex-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-3 py-2 text-sm font-semibold transition">
                        {{ __('cash.close_btn') }}
                    </button>
                    <button wire:click="$set('showCloseForm', false)"
                            class="rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-sm text-neutral-600 dark:text-neutral-300">
                        ✕
                    </button>
                </div>
            </div>
        @endif

        {{-- Últimos movimientos --}}
        @if(!$showMovementForm && !$showCloseForm && $movements->count())
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
