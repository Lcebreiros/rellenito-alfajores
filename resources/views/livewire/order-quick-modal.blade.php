<div>
  {{-- Botón abrir (minimal) --}}
  <div class="mb-4">
    <button type="button"
            wire:click="showModal"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-indigo-600 text-white font-medium shadow-sm
                   hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
      </svg>
      <span class="text-sm">Nuevo pedido</span>
    </button>
  </div>


  {{-- Modal --}}
  @if($open)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         wire:keydown.escape="hideModal"
         x-data="{ show: false }"
         x-init="setTimeout(() => show = true, 50)"
         x-show="show"
         x-transition:enter="transition-opacity duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

      {{-- Overlay --}}
      <div class="absolute inset-0 bg-black/50" @click="$wire.hideModal()"></div>

      {{-- Modal Content --}}
      <form wire:submit.prevent="save"
            class="relative w-full max-w-6xl max-h-[90vh] bg-white dark:bg-gray-900 
                   border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl
                   flex flex-col overflow-hidden"
            x-transition:enter="transition-all duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <header class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
              <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Nuevo Pedido</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Crear pedido</p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            {{-- Fecha en desktop --}}
            <div class="hidden md:block">
              <input type="datetime-local"
                     wire:model.live="orderDate"
                     class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                            focus:outline-none focus:ring-2 focus:ring-gray-500" />
            </div>

            <button type="button"
                    wire:click="hideModal"
                    class="w-8 h-8 rounded hover:bg-gray-100 dark:hover:bg-gray-800 
                           flex items-center justify-center text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </header>

        {{-- Fecha en móvil --}}
        <div class="md:hidden px-6 py-3 border-b border-gray-200 dark:border-gray-700">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Fecha y hora del pedido
          </label>
          <input type="datetime-local"
                 wire:model.live="orderDate"
                 class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                        bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                        focus:outline-none focus:ring-2 focus:ring-gray-500" />
          @error('orderDate')
            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Content --}}
        <main class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-0 overflow-hidden min-h-0">
          {{-- Productos --}}
          <section class="flex flex-col min-h-0 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900 dark:text-gray-100">Productos</h4>
                <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded">
                  {{ $products->total() }}
                </span>
              </div>

              {{-- Buscador --}}
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Buscar productos..."
                       class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                              bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                              placeholder:text-gray-400 dark:placeholder:text-gray-500
                              focus:outline-none focus:ring-2 focus:ring-gray-500">
                @if($search)
                  <button type="button" 
                          wire:click="$set('search', '')"
                          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                @endif
              </div>
            </div>

            {{-- Lista productos --}}
            <div class="flex-1 overflow-y-auto p-6">
              @if($products->isEmpty())
                <div class="flex flex-col items-center justify-center h-full text-center text-gray-500 dark:text-gray-400">
                  <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7"/>
                    </svg>
                  </div>
                  <p class="font-medium">No hay productos</p>
                  <p class="text-sm opacity-75">Intentá con otros términos</p>
                </div>
              @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  @foreach($products as $p)
                    <div wire:key="p-{{ $p->id }}" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-sm transition-shadow">
                      <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $p->name }}</h5>
                      <div class="flex items-center justify-between mb-3">
                        <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                          ${{ number_format((float)($p->price ?? 0), 2, ',', '.') }}
                        </span>
                        @if($p->stock ?? false)
                          <span class="text-xs text-gray-500 dark:text-gray-400">Stock: {{ $p->stock }}</span>
                        @endif
                      </div>
                      <button type="button"
                              wire:click="addProduct({{ $p->id }})"
                              wire:loading.attr="disabled"
                              wire:target="addProduct({{ $p->id }})"
                              class="w-full px-3 py-2 text-sm font-medium bg-gray-900 dark:bg-gray-700 text-white rounded
                                     hover:bg-gray-800 dark:hover:bg-gray-600 disabled:opacity-50 transition-colors">
                        <span wire:loading.remove wire:target="addProduct({{ $p->id }})">Agregar</span>
                        <span wire:loading wire:target="addProduct({{ $p->id }})">Agregando...</span>
                      </button>
                    </div>
                  @endforeach
                </div>

                @if($products->hasPages())
                  <div class="mt-6">
                    {{ $products->onEachSide(1)->links() }}
                  </div>
                @endif
              @endif
            </div>
          </section>

          {{-- Info y Carrito --}}
          <section class="flex flex-col min-h-0">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h4 class="font-medium text-gray-900 dark:text-gray-100">Información del pedido</h4>
            </div>

            <div class="flex-1 overflow-y-auto p-6 space-y-6">
              {{-- Cliente --}}
              <div>
                <div class="flex items-center justify-between mb-4">
                  <label class="font-medium text-gray-900 dark:text-gray-100">
                    Cliente <span class="text-sm text-gray-500 dark:text-gray-400 font-normal">(opcional)</span>
                  </label>
                  
                  <div class="flex rounded-md border border-gray-200 dark:border-gray-700">
                    <button type="button"
                            wire:click="toggleClientForm"
                            class="px-3 py-1 text-sm {{ !$showClientForm ? 'bg-gray-900 text-white' : 'text-gray-600 dark:text-gray-400' }} rounded-l-md">
                      Buscar
                    </button>
                    <button type="button"
                            wire:click="toggleClientForm"
                            class="px-3 py-1 text-sm {{ $showClientForm ? 'bg-gray-900 text-white' : 'text-gray-600 dark:text-gray-400' }} rounded-r-md border-l border-gray-200 dark:border-gray-700">
                      Crear
                    </button>
                  </div>
                </div>

                @if($showClientForm)
                  <div class="space-y-3">
                    <input type="text" 
                           wire:model="newClientName" 
                           placeholder="Nombre del cliente *"
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                  placeholder:text-gray-400 dark:placeholder:text-gray-500
                                  focus:outline-none focus:ring-2 focus:ring-gray-500" />
                    @error('newClientName')
                      <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                    @enderror

                    <div class="grid grid-cols-2 gap-3">
                      <input type="email" 
                             wire:model="newClientEmail" 
                             placeholder="Email"
                             class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                    placeholder:text-gray-400 dark:placeholder:text-gray-500
                                    focus:outline-none focus:ring-2 focus:ring-gray-500" />
                      <input type="tel" 
                             wire:model="newClientPhone" 
                             placeholder="Teléfono"
                             class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                    placeholder:text-gray-400 dark:placeholder:text-gray-500
                                    focus:outline-none focus:ring-2 focus:ring-gray-500" />
                    </div>
                  </div>
                @else
                  <div class="relative">
                    <input type="text"
                           wire:model.live.debounce.300ms="clientSearch"
                           placeholder="Buscar cliente..."
                           class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                                  bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                  placeholder:text-gray-400 dark:placeholder:text-gray-500
                                  focus:outline-none focus:ring-2 focus:ring-gray-500" />

                    @if($this->clients->count() > 0)
                      <div class="absolute top-full left-0 right-0 z-20 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-48 overflow-y-auto">
                        @foreach($this->clients as $client)
                          <button type="button"
                                  wire:click="selectClient({{ $client->id }})"
                                  wire:key="c-{{ $client->id }}"
                                  class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700
                                         {{ $client_id == $client->id ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $client->name }}</div>
                            @if($client->email || $client->phone)
                              <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ collect([$client->email, $client->phone])->filter()->implode(' • ') }}
                              </div>
                            @endif
                          </button>
                        @endforeach
                      </div>
                    @endif
                  </div>
                @endif
              </div>

              {{-- Carrito --}}
              <div>
                <div class="flex items-center justify-between mb-4">
                  <h5 class="font-medium text-gray-900 dark:text-gray-100">
                    Carrito
                    <span class="ml-2 px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded text-xs">
                      {{ count($items) }}
                    </span>
                  </h5>
                  @if(count($items))
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                      ${{ number_format($this->total, 2, ',', '.') }}
                    </div>
                  @endif
                </div>

                @if(count($items) === 0)
                  <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4"/>
                      </svg>
                    </div>
                    <p class="font-medium">Carrito vacío</p>
                    <p class="text-sm">Agregá productos</p>
                  </div>
                @else
                  <div class="space-y-3">
                    @foreach($items as $index => $item)
                      <div wire:key="i-{{ $item['product_id'] }}-{{ $index }}" 
                           class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex-1 min-w-0">
                          <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item['name'] }}</div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">
                            ${{ number_format($item['price'], 2, ',', '.') }} c/u
                          </div>
                          <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            ${{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}
                          </div>
                        </div>

                        <div class="flex items-center gap-2">
                          <button type="button"
                                  wire:click="updateQuantity({{ $index }}, {{ max(0, $item['quantity'] - 1) }})"
                                  class="w-8 h-8 rounded border border-gray-200 dark:border-gray-700 
                                         flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-800">
                            −
                          </button>

                          <input type="number" min="0"
                                 value="{{ $item['quantity'] }}"
                                 wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                 class="w-16 text-center text-sm border border-gray-200 dark:border-gray-700 rounded
                                        bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100" />

                          <button type="button"
                                  wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                  class="w-8 h-8 rounded border border-gray-200 dark:border-gray-700 
                                         flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-800">
                            +
                          </button>

                          <button type="button"
                                  wire:click="removeItem({{ $index }})"
                                  class="w-8 h-8 rounded text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 
                                         flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                          </button>
                        </div>
                      </div>
                    @endforeach
                  </div>
                @endif
              </div>
            </div>
          </section>
        </main>

        {{-- Footer --}}
        <footer class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <div>
            @if(count($items))
              <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ count($items) }} producto{{ count($items) !== 1 ? 's' : '' }} • 
                {{ array_sum(array_column($items, 'quantity')) }} unidad{{ array_sum(array_column($items, 'quantity')) !== 1 ? 'es' : '' }}
              </div>
              <div class="font-semibold text-lg text-gray-900 dark:text-gray-100">
                Total: ${{ number_format($this->total, 2, ',', '.') }}
              </div>
            @else
              <div class="text-sm text-gray-500 dark:text-gray-400">
                Agregá productos para continuar
              </div>
            @endif
          </div>

          <div class="flex gap-3">
            <button type="button"
                    wire:click="hideModal"
                    class="px-4 py-2 text-sm font-medium border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded
                           hover:bg-gray-50 dark:hover:bg-gray-800">
              Cancelar
            </button>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    @disabled(!count($items))
                    class="px-4 py-2 text-sm font-medium bg-gray-900 dark:bg-gray-700 text-white rounded
                           hover:bg-gray-800 dark:hover:bg-gray-600 disabled:opacity-50">
              <span wire:loading.remove wire:target="save">Guardar Pedido</span>
              <span wire:loading wire:target="save">Guardando...</span>
            </button>
          </div>
        </footer>
      </form>
    </div>
  @endif

  <style>
    /* Input datetime styling for dark mode */
    input[type="datetime-local"] {
      color-scheme: light dark;
    }
    
    .dark input[type="datetime-local"] {
      color-scheme: dark;
    }
    
    /* Ensure text is visible in all inputs */
    input {
      color: inherit !important;
    }
    
    .dark input {
      color: rgb(243 244 246) !important;
    }
  </style>
</div>