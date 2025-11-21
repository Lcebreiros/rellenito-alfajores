<div class="bg-white dark:bg-neutral-950 rounded-3xl shadow-xl shadow-slate-200/20 dark:shadow-black/40 ring-1 ring-slate-200/50 dark:ring-neutral-800/60 overflow-hidden backdrop-blur-sm max-h-[100dvh] md:max-h-screen flex flex-col w-full max-w-full min-w-0">

  {{-- Header --}}
  <div class="px-6 py-4 bg-gradient-to-r from-slate-50 to-slate-100/50 dark:from-neutral-900 dark:to-neutral-900/80 border-b border-slate-200/60 dark:border-neutral-800/60">
    <h2 class="text-base font-semibold text-slate-900 dark:text-neutral-50 flex items-center gap-3">
      <div class="flex-shrink-0 w-2 h-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full animate-pulse"></div>
      <span wire:loading.remove>Pedido en curso</span>
      <span wire:loading class="text-indigo-600 dark:text-indigo-400 flex items-center gap-2" aria-live="polite">
        <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true" role="img">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"/>
        </svg>
        Actualizando pedido…
      </span>
    </h2>
  </div>

  {{-- Contenido principal (flex con min-h-0 para scroll interno) --}}
  <div class="flex-1 p-6 overflow-hidden flex flex-col min-h-0">
    {{-- Campo: Nombre de cliente --}}
<div class="mb-4">
  <label class="block text-xs font-medium text-slate-600 dark:text-neutral-300 mb-1">
    Cliente
  </label>
  <input
    type="text"
    wire:model.lazy="customerName"
    placeholder="Nombre del cliente"
    class="w-full rounded-xl border border-slate-300/70 dark:border-neutral-700 bg-white dark:bg-neutral-900
           px-3 py-2 text-sm text-slate-900 dark:text-neutral-100
           focus:outline-none focus:ring-2 focus:ring-indigo-500/70 focus:border-indigo-500/70"
  />
  <p class="mt-1 text-[11px] text-slate-500 dark:text-neutral-400">
    Si no existe, se creará automáticamente al guardar el pedido.
  </p>
</div>

    {{-- Área con scroll interno --}}
    <div class="flex-1 overflow-y-auto overscroll-contain min-h-0">
      @if(empty($items))
        {{-- Estado vacío --}}
        <div class="py-12 text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400
                      dark:from-neutral-800 dark:to-neutral-700 dark:text-neutral-300 shadow-inner">
            <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" role="img">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M16 16a2 2 0 100 4 2 2 0 000-4M7 16a2 2 0 100 4 2 2 0 000-4"/>
            </svg>
          </div>
          <h3 class="text-sm font-medium text-slate-900 dark:text-neutral-100 mb-1">Sin productos agregados</h3>
          <p class="text-xs text-slate-500 dark:text-neutral-400">Selecciona productos para comenzar tu pedido</p>
        </div>
      @else
        {{-- Mobile: lista --}}
        <div class="md:hidden space-y-2">
          @foreach($items as $it)
            <div class="group p-3 rounded-xl bg-white dark:bg-neutral-800/80 border border-slate-200/60 dark:border-neutral-800/60 hover:shadow-md hover:shadow-slate-200/20 dark:hover:shadow-black/20 transition-all duration-200">
              <div class="flex flex-wrap items-center gap-3">
                <div class="min-w-0 flex-1">
                  <div class="font-medium text-slate-900 dark:text-neutral-50 text-sm leading-tight truncate">{{ $it['name'] }}</div>
                  <div class="text-xs text-slate-500 dark:text-neutral-400">
                    $ {{ number_format($it['price'],2,',','.') }}
                  </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap justify-end w-full">
                  <div class="flex items-center bg-slate-50 dark:bg-neutral-700 rounded-lg border border-slate-200 dark:border-neutral-600">
                    <button wire:click="sub({{ $it['id'] }})" wire:loading.attr="disabled"
                      class="h-7 w-7 flex items-center justify-center rounded-l-lg hover:bg-slate-100 dark:hover:bg-neutral-600
                             text-slate-700 dark:text-neutral-200 transition-colors duration-200"
                      title="Restar 1" aria-label="Restar 1">
                      <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                      </svg>
                    </button>
                    <input
                      type="text"
                      inputmode="numeric"
                      pattern="[0-9]*"
                      value="{{ $it['qty'] }}"
                      wire:change="updateQty({{ $it['id'] }}, $event.target.value)"
                      wire:keydown.enter.prevent="$wire.updateQty({{ $it['id'] }}, $event.target.value)"
                      class="h-7 w-10 text-center bg-transparent text-slate-900 dark:text-neutral-100 text-xs font-semibold border-x border-slate-200 dark:border-neutral-500 focus:outline-none focus:ring-1 focus:ring-indigo-500/70"
                      aria-label="Cantidad"
                    />
                    <button wire:click="add({{ $it['id'] }})" wire:loading.attr="disabled"
                      class="h-7 w-7 flex items-center justify-center rounded-r-lg hover:bg-slate-100 dark:hover:bg-neutral-600
                             text-slate-700 dark:text-neutral-200 transition-colors duration-200"
                      title="Sumar 1" aria-label="Sumar 1">
                      <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12M6 12h12"/>
                      </svg>
                    </button>
                  </div>
                  
                  <button wire:click="remove({{ $it['id'] }})" wire:loading.attr="disabled"
                    class="h-7 w-7 flex items-center justify-center rounded-lg bg-rose-50 hover:bg-rose-100 
                           dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400 
                           transition-colors duration-200"
                    title="Eliminar" aria-label="Eliminar">
                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                  
                  <div class="text-sm font-semibold text-slate-900 dark:text-neutral-50 min-w-[4rem] text-right">
                    $ {{ number_format($it['subtotal'],2,',','.') }}
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- Desktop: tabla --}}
        <div class="hidden md:block bg-white dark:bg-neutral-800/50 rounded-xl border border-slate-200/60 dark:border-neutral-800/60 overflow-hidden">
          <table class="w-full">
            <thead class="bg-slate-50 dark:bg-neutral-900/80">
              <tr>
                <th class="py-3 px-4 text-left text-xs font-medium text-slate-500 dark:text-neutral-400 uppercase">Producto</th>
                <th class="py-3 px-3 text-center text-xs font-medium text-slate-500 dark:text-neutral-400 uppercase">Cant.</th>
                <th class="py-3 px-3 text-right text-xs font-medium text-slate-500 dark:text-neutral-400 uppercase">Subtotal</th>
                <th class="py-3 px-4 text-right text-xs font-medium text-slate-500 dark:text-neutral-400 uppercase">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-neutral-800/60">
              @foreach($items as $it)
                <tr class="group hover:bg-slate-50/50 dark:hover:bg-neutral-800/30 transition-colors">
                  <td class="py-3 px-4">
                    <div class="font-medium text-slate-900 dark:text-neutral-100 text-sm">{{ $it['name'] }}</div>
                    <div class="text-xs text-slate-500 dark:text-neutral-400">$ {{ number_format($it['price'],2,',','.') }}</div>
                  </td>
                  <td class="py-3 px-3 text-center">
                    <div class="inline-flex items-center gap-1 justify-center">
                      <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        value="{{ $it['qty'] }}"
                        wire:change="updateQty({{ $it['id'] }}, $event.target.value)"
                        wire:keydown.enter.prevent="$wire.updateQty({{ $it['id'] }}, $event.target.value)"
                        class="h-8 w-12 text-center rounded-lg border border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500/70"
                        aria-label="Cantidad"
                      />
                    </div>
                  </td>
                  <td class="py-3 px-3 text-right">
                    <span class="text-sm font-semibold text-slate-900 dark:text-neutral-100">$ {{ number_format($it['subtotal'],2,',','.') }}</span>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <div class="flex items-center gap-1 justify-end opacity-60 group-hover:opacity-100">
                      <button wire:click="sub({{ $it['id'] }})" wire:loading.attr="disabled"
                        class="h-6 w-6 flex items-center justify-center rounded bg-slate-100 dark:bg-neutral-700 hover:bg-slate-200 dark:hover:bg-neutral-600 text-slate-700 dark:text-neutral-200"
                        title="Restar 1" aria-label="Restar 1">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                        </svg>
                      </button>
                      <button wire:click="add({{ $it['id'] }})" wire:loading.attr="disabled"
                        class="h-6 w-6 flex items-center justify-center rounded bg-slate-100 dark:bg-neutral-700 hover:bg-slate-200 dark:hover:bg-neutral-600 text-slate-700 dark:text-neutral-200"
                        title="Sumar 1" aria-label="Sumar 1">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12M6 12h12"/>
                        </svg>
                      </button>
                      <button wire:click="remove({{ $it['id'] }})" wire:loading.attr="disabled"
                        class="ml-1 h-6 w-6 flex items-center justify-center rounded bg-rose-50 hover:bg-rose-100 
                               dark:bg-rose-900/20 dark:hover:bg-rose-900/40 text-rose-600 dark:text-rose-400"
                        title="Eliminar" aria-label="Eliminar">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Resumen de cantidad --}}
        <div class="mt-3 text-center">
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-neutral-900 dark:text-neutral-300">
            {{ count($items) }} {{ count($items) === 1 ? 'producto' : 'productos' }}
          </span>
        </div>
      @endif
    </div>

    {{-- Footer con total y acciones (fijo al fondo del componente) --}}
    <div class="flex-shrink-0 mt-4 space-y-4 border-t border-slate-200/60 dark:border-neutral-800/60 pt-4">


      {{-- Bloque de total --}}
      <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-50 to-slate-100/80 dark:from-neutral-900/80 dark:to-neutral-800/60 border border-slate-200/60 dark:border-neutral-800/60 shadow-sm">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-1.5 h-1.5 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full"></div>
            <span class="text-sm font-semibold text-slate-700 dark:text-neutral-200">Total del pedido</span>
          </div>
          <span class="text-xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 dark:from-neutral-100 dark:to-neutral-300 bg-clip-text text-transparent">
            $ {{ number_format($total,2,',','.') }}
          </span>
        </div>
      </div>

      {{-- Botones de acciones --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button wire:click="finalize" wire:loading.attr="disabled" @disabled(empty($items))
          class="group relative w-full rounded-xl py-3 text-sm font-semibold transition-all duration-300
                 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2
                 @if(empty($items))
                   bg-slate-200 text-slate-400 cursor-not-allowed dark:bg-neutral-700 dark:text-neutral-500
                 @else
                   bg-gradient-to-r from-indigo-600 to-purple-600 text-white hover:from-indigo-700 hover:to-purple-700 
                   hover:shadow-lg hover:shadow-indigo-500/25 dark:hover:shadow-indigo-400/20 hover:-translate-y-0.5 active:scale-95
                 @endif">
          <span wire:loading.remove wire:target="finalize">
            @if(empty($items)) 
              <span class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9"/>
                </svg>
                Sin productos
              </span>
            @else 
              <span class="flex items-center justify-center gap-2">
                @if($isScheduled)
                  <svg class="w-4 h-4 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  Agendar Pedido
                @else
                  <svg class="w-4 h-4 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                  </svg>
                  Finalizar Pedido
                @endif
              </span>
            @endif
          </span>
          <span wire:loading wire:target="finalize" class="flex items-center justify-center gap-2">
            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true" role="img">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"/>
            </svg>
            Procesando…
          </span>
        </button>

        <button wire:click="cancel" wire:loading.attr="disabled" @disabled(empty($items))
          class="group w-full rounded-xl py-3 text-sm font-semibold transition-all duration-300
                 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2
                 @if(empty($items))
                   bg-slate-100 text-slate-400 cursor-not-allowed dark:bg-neutral-800 dark:text-neutral-600
                 @else
                   bg-white text-slate-700 hover:bg-slate-50 hover:shadow-lg hover:shadow-slate-200/30 
                   dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700 dark:hover:shadow-black/20
                   border border-slate-200 dark:border-neutral-700 hover:-translate-y-0.5 active:scale-95
                 @endif">
          <span wire:loading.remove wire:target="cancel" class="flex items-center justify-center gap-2">
            <svg class="w-4 h-4 group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" role="img">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Cancelar
          </span>
          <span wire:loading wire:target="cancel" class="flex items-center justify-center gap-2">
            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true" role="img">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4z"/>
            </svg>
            Cancelando…
          </span>
        </button>
      </div>
    </div>
  </div>
</div>
