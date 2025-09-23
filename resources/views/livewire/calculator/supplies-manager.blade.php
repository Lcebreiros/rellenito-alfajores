<div class="space-y-6">
  {{-- Alta rápida (crea una compra y recalcula el insumo) --}}
  <div class="bg-white dark:bg-neutral-950 rounded-3xl shadow-xl shadow-slate-200/20 dark:shadow-black/40 ring-1 ring-slate-200/50 dark:ring-neutral-800/60 overflow-hidden backdrop-blur-sm transition-all duration-200">
    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50/50 dark:from-blue-900/80 dark:to-indigo-900/40 border-b border-slate-200/60 dark:border-neutral-800/60">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-neutral-50 flex items-center gap-3">
        <div class="flex-shrink-0 w-2 h-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full animate-pulse"></div>
        Agregar Insumo (Compra)
        <span class="ml-auto text-sm font-medium bg-gradient-to-r from-slate-600 to-slate-700 dark:from-neutral-300 dark:to-neutral-400 bg-clip-text text-transparent">
          Total: {{ number_format($this->count ?? 0, 0, ',', '.') }}
        </span>
      </h2>
    </div>

    <form wire:submit.prevent="quickStore" class="p-6 space-y-6">
      @if (session('ok'))
        <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-green-50/80 dark:from-emerald-900/40 dark:to-green-900/20 
                    border border-emerald-200/60 dark:border-emerald-800/40 shadow-sm">
          <div class="flex items-center gap-3">
            <div class="flex-shrink-0 w-1.5 h-1.5 bg-gradient-to-r from-emerald-500 to-green-600 rounded-full"></div>
            <span class="text-sm text-emerald-800 dark:text-emerald-200">{{ session('ok') }}</span>
          </div>
        </div>
      @endif

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="space-y-2">
          <label class="block text-xs font-medium text-slate-600 dark:text-neutral-300 mb-2">Nombre</label>
          <input wire:model.defer="name" required
                 class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                        px-4 py-3 text-sm text-slate-900 dark:text-neutral-100
                        placeholder:text-slate-400 dark:placeholder:text-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                        transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
          @error('name') 
            <div class="text-rose-600 dark:text-rose-300 text-xs mt-1 flex items-center gap-1">
              <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
              </svg>
              {{ $message }}
            </div> 
          @enderror
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-medium text-slate-600 dark:text-neutral-300 mb-2">Cantidad comprada</label>
          <input type="number" step="0.001" min="0" wire:model.defer="qty" required
                 class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                        px-4 py-3 text-sm text-slate-900 dark:text-neutral-100
                        placeholder:text-slate-400 dark:placeholder:text-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                        transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
          @error('qty') 
            <div class="text-rose-600 dark:text-rose-300 text-xs mt-1 flex items-center gap-1">
              <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
              </svg>
              {{ $message }}
            </div> 
          @enderror
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-medium text-slate-600 dark:text-neutral-300 mb-2">Unidad</label>
          <select wire:model.defer="unit" required
                  class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                         px-4 py-3 text-sm text-slate-900 dark:text-neutral-100
                         focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                         transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
            <optgroup label="Masa">
              <option value="g">g</option>
              <option value="kg">kg</option>
            </optgroup>
            <optgroup label="Volumen">
              <option value="ml">ml</option>
              <option value="l">l</option>
              <option value="cm3">cm3</option>
            </optgroup>
            <optgroup label="Unidad">
              <option value="u">u</option>
            </optgroup>
          </select>
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-medium text-slate-600 dark:text-neutral-300 mb-2">Precio total ($)</label>
          <input type="number" step="0.01" min="0" wire:model.defer="total_cost" required
                 class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                        px-4 py-3 text-sm text-slate-900 dark:text-neutral-100
                        placeholder:text-slate-400 dark:placeholder:text-neutral-400
                        focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                        transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
          @error('total_cost') 
            <div class="text-rose-600 dark:text-rose-300 text-xs mt-1 flex items-center gap-1">
              <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
              </svg>
              {{ $message }}
            </div> 
          @enderror
        </div>
      </div>

      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 pt-4 border-t border-slate-200/60 dark:border-neutral-800/60">
        <p class="text-[11px] text-slate-500 dark:text-neutral-400">
          Tip: el stock y el costo promedio se recalculan automáticamente a partir de todas las compras registradas.
        </p>
        
        <button class="group relative rounded-xl px-6 py-3 text-sm font-semibold transition-all duration-300
                       bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:from-indigo-700 hover:to-purple-700 
                       hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95
                       focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2
                       disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none" 
                wire:loading.attr="disabled">
          <span wire:loading.remove class="flex items-center gap-2">
            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12M6 12h12"/>
            </svg>
            Registrar compra
          </span>
          <span wire:loading class="flex items-center gap-2">
            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"/>
            </svg>
            Guardando…
          </span>
        </button>
      </div>
    </form>
  </div>

  {{-- Filtro y listado de insumos --}}
  <div class="bg-white dark:bg-neutral-950 rounded-3xl shadow-xl shadow-slate-200/20 dark:shadow-black/40 ring-1 ring-slate-200/50 dark:ring-neutral-800/60 overflow-hidden backdrop-blur-sm transition-all duration-200">
    <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-slate-100/50 dark:from-neutral-900 dark:to-neutral-900/80 border-b border-slate-200/60 dark:border-neutral-800/60">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-neutral-50 flex items-center gap-3">
          <div class="flex-shrink-0 w-2 h-2 bg-gradient-to-r from-slate-500 to-slate-600 rounded-full animate-pulse"></div>
          Insumos
        </h3>
        <input wire:model.debounce.400ms="search" placeholder="Buscar insumos…"
               class="w-full sm:w-72 rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                      px-4 py-2 text-sm text-slate-900 dark:text-neutral-100
                      placeholder:text-slate-400 dark:placeholder:text-neutral-400
                      focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                      transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
      </div>
    </div>

    <div class="p-6">
      <div class="space-y-4">
        @forelse($supplies as $s)
          @php
            // Si en el componente hiciste ->with(['purchases' => fn($q)=>$q->latest()->limit(1)])
            $last = $s->purchases->first();
          @endphp

          <div class="group p-4 rounded-2xl bg-white dark:bg-neutral-800/50 border border-slate-200/60 dark:border-neutral-800/60 
                      hover:shadow-md hover:shadow-slate-200/20 dark:hover:shadow-black/20 transition-all duration-200"
               wire:key="supply-{{ $s->id }}">

            {{-- Encabezado insumo + acciones --}}
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
              <div class="min-w-0 flex-1">
                <h4 class="font-medium text-slate-900 dark:text-neutral-100 text-base mb-2">{{ $s->name }}</h4>
                
                <div class="space-y-1">
                  <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                      <div class="w-1 h-1 bg-blue-500 rounded-full"></div>
                      <span class="text-slate-600 dark:text-neutral-400">Stock:</span>
                      <span class="font-medium text-slate-900 dark:text-neutral-100">
                        {{ number_format((float)($s->stock_base_qty ?? 0), 2, ',', '.') }} {{ $s->base_unit }}
                      </span>
                    </div>
                    <div class="flex items-center gap-2">
                      <div class="w-1 h-1 bg-green-500 rounded-full"></div>
                      <span class="text-slate-600 dark:text-neutral-400">$/base:</span>
                      <span class="font-medium text-slate-900 dark:text-neutral-100">
                        ${{ number_format((float)($s->avg_cost_per_base ?? 0), 6, ',', '.') }}
                      </span>
                    </div>
                  </div>
                  
                  @if($last)
                    <div class="px-3 py-1 rounded-lg bg-slate-50 dark:bg-neutral-800/50 border border-slate-200/60 dark:border-neutral-800/60">
                      <p class="text-xs text-slate-500 dark:text-neutral-400">
                        <span class="font-medium">Última compra:</span> 
                        {{ rtrim(rtrim(number_format((float)$last->qty, 6, ',', '.'), '0'), ',') }} {{ $last->unit }}
                        — Total: <span class="font-medium">${{ number_format((float)$last->total_cost, 2, ',', '.') }}</span>
                      </p>
                    </div>
                  @else
                    <div class="px-3 py-1 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/40">
                      <p class="text-xs text-amber-600 dark:text-amber-400">Aún sin compras registradas.</p>
                    </div>
                  @endif
                </div>
              </div>

              <div class="flex gap-2 shrink-0">
                <button wire:click="startEditBoth({{ $s->id }})" 
                        class="group relative rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-300
                               bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:from-indigo-700 hover:to-purple-700 
                               hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                  <span class="flex items-center gap-2">
                    <svg class="w-3 h-3 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                      <path d="m18.5 2.5 3 3L12 15l-4 1 1-4Z"/>
                    </svg>
                    Editar
                  </span>
                </button>
                <button wire:click="delete({{ $s->id }})"
                        class="group relative rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-300
                               bg-gradient-to-r from-rose-600 to-red-600 text-white hover:from-rose-700 hover:to-red-700 
                               hover:shadow-lg hover:shadow-rose-500/25 dark:hover:shadow-rose-400/20 hover:-translate-y-0.5 active:scale-95
                               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2">
                  <span class="flex items-center gap-2">
                    <svg class="w-3 h-3 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M3 6h18"/>
                      <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                      <path d="M8 6V4c0-1 1-2 2-2h4c0 1 1 2 2 2v2"/>
                    </svg>
                    Eliminar
                  </span>
                </button>
              </div>
            </div>

            {{-- Editor único (Nombre + precio total de la última compra si existe) --}}
            @if($editingId === $s->id)
              <div class="mt-4 p-4 rounded-2xl bg-slate-50 dark:bg-neutral-900/50 border border-slate-200/60 dark:border-neutral-800/60">
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-4">
                  {{-- Nombre --}}
                  <div class="sm:col-span-3 space-y-2">
                    <label class="block text-xs font-medium text-slate-600 dark:text-neutral-300">Nombre</label>
                    <input wire:model.defer="e_name"
                           class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                  px-3 py-2 text-sm text-slate-900 dark:text-neutral-100
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                                  transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
                    @error('e_name') 
                      <div class="text-rose-600 dark:text-rose-300 text-xs mt-1 flex items-center gap-1">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <circle cx="12" cy="12" r="10"/>
                          <line x1="15" y1="9" x2="9" y2="15"/>
                          <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        {{ $message }}
                      </div> 
                    @enderror
                  </div>

                  {{-- Precio total de la última compra (solo si existe una compra) --}}
                  @if($editingPurchaseId)
                    <div class="sm:col-span-2 space-y-2">
                      <label class="block text-xs font-medium text-slate-600 dark:text-neutral-300">Precio total última compra ($)</label>
                      <input type="number" step="0.01" min="0" wire:model.defer="ep_total_cost"
                             class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
                                    px-3 py-2 text-sm text-slate-900 dark:text-neutral-100
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70
                                    transition-all duration-200 hover:border-slate-400 dark:hover:border-neutral-600">
                      @error('ep_total_cost') 
                        <div class="text-rose-600 dark:text-rose-300 text-xs mt-1 flex items-center gap-1">
                          <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                          </svg>
                          {{ $message }}
                        </div> 
                      @enderror
                    </div>
                  @endif
                </div>

                <div class="flex gap-3 mt-4">
                  <button wire:click="saveBoth" wire:loading.attr="disabled" 
                          class="group relative rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-300
                                 bg-gradient-to-r from-emerald-600 to-green-600 text-white hover:from-emerald-700 hover:to-green-700 
                                 hover:shadow-lg hover:shadow-emerald-500/25 dark:hover:shadow-emerald-400/20 hover:-translate-y-0.5 active:scale-95
                                 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2
                                 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none">
                    <span wire:loading.remove wire:target="saveBoth" class="flex items-center gap-2">
                      <svg class="w-3 h-3 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                      </svg>
                      Guardar
                    </span>
                    <span wire:loading wire:target="saveBoth" class="flex items-center gap-2">
                      <svg class="h-3 w-3 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"/>
                      </svg>
                      Guardando…
                    </span>
                  </button>
                  <button type="button" wire:click="cancelEditBoth"
                          class="rounded-xl px-4 py-2 text-sm font-semibold
                                 bg-white text-slate-700 hover:bg-slate-50 hover:shadow-md hover:shadow-slate-200/30 
                                 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700 dark:hover:shadow-black/20
                                 border border-slate-200 dark:border-neutral-700 transition-all duration-200 hover:-translate-y-0.5 active:scale-95">
                    Cancelar
                  </button>
                </div>
              </div>
            @endif
          </div>
        @empty
          <div class="py-12 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400
                        dark:from-neutral-800 dark:to-neutral-700 dark:text-neutral-300 shadow-inner">
              <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
              </svg>
            </div>
            <h3 class="text-sm font-medium text-slate-900 dark:text-neutral-100 mb-1">No hay insumos cargados</h3>
            <p class="text-xs text-slate-500 dark:text-neutral-400">Agrega tu primer insumo usando el formulario superior</p>
          </div>
        @endforelse
      </div>
    </div>

    @if($supplies->hasPages())
      <div class="px-6 py-4 border-t border-slate-200/60 dark:border-neutral-800/60">
        {{ $supplies->links() }}
      </div>
    @endif
  </div>
</div>