<div>
  {{-- Botón abrir (minimal) - sin contenedor que afecte layout --}}
<button type="button"
        wire:click="showModal"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-white hover:bg-indigo-700 transition-colors">
  <i class="fas fa-plus-circle mr-2"></i>
  Nueva Venta
</button>



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
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Nueva Venta</h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">Crear venta</p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            {{-- Fecha en desktop --}}
            <div class="hidden md:flex items-center gap-2">
              <input type="datetime-local"
                     wire:model.live="orderDate"
                     id="orderDateDesktop"
                     class="px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                            bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                            focus:outline-none focus:ring-2 focus:ring-gray-500" />
              <!-- Botón personalizado de calendario fuera del input -->
              <button type="button" 
                      onclick="document.getElementById('orderDateDesktop').showPicker()"
                      class="flex-shrink-0 w-8 h-8 border border-gray-200 dark:border-gray-700 rounded
                             bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700
                             flex items-center justify-center transition-all duration-200 
                             hover:scale-110 focus:outline-none focus:ring-2 focus:ring-gray-500">
                <img src="{{ asset('images/calendario.png') }}" alt="Calendario" class="w-4 h-4 dark-calendar-icon">
              </button>
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
            Fecha y hora de la venta
          </label>
          <div class="flex items-center gap-2">
            <input type="datetime-local"
                   wire:model.live="orderDate"
                   id="orderDateMobile"
                   class="flex-1 px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                          bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                          focus:outline-none focus:ring-2 focus:ring-gray-500" />
            <!-- Botón personalizado de calendario móvil fuera del input -->
            <button type="button" 
                    onclick="document.getElementById('orderDateMobile').showPicker()"
                    class="flex-shrink-0 w-8 h-8 border border-gray-200 dark:border-gray-700 rounded
                           bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700
                           flex items-center justify-center transition-all duration-200 
                           hover:scale-110 focus:outline-none focus:ring-2 focus:ring-gray-500">
              <img src="{{ asset('images/calendario.png') }}" alt="Calendario" class="w-4 h-4 dark-calendar-icon">
            </button>
          </div>
          @error('orderDate')
            <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Content --}}
        <main class="flex-1 grid grid-cols-1 lg:grid-cols-2 gap-0 overflow-hidden min-h-0">
          {{-- Productos/Servicios --}}
          <section class="flex flex-col min-h-0 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              {{-- Tabs --}}
              <div class="flex items-center gap-2 mb-3">
                <button type="button"
                        wire:click="setTab('products')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                               {{ $currentTab === 'products' ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                  <i class="fas fa-box mr-2"></i>Productos
                  <span class="ml-2 px-2 py-0.5 bg-white/20 rounded text-xs">{{ $products->total() }}</span>
                </button>
                <button type="button"
                        wire:click="setTab('services')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors
                               {{ $currentTab === 'services' ? 'bg-indigo-600 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                  <i class="fas fa-concierge-bell mr-2"></i>Servicios
                  <span class="ml-2 px-2 py-0.5 bg-white/20 rounded text-xs">{{ $services->total() }}</span>
                </button>
              </div>

              {{-- Buscador --}}
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       wire:model.live.debounce.400ms="search"
                       placeholder="Buscar {{ $currentTab === 'products' ? 'productos' : 'servicios' }}..."
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
            @if($currentTab === 'products')
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
                              class="w-full px-3 py-2 text-sm font-medium bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded
                                     hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors">
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
            @endif

            {{-- Lista servicios --}}
            @if($currentTab === 'services')
              <div class="flex-1 overflow-y-auto p-6">
                @if($services->isEmpty())
                  <div class="flex flex-col items-center justify-center h-full text-center text-gray-500 dark:text-gray-400">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center mb-4">
                      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                      </svg>
                    </div>
                    <p class="font-medium">No hay servicios</p>
                    <p class="text-sm opacity-75">Intentá con otros términos</p>
                  </div>
                @else
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($services as $s)
                      <div wire:key="s-{{ $s->id }}" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-sm transition-shadow">
                        <h5 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ $s->name }}</h5>
                        @if($s->description)
                          <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">{{ $s->description }}</p>
                        @endif
                        <div class="flex items-center justify-between mb-3">
                          <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            ${{ number_format((float)($s->price ?? 0), 2, ',', '.') }}
                          </span>
                        </div>
                        <button type="button"
                                wire:click="addService({{ $s->id }})"
                                wire:loading.attr="disabled"
                                wire:target="addService({{ $s->id }})"
                                class="w-full px-3 py-2 text-sm font-medium bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded
                                       hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-colors">
                          <span wire:loading.remove wire:target="addService({{ $s->id }})">Agregar</span>
                          <span wire:loading wire:target="addService({{ $s->id }})">Agregando...</span>
                        </button>
                      </div>
                    @endforeach
                  </div>

                  @if($services->hasPages())
                    <div class="mt-6">
                      {{ $services->onEachSide(1)->links() }}
                    </div>
                  @endif
                @endif
              </div>
            @endif
          </section>

          {{-- Info y Carrito --}}
          <section class="flex flex-col min-h-0">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h4 class="font-medium text-gray-900 dark:text-gray-100">Información de la venta</h4>
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

              {{-- Agendamiento --}}
              <div>
                <div class="flex items-center justify-between mb-4">
                  <label class="font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-calendar-clock text-indigo-600 dark:text-indigo-400"></i>
                    Agendar venta
                  </label>

                  <button type="button"
                          wire:click="toggleScheduled"
                          role="switch"
                          aria-checked="{{ $isScheduled ? 'true' : 'false' }}"
                          class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $isScheduled ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200 {{ $isScheduled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                  </button>
                </div>

                @if($isScheduled)
                  <div class="space-y-3">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Fecha y hora del encargo
                      </label>
                      <input type="datetime-local"
                             wire:model="scheduledFor"
                             class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                                    bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                                    focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                      @error('scheduledFor')
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                      @enderror
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Se enviará un recordatorio un día antes
                      </p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Notas del encargo
                      </label>
                      <textarea
                        wire:model="orderNotes"
                        rows="3"
                        placeholder="Ej: Torta de chocolate para 20 personas..."
                        class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded
                               bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100
                               placeholder:text-gray-400 dark:placeholder:text-gray-500
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                      @error('orderNotes')
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                      @enderror
                    </div>

                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                      <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                          <strong>Venta agendado:</strong> Se guardará como "Agendado" y podrás confirmarlo el día indicado.
                        </div>
                      </div>
                    </div>
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
                    <p class="text-sm">Agregá productos o servicios</p>
                  </div>
                @else
                  <div class="space-y-3">
                    @foreach($items as $index => $item)
                      <div wire:key="i-{{ $item['product_id'] ?? 'service' }}-{{ $item['service_id'] ?? 'product' }}-{{ $index }}"
                           class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-2">
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $item['name'] }}</div>
                            @if(isset($item['type']))
                              <span class="text-xs px-1.5 py-0.5 rounded
                                           {{ $item['type'] === 'service' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                                {{ $item['type'] === 'service' ? 'Servicio' : 'Producto' }}
                              </span>
                            @endif
                          </div>
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

              {{-- Medios de pago --}}
              @if(count($items) > 0 && !$isScheduled)
                <div>
                  <div class="flex items-center justify-between mb-4">
                    <label class="font-medium text-gray-900 dark:text-gray-100 flex items-center gap-2">
                      <i class="fas fa-credit-card text-green-600 dark:text-green-400"></i>
                      Medios de pago <span class="text-sm text-gray-500 dark:text-gray-400 font-normal">(opcional)</span>
                    </label>
                  </div>

                  @if($availablePaymentMethods->count() > 0)
                    <div class="space-y-3">
                      <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                        Seleccioná cómo se pagó la venta
                      </p>

                      <div class="grid grid-cols-2 gap-2">
                        @foreach($availablePaymentMethods as $pm)
                          <button type="button"
                                  wire:click="$set('paymentMethods.0', {payment_method_id: {{ $pm->id }}, amount: {{ $this->total }}, reference: ''})"
                                  class="px-3 py-2 text-sm border rounded-lg transition-colors
                                         {{ isset($paymentMethods[0]['payment_method_id']) && $paymentMethods[0]['payment_method_id'] == $pm->id
                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                            {{ $pm->name }}
                          </button>
                        @endforeach
                      </div>

                      @if(isset($paymentMethods[0]['payment_method_id']))
                        <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                          <p class="text-sm text-green-800 dark:text-green-200">
                            <strong>Método seleccionado:</strong> {{ $availablePaymentMethods->firstWhere('id', $paymentMethods[0]['payment_method_id'])->name ?? 'N/A' }}
                          </p>
                        </div>
                      @endif
                    </div>
                  @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      No hay medios de pago configurados
                    </p>
                  @endif
                </div>
              @endif
            </div>
          </section>
        </main>

        {{-- Footer --}}
        <footer class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
          <div>
            @if(count($items))
              <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ count($items) }} {{ count($items) !== 1 ? 'items' : 'item' }} •
                {{ array_sum(array_column($items, 'quantity')) }} unidad{{ array_sum(array_column($items, 'quantity')) !== 1 ? 'es' : '' }}
              </div>
              <div class="font-semibold text-lg text-gray-900 dark:text-gray-100">
                Total: ${{ number_format($this->total, 2, ',', '.') }}
              </div>
            @else
              <div class="text-sm text-gray-500 dark:text-gray-400">
                Agregá productos o servicios para continuar
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
                    class="px-4 py-2 text-sm font-medium bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded
                           hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50">
              <span wire:loading.remove wire:target="save">Guardar Venta</span>
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
    
    /* Ocultar el icono nativo del calendario completamente */
    input[type="datetime-local"]::-webkit-calendar-picker-indicator {
      display: none;
      -webkit-appearance: none;
      appearance: none;
    }
    
    /* Estilo para el icono de calendario personalizado en modo oscuro */
    .dark .dark-calendar-icon {
      filter: invert(1) brightness(1.2);
    }
    
    /* Asegurar que el botón sea visible con z-index */
    button img.dark-calendar-icon {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
      position: relative;
      z-index: 10;
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