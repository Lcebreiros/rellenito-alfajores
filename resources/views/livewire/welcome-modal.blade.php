<div>
<style>[x-cloak]{display:none!important}</style>

@php
  $done  = collect($steps)->where('done', true)->count();
  $total = count($steps);
  $pct   = $total > 0 ? round($done / $total * 100) : 0;
  $allDone = $done === $total;
@endphp

{{-- Drawer lateral derecho --}}
<div
  x-data="{ show: @entangle('showWizard').live }"
  x-cloak
  x-show="show"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0 translate-x-8"
  x-transition:enter-end="opacity-100 translate-x-0"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="opacity-100 translate-x-0"
  x-transition:leave-end="opacity-0 translate-x-8"
  @keydown.escape.window="$wire.dismiss()"
  class="fixed bottom-6 right-4 z-40 w-80 sm:w-88
         bg-white dark:bg-neutral-900
         rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10
         flex flex-col overflow-hidden"
  style="max-height: calc(100vh - 5rem)"
>

  {{-- Header --}}
  <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-neutral-100 dark:border-neutral-800 flex-shrink-0">
    <div>
      <h2 class="text-sm font-bold text-neutral-900 dark:text-neutral-50 tracking-tight">
        Guía de inicio
      </h2>
      <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">
        {{ $done }} de {{ $total }} completados
      </p>
    </div>
    <button wire:click="dismiss"
            class="p-1.5 rounded-lg text-neutral-400 hover:text-neutral-600 hover:bg-neutral-100
                   dark:hover:text-neutral-200 dark:hover:bg-neutral-800 transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  </div>

  {{-- Barra de progreso --}}
  <div class="px-5 py-3 flex-shrink-0">
    <div class="h-1.5 bg-neutral-100 dark:bg-neutral-800 rounded-full overflow-hidden">
      <div class="h-full rounded-full transition-all duration-700
                  {{ $allDone ? 'bg-emerald-500' : 'bg-indigo-500' }}"
           style="width: {{ $pct }}%"></div>
    </div>
  </div>

  {{-- Steps --}}
  <div class="flex-1 overflow-y-auto px-3 pb-3 space-y-1.5">

    @if($allDone)
      <div class="py-6 text-center px-4">
        <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mx-auto mb-3">
          <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">¡Todo listo!</p>
        <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Completaste la configuración inicial.</p>
      </div>
    @else
      @foreach($steps as $step)
        @php $icon = match($step['key']) {
          'products'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>',
          'payment_methods' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
          'first_sale'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
          'expenses'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>',
          'stock'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>',
          default           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        }; @endphp

        <a href="{{ $step['route'] }}"
           class="flex items-start gap-3 p-3 rounded-xl transition-colors group
                  {{ $step['done']
                     ? 'opacity-60 hover:opacity-80'
                     : 'hover:bg-indigo-50 dark:hover:bg-indigo-900/20 cursor-pointer' }}">

          {{-- Indicador --}}
          <div class="flex-shrink-0 mt-0.5 w-7 h-7 rounded-full flex items-center justify-center
                      {{ $step['done']
                         ? 'bg-emerald-100 dark:bg-emerald-900/40'
                         : 'bg-neutral-100 dark:bg-neutral-800 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/40' }}">
            @if($step['done'])
              <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
            @else
              <svg class="w-3.5 h-3.5 text-neutral-400 dark:text-neutral-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
              </svg>
            @endif
          </div>

          {{-- Texto --}}
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold leading-snug
                      {{ $step['done']
                         ? 'text-neutral-500 dark:text-neutral-500 line-through'
                         : 'text-neutral-800 dark:text-neutral-100' }}">
              {{ $step['title'] }}
            </p>
            @unless($step['done'])
              <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5 leading-snug">
                {{ $step['description'] }}
              </p>
            @endunless
          </div>

          {{-- Arrow --}}
          @unless($step['done'])
            <svg class="flex-shrink-0 w-4 h-4 mt-1 text-neutral-300 dark:text-neutral-600
                        group-hover:text-indigo-400 dark:group-hover:text-indigo-500 transition-colors"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6"/>
            </svg>
          @endunless
        </a>
      @endforeach
    @endif

  </div>

  {{-- Footer --}}
  <div class="px-5 py-3 border-t border-neutral-100 dark:border-neutral-800 flex-shrink-0 text-center">
    <button wire:click="disableForever"
            class="text-xs text-neutral-400 dark:text-neutral-500 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors underline-offset-2 hover:underline">
      No mostrar de nuevo
    </button>
  </div>

</div>
</div>
