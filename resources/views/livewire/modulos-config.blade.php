{{-- Configuración de Módulos --}}
<div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
            dark:border-neutral-800 dark:bg-neutral-900">
  <div class="flex items-center gap-3 mb-6">
    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-900/30">
      <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
      </svg>
    </div>
    <div>
      <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Módulos del Panel</h2>
      <p class="text-xs text-neutral-500 dark:text-neutral-400">Personalizá qué módulos ves en tu sidebar</p>
    </div>
  </div>

  @if (session('modulos-saved'))
    <div class="mb-4 p-3 rounded-md bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
      {{ session('modulos-saved') }}
    </div>
  @endif

  <div class="space-y-4">
    {{-- Info --}}
    <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-900/30">
      <div class="flex items-start gap-2">
        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div class="text-sm text-neutral-700 dark:text-neutral-300">
          Seleccioná solo los módulos que usás en tu negocio. Los módulos fijos (Dashboard, Pedidos, Métodos de Pago, Stock, Gastos, Configuración y Soporte) siempre estarán disponibles.
        </div>
      </div>
    </div>

    {{-- Grid de módulos opcionales --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
      @foreach($availableModules as $key => $label)
        <button type="button"
                wire:click="toggleModulo('{{ $key }}')"
                class="relative rounded-xl p-4 border-2 transition-all duration-200
                       hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-purple-500
                       @if(in_array($key, $modulosActivos))
                         bg-purple-50 border-purple-600 dark:bg-purple-900/20 dark:border-purple-500
                       @else
                         bg-white border-neutral-200 dark:bg-neutral-900 dark:border-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600
                       @endif">

          {{-- Check mark cuando está seleccionado --}}
          @if(in_array($key, $modulosActivos))
            <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-purple-600 dark:bg-purple-500
                      flex items-center justify-center text-white">
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          @endif

          {{-- Contenido --}}
          <div class="flex flex-col items-center gap-2 text-center">
            <span class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
              {{ $label }}
            </span>
            <span class="text-xs text-neutral-600 dark:text-neutral-400">
              @if(in_array($key, $modulosActivos))
                Visible
              @else
                Oculto
              @endif
            </span>
          </div>
        </button>
      @endforeach
    </div>

    {{-- Botón guardar --}}
    <div class="flex items-center justify-between pt-4 border-t border-neutral-200 dark:border-neutral-700">
      <div class="text-sm text-neutral-600 dark:text-neutral-400">
        <span class="font-semibold">{{ count($modulosActivos) }}</span> de {{ count($availableModules) }} módulos seleccionados
      </div>

      <button wire:click="guardar"
              wire:loading.attr="disabled"
              wire:loading.class="opacity-50 cursor-not-allowed"
              class="group px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700
                     text-white font-medium text-sm transition-all duration-200 shadow-sm hover:shadow-md
                     focus:ring-2 focus:ring-purple-500/20 disabled:opacity-60 disabled:cursor-not-allowed
                     dark:bg-purple-500 dark:hover:bg-purple-600">
        <span wire:loading.remove class="flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Guardar cambios
        </span>
        <span wire:loading class="flex items-center gap-2">
          <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Guardando...
        </span>
      </button>
    </div>
  </div>
</div>
