<div class="w-full">
    <style>
        /* Animaciones y estilos para tarjetas de métodos de pago estilo oficial */
        .payment-card-official {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
            position: relative;
            overflow: hidden;
        }

        .payment-card-official::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
            opacity: 0;
            transition: opacity 0.3s;
        }

        .payment-card-official:hover:not(.payment-card-selected) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.15);
        }

        .payment-card-official:hover::before {
            opacity: 1;
        }

        .payment-card-official:active {
            transform: translateY(-2px) scale(0.98);
        }

        .payment-card-selected {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.4), 0 8px 16px -2px rgba(99, 102, 241, 0.2);
        }

        .dark .payment-card-selected {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.5), 0 8px 16px -2px rgba(99, 102, 241, 0.3);
        }

        .payment-logo-container {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: transparent;
            border-radius: 4px;
            padding: 0;
        }

        .dark .payment-logo-container {
            background: transparent;
        }

        .payment-card-official:hover .payment-logo-container {
            transform: scale(1.02);
        }

        .payment-card-selected .payment-logo-container {
            transform: scale(1.04);
        }

        .payment-logo-img {
            object-fit: contain;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.05));
        }

        .payment-checkmark {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .payment-card-official:not(.payment-card-selected) .payment-checkmark {
            opacity: 0;
            transform: scale(0.3) rotate(-90deg);
        }

        .payment-card-selected .payment-checkmark {
            opacity: 1;
            transform: scale(1) rotate(0deg);
        }

        .payment-api-badge {
            transition: all 0.2s;
        }

        .payment-card-selected .payment-api-badge {
            background: rgb(99 102 241);
            color: white;
        }

        .dark .payment-card-selected .payment-api-badge {
            background: rgb(129 140 248);
        }
    </style>

    @if($paymentMethods->isNotEmpty())
        <div class="mb-6">
            <h3 class="text-base font-bold text-neutral-800 dark:text-neutral-200 mb-4 flex items-center gap-2">
                <x-heroicon-o-credit-card class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                Métodos de Pago
            </h3>

            <div class="flex flex-wrap gap-2">
                @foreach($paymentMethods as $pm)
                    <div
                        wire:click="togglePaymentMethod({{ $pm->id }})"
                        class="payment-card-official {{ in_array($pm->id, $selectedPaymentMethods) ? 'payment-card-selected' : '' }}
                               relative flex items-center justify-center p-2 rounded-lg
                               h-[56px] w-[84px] shrink-0
                               bg-white dark:bg-neutral-800"
                        role="button"
                        tabindex="0"
                        aria-pressed="{{ in_array($pm->id, $selectedPaymentMethods) ? 'true' : 'false' }}"
                        aria-label="Seleccionar {{ $pm->name }}"
                    >
                        {{-- Checkmark en la esquina --}}
                        <div class="payment-checkmark absolute top-1 right-1 z-10">
                            <div class="w-4.5 h-4.5 rounded-full bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center shadow-md">
                                <x-heroicon-o-check class="w-3 h-3 text-white stroke-[3]" />
                            </div>
                        </div>

                        {{-- Logo del método de pago --}}
                        <div class="payment-logo-container w-full h-full flex items-center justify-center overflow-hidden">
                            @if($pm->hasLogo())
                                <img
                                    src="{{ asset('images/' . $pm->getLogo()) }}"
                                    alt="{{ $pm->name }}"
                                    class="payment-logo-img w-full h-full object-contain px-1 py-0"
                                    loading="lazy"
                                />
                            @else
                                <div class="flex items-center justify-center w-full h-full bg-neutral-100 dark:bg-neutral-700 rounded">
                                    <x-dynamic-component
                                        :component="'heroicon-o-' . $pm->getIcon()"
                                        class="w-8 h-8 text-neutral-600 dark:text-neutral-400"
                                    />
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="mb-6 p-4 rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20">
            <div class="flex items-start gap-3">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                <div>
                    <div class="text-sm font-bold text-amber-900 dark:text-amber-100">
                        No hay métodos de pago configurados
                    </div>
                    <div class="text-xs text-amber-700 dark:text-amber-300 mt-1">
                        Ve a <a href="{{ route('payment-methods.index') }}" class="underline hover:text-amber-900 dark:hover:text-amber-100 font-semibold">Métodos de Pago</a> para agregar algunos.
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
