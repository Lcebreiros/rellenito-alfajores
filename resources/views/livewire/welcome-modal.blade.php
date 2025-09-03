<div>
  {{-- evita FOUC del modal antes de Alpine --}}
  <style>[x-cloak]{display:none!important}</style>

  <div
  class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
  {{-- 1) Estado local arranca en false para evitar FOUC --}}
  x-data="{ show: false }"
  {{-- 2) Semilla inicial desde el SSR (primer render) --}}
  x-init="
    $nextTick(() => {
      show = {{ $showModal ? 'true' : 'false' }};
      // opcional: bloquear scroll si abre
      if (show) document.documentElement.classList.add('overflow-y-hidden');
    });
    // 3) Mantener sincronía con Livewire después de hidratar
    $watch('show', v => document.documentElement.classList.toggle('overflow-y-hidden', v));
  "
  {{-- 4) Reflejar cambios posteriores de Livewire (next/skip/complete) --}}
  x-effect="show = $wire.showModal"
  x-show="show"
  x-transition.opacity
  @keydown.escape.window="$wire.skipWelcome()"
  @click.self="$wire.skipWelcome()"
  x-cloak
  {{-- opcional: fuerza rehidratación por usuario/ruta, sin impacto visual --}}
  wire:key="welcome-modal-{{ Auth::id() }}-{{ request()->path() }}"
>

    <div
      class="relative bg-white dark:bg-neutral-900 rounded-2xl shadow-xl ring-1 ring-neutral-200/70 dark:ring-neutral-800
             overflow-hidden flex flex-col"
      style="
        width: 560px; min-width: 560px; max-width: 560px;
        height: 460px; min-height: 460px; max-height: 460px;
      "
      x-show="show"
      x-transition:enter="transition ease-out duration-300 transform"
      x-transition:enter-start="opacity-0 scale-95 translate-y-8"
      x-transition:enter-end="opacity-100 scale-100 translate-y-0"
      x-transition:leave="transition ease-in duration-200 transform"
      x-transition:leave-start="opacity-100 scale-100 translate-y-0"
      x-transition:leave-end="opacity-0 scale-95 translate-y-8"
      role="dialog" aria-modal="true" aria-labelledby="welcome-title"
    >
      {{-- Cerrar --}}
      <button
        wire:click="skipWelcome"
        class="absolute right-3.5 top-3.5 z-10 rounded-full p-2 text-neutral-500 hover:text-neutral-900 hover:bg-neutral-100
                   dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800 transition"
        aria-label="Cerrar"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>

      {{-- Contenido (fijo) --}}
      <div class="flex-1 px-8 pt-10 pb-6 text-center overflow-y-auto overflow-x-hidden">
        {{-- Logo fijo (no altera layout) --}}
        <img src="{{ asset('images/Gestior.png') }}" alt="Gestior"
             class="mx-auto w-14 h-14 object-contain mb-4 select-none pointer-events-none" />

        {{-- Indicador de progreso --}}
        <div class="flex justify-center mb-6">
          <div class="flex space-x-1.5">
            @for($i = 1; $i <= $totalSteps; $i++)
              <div class="h-1.5 w-1.5 rounded-full transition-colors duration-300
                         {{ $i == $currentStep ? 'bg-blue-600' : 'bg-neutral-300 dark:bg-neutral-700' }}">
              </div>
            @endfor
          </div>
        </div>

        {{-- Marco de imagen de paso (tamaño fijo) --}}
        @php $img = $steps[$currentStep]['image'] ?? null; @endphp
        @if ($img)
          <div class="mt-2 mb-5 mx-auto rounded-lg overflow-hidden bg-neutral-100 dark:bg-neutral-800 p-4"
               style="width: 160px; height: 160px;">
            <img
              src="{{ asset('images/'.$img) }}"
              alt="{{ $steps[$currentStep]['title'] ?? '' }}"
              class="w-full h-full object-contain transition-opacity duration-300"
              draggable="false"
              x-transition:enter="transition ease-out duration-300"
              x-transition:enter-start="opacity-0"
              x-transition:enter-end="opacity-100"
            />
          </div>
        @endif

        {{-- Contenido del paso con animación --}}
        <div 
          x-data="{ currentStep: @entangle('currentStep') }"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0 transform translate-y-4"
          x-transition:enter-end="opacity-100 transform translate-y-0"
          wire:key="step-{{ $currentStep }}"
        >
          @if(!empty($steps[$currentStep]['title']))
            <h3 class="text-xl font-semibold text-neutral-900 dark:text-neutral-50 mb-3">
              {{ $steps[$currentStep]['title'] }}
            </h3>
          @endif

          @if(!empty($steps[$currentStep]['description']))
            <p class="text-sm leading-relaxed text-neutral-700 dark:text-neutral-300 px-4">
              {{ $steps[$currentStep]['description'] }}
            </p>
          @endif
        </div>
      </div>

      {{-- Footer fijo --}}
      <footer class="px-8 pb-7 text-center border-t border-neutral-100 dark:border-neutral-800 pt-5">
        <div class="flex items-center justify-between">
          {{-- Botón anterior --}}
          <button
            wire:click="previousStep"
            @if($currentStep == 1) disabled @endif
            class="px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 rounded-md
                   hover:bg-neutral-100 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-blue-500
                   disabled:opacity-50 disabled:cursor-not-allowed transition"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
          >
            Anterior
          </button>

          {{-- Botón principal --}}
          @if ($currentStep < $totalSteps)
            <button
              wire:click="nextStep"
              class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-md
                     hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                     transition transform hover:scale-105 duration-150"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 transform translate-y-2"
              x-transition:enter-end="opacity-100 transform translate-y-0"
            >
              Siguiente
            </button>
          @else
            <button
              wire:click="completeWelcome"
              class="px-6 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-md
                     hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2
                     transition transform hover:scale-105 duration-150"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 transform translate-y-2"
              x-transition:enter-end="opacity-100 transform translate-y-0"
            >
              Comenzar
            </button>
          @endif
        </div>

        <div class="mt-4">
          <button
            wire:click="skipWelcome"
            class="text-xs text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200 underline-offset-2 hover:underline transition"
          >
            No gracias, quiero verlo más tarde
          </button>
        </div>
      </footer>
    </div>
  </div>
</div>
