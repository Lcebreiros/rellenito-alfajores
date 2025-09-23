<div class="space-y-6">
  <div class="bg-white dark:bg-neutral-950 rounded-3xl shadow-xl shadow-slate-200/20 dark:shadow-black/40 ring-1 ring-slate-200/50 dark:ring-neutral-800/60 overflow-hidden backdrop-blur-sm transition-all duration-200">

    {{-- Header --}}
    <div class="px-6 py-4 bg-gradient-to-r from-purple-50 to-fuchsia-50/50 dark:from-purple-900/80 dark:to-fuchsia-900/40 border-b border-slate-200/60 dark:border-neutral-800/60">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-neutral-50 flex items-center gap-3">
          <div class="flex-shrink-0 w-2 h-2 bg-gradient-to-r from-purple-500 to-fuchsia-600 rounded-full animate-pulse"></div>
          Análisis Guardados
        </h2>

        <div class="w-full md:w-auto">
          <div class="p-1 bg-white/90 dark:bg-neutral-800/50 backdrop-blur-sm rounded-xl border border-slate-200/60 dark:border-neutral-700/60 shadow-sm">
            <select wire:model="filterProductId"
                    class="w-full md:w-[240px] bg-transparent rounded-lg px-3 py-2 text-sm
                           text-slate-900 dark:text-neutral-100 placeholder:text-slate-400 dark:placeholder:text-neutral-400
                           focus:outline-none focus:ring-2 focus:ring-purple-500/70 focus:border-purple-500/70
                           border-0 transition-all duration-200">
              <option value="">Todos los productos</option>
              @foreach($products as $p)
                <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    {{-- Body --}}
    <div class="p-6">
      @if (session('ok'))
        <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-green-50/80 dark:from-emerald-900/40 dark:to-green-900/20 
                    border border-emerald-200/60 dark:border-emerald-800/40 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-1.5 h-1.5 bg-gradient-to-r from-emerald-500 to-green-600 rounded-full"></div>
            <span class="text-sm text-emerald-800 dark:text-emerald-200">{{ session('ok') }}</span>
          </div>
        </div>
      @endif

      {{-- Dashboard resumen --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-blue-100/80 dark:from-blue-900/40 dark:to-blue-800/20 
                    border border-blue-200/60 dark:border-blue-800/40 shadow-sm">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-1.5 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div>
            <span class="text-sm font-semibold text-blue-700 dark:text-blue-200">Total de análisis</span>
          </div>

          {{-- VALOR: color sólido para contraste --}}
          <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
            {{ number_format($totalAnalyses ?? 0, 0, ',', '.') }}
          </div>
        </div>

        <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-green-100/80 dark:from-emerald-900/40 dark:to-green-800/20 
                    border border-emerald-200/60 dark:border-emerald-800/40 shadow-sm">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-1.5 bg-gradient-to-r from-emerald-500 to-green-600 rounded-full animate-pulse"></div>
            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-200">Producción total</span>
          </div>

          {{-- VALOR: color sólido para contraste --}}
          <div class="text-2xl font-bold text-emerald-900 dark:text-emerald-100">
            {{ number_format($totalProduction ?? 0, 0, ',', '.') }} unidades
          </div>
        </div>

        <div class="p-4 rounded-2xl bg-gradient-to-r from-purple-50 to-purple-100/80 dark:from-purple-900/40 dark:to-purple-800/20 
                    border border-purple-200/60 dark:border-purple-800/40 shadow-sm">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-1.5 h-1.5 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full animate-pulse"></div>
            <span class="text-sm font-semibold text-purple-700 dark:text-purple-200">Gasto total</span>
          </div>

          {{-- VALOR: color sólido para contraste --}}
          <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
            ${{ number_format($totalSpend ?? 0, 2, ',', '.') }}
          </div>
        </div>
      </div>

      @if (empty($saved))
        <div class="py-12 text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400
                      dark:from-neutral-800 dark:to-neutral-700 dark:text-neutral-300 shadow-inner">
            <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
          </div>
          <h3 class="text-sm font-medium text-slate-900 dark:text-neutral-100 mb-1">No hay análisis guardados</h3>
          <p class="text-xs text-slate-500 dark:text-neutral-400">Los análisis que guardes aparecerán aquí para poder reutilizarlos</p>
        </div>
      @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          @foreach($saved as $s)
            <div class="group p-6 rounded-2xl bg-white dark:bg-neutral-800/50 border border-slate-200/60 dark:border-neutral-800/60 
                        hover:shadow-md hover:shadow-slate-200/20 dark:hover:shadow-black/20 transition-all duration-200">
              
              <div class="flex items-start justify-between mb-4 gap-3">
                <div class="min-w-0 flex-1">
                  <h4 class="font-semibold text-slate-900 dark:text-neutral-100 text-base mb-1 truncate">
                    {{ $s['product_name'] ?? 'Sin producto' }}
                  </h4>
                  <p class="text-xs text-slate-500 dark:text-neutral-400">
                    {{ \Carbon\Carbon::parse($s['created_at'])->format('d M Y H:i') }}
                  </p>
                </div>

                @php $fromRecipe = ($s['source'] ?? '') === 'recipe'; @endphp
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium shrink-0
                             {{ $fromRecipe
                                ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/40'
                                : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/40' }}">
                  <div class="w-1 h-1 rounded-full mr-1.5
                              {{ $fromRecipe ? 'bg-indigo-500' : 'bg-amber-500' }}"></div>
                  {{ $fromRecipe ? 'Receta' : 'Rápido' }}
                </span>
              </div>

              <div class="space-y-3">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-neutral-900/50 border border-slate-200/60 dark:border-neutral-800/60">
                  <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-600 dark:text-neutral-400">Costo por unidad</span>
                    {{-- VALOR: color sólido para contraste --}}
                    <span class="font-semibold text-slate-900 dark:text-neutral-100">
                      ${{ number_format($s['unit_total'] ?? 0, 2, ',', '.') }}
                    </span>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div class="text-center p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200/60 dark:border-blue-800/40">
                    <div class="text-xs text-blue-600 dark:text-blue-400 mb-1">Batch</div>
                    {{-- VALOR: color sólido para contraste --}}
                    <div class="font-medium text-blue-900 dark:text-blue-100 text-sm">
                      ${{ number_format($s['batch_total'] ?? 0, 2, ',', '.') }}
                    </div>
                  </div>

                  <div class="text-center p-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200/60 dark:border-emerald-800/40">
                    <div class="text-xs text-emerald-600 dark:text-emerald-400 mb-1">Rendimiento</div>
                    {{-- VALOR: color sólido para contraste --}}
                    <div class="font-medium text-emerald-900 dark:text-emerald-100 text-sm">
                      {{ (int) ($s['yield_units'] ?? 0) }} u
                    </div>
                  </div>
                </div>
              </div>

              @php $lines = collect($s['lines'] ?? [])->take(3); @endphp
              @if($lines->isNotEmpty())
                <div class="mt-4 pt-4 border-t border-slate-200/60 dark:border-neutral-800/60">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="w-1 h-1 bg-slate-400 dark:bg-neutral-500 rounded-full"></div>
                    <span class="text-xs font-medium text-slate-600 dark:text-neutral-400">Ingredientes principales</span>
                  </div>
                  <div class="space-y-1">
                    @foreach($lines as $ln)
                      <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500 dark:text-neutral-400 truncate mr-2">{{ $ln['name'] ?? '—' }}</span>
                        {{-- VALOR: color sólido para contraste --}}
                        <span class="font-medium text-slate-700 dark:text-neutral-300">
                          ${{ number_format((float) ($ln['per_unit_cost'] ?? 0), 2, ',', '.') }}
                        </span>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif

              <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-slate-200/60 dark:border-neutral-800/60">
                <button wire:click="useSaved({{ (int) ($s['id'] ?? 0) }})"
                        class="group relative rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-300
                               bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:from-indigo-700 hover:to-purple-700 
                               hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                  <span class="flex items-center gap-2">
                    <svg class="w-3 h-3 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                      <polyline points="17,8 12,3 7,8"/>
                      <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    Usar análisis
                  </span>
                </button>

                <button x-data
                        x-on:click.prevent="confirm('¿Eliminar este análisis?') && $wire.delete({{ (int) ($s['id'] ?? 0) }})"
                        class="group relative rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-300
                               bg-gradient-to-r from-rose-600 to-red-600 text-white hover:from-rose-700 hover:to-red-700 
                               hover:shadow-lg hover:shadow-rose-500/25 dark:hover:shadow-rose-400/20 hover:-translate-y-0.5 active:scale-95
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2">
                  <span class="flex items-center gap-2">
                    <svg class="w-3 h-3 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18"/>
                      <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                      <path d="M8 6V4c0-1 1-2 2-2h4c0 1 1 2 2 2v2"/>
                      <line x1="10" y1="11" x2="10" y2="17"/>
                      <line x1="14" y1="11" x2="14" y2="17"/>
                    </svg>
                    Eliminar
                  </span>
                </button>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
