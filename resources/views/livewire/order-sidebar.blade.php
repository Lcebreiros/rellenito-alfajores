<div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200/70 overflow-hidden">
  <div class="px-3 py-2 border-b border-slate-200/70">
    <h2 class="text-sm font-semibold text-slate-800">
      <span wire:loading.remove>Pedido en curso</span>
      <span wire:loading class="text-indigo-600">Actualizando pedido...</span>
    </h2>
  </div>

  <div class="p-3">
    <div style="max-height: calc(100svh - 12rem); overflow-y:auto;">
      @if(empty($items))
        <div class="py-6 text-center text-slate-500 text-sm">
          <div>Sin ítems agregados</div>
          <div class="text-xs mt-1">Haz clic en un producto para agregarlo</div>
        </div>
      @else
        <table class="w-full text-xs">
          <thead class="text-slate-500 uppercase tracking-wide text-[11px]">
            <tr class="border-b border-slate-100">
              <th class="py-2 text-left">Producto</th>
              <th class="py-2 w-14 text-center">Cant.</th>
              <th class="py-2 w-20 text-right">Subt.</th>
              <th class="py-2 w-8"></th>
            </tr>
          </thead>
          <tbody>
          @foreach($items as $it)
            <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-25 transition-colors">
              <td class="py-2 pr-2 align-top">
                <div class="truncate text-slate-800 font-medium">{{ $it['name'] }}</div>
                <div class="text-[11px] text-slate-500">$ {{ number_format($it['price'],2,',','.') }}</div>
              </td>
              <td class="py-2 text-center align-top">
                <span class="inline-flex items-center justify-center w-6 h-5 bg-slate-100 rounded text-slate-700 font-medium">
                  {{ $it['qty'] }}
                </span>
              </td>
              <td class="py-2 text-right align-top font-medium text-slate-800">
                $ {{ number_format($it['subtotal'],2,',','.') }}
              </td>
              <td class="py-2 text-right align-top">
                <div class="flex items-center gap-0.5 justify-end">
                  <button 
                    wire:click="sub({{ $it['id'] }})" 
                    wire:loading.attr="disabled"
                    class="w-6 h-6 flex items-center justify-center rounded border border-slate-300 hover:bg-slate-50 hover:border-slate-400 transition-colors disabled:opacity-50"
                    title="Restar 1">
                    −
                  </button>
                  <button 
                    wire:click="add({{ $it['id'] }})" 
                    wire:loading.attr="disabled"
                    class="w-6 h-6 flex items-center justify-center rounded border border-slate-300 hover:bg-slate-50 hover:border-slate-400 transition-colors disabled:opacity-50"
                    title="Sumar 1">
                    +
                  </button>
                  <button 
                    wire:click="remove({{ $it['id'] }})" 
                    wire:loading.attr="disabled"
                    class="w-6 h-6 flex items-center justify-center rounded border border-rose-200 text-rose-600 hover:bg-rose-50 hover:border-rose-300 transition-colors disabled:opacity-50"
                    title="Eliminar">
                    ×
                  </button>
                </div>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
        
        {{-- Mostrar total de items --}}
        <div class="mt-2 text-center text-[11px] text-slate-400">
          {{ count($items) }} {{ count($items) === 1 ? 'producto' : 'productos' }} en el pedido
        </div>
      @endif
    </div>

    {{-- Total section --}}
    <div class="mt-3 p-2 bg-slate-50 rounded-lg">
      <div class="flex items-center justify-between text-sm">
        <span class="font-medium text-slate-700">Total del pedido</span>
        <span class="font-bold text-lg text-slate-900">$ {{ number_format($total,2,',','.') }}</span>
      </div>
    </div>

    {{-- Action buttons --}}
    <div class="mt-3 grid grid-cols-2 gap-2">
      <button 
        wire:click="finalize"
        wire:loading.attr="disabled"
        @disabled(empty($items))
        class="w-full rounded-lg py-2.5 text-sm font-medium transition-all duration-200
               @if(empty($items))
                 bg-slate-200 text-slate-400 cursor-not-allowed
               @else
                 bg-indigo-600 text-white hover:bg-indigo-700 hover:shadow-md active:scale-95
               @endif
               disabled:opacity-60">
        <span wire:loading.remove wire:target="finalize">
          @if(empty($items))
            Sin productos
          @else
            Finalizar Pedido
          @endif
        </span>
        <span wire:loading wire:target="finalize" class="flex items-center justify-center gap-2">
          <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Procesando…
        </span>
      </button>

      <button 
        wire:click="cancel"
        wire:loading.attr="disabled"
        @disabled(empty($items))
        class="w-full rounded-lg py-2.5 text-sm font-medium transition-all duration-200
               @if(empty($items))
                 bg-slate-100 text-slate-400 cursor-not-allowed
               @else
                 bg-slate-100 text-slate-700 hover:bg-slate-200 hover:shadow-sm active:scale-95
               @endif
               disabled:opacity-60">
        <span wire:loading.remove wire:target="cancel">Cancelar</span>
        <span wire:loading wire:target="cancel">Cancelando…</span>
      </button>
    </div>
  </div>
</div>