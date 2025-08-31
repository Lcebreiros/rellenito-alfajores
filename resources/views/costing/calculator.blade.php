@extends('layouts.app')

@section('header')
  <h1 class="sr-only">Calculadora de Costos</h1>
@endsection

@section('content')
<div class="bg-gray-50 min-h-screen">
  <div class="max-w-6xl mx-auto p-6 costing" x-data="shell()">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Calculadora de Costos</h1>
      <p class="text-gray-600">Gestiona tus insumos, crea recetas y analiza costos de producción</p>
    </div>

    @php
      $suppliesCount = $supplies instanceof \Illuminate\Pagination\AbstractPaginator
        ? $supplies->total()
        : (is_countable($supplies ?? []) ? count($supplies) : 0);
      $savedCount = is_countable($savedAnalyses ?? []) ? count($savedAnalyses) : 0;
    @endphp

    {{-- ======= Navegación (tabs) ======= --}}
    <div class="flex bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
      <button @click="setTab('supplies')"
              :class="activeTab === 'supplies' ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-500' : 'text-gray-700 hover:bg-gray-50'"
              class="flex-1 px-6 py-3 font-medium transition-all">
        <div class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          <span>Insumos</span>
          <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs" x-text="counts.supplies"></span>
        </div>
      </button>

      <button @click="setTab('product')"
              :class="activeTab === 'product' ? 'bg-green-50 text-green-700 border-b-2 border-green-500' : 'text-gray-700 hover:bg-gray-50'"
              class="flex-1 px-6 py-3 font-medium transition-all">
        <div class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
          <span>Productos</span>
        </div>
      </button>

      <button @click="setTab('analysis')"
              :class="activeTab === 'analysis' ? 'bg-purple-50 text-purple-700 border-b-2 border-purple-500' : 'text-gray-700 hover:bg-gray-50'"
              class="flex-1 px-6 py-3 font-medium transition-all">
        <div class="flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 01-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/></svg>
          <span>Análisis</span>
          <span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full text-xs" x-text="counts.saved"></span>
        </div>
      </button>
    </div>

    {{-- ======================= TAB: INSUMOS ======================= --}}
    <div x-show="activeTab === 'supplies'" class="space-y-6 animate-fade-in">
      {{-- Alta rápida --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 rounded-t-xl">
          <h2 class="text-lg font-semibold text-white">Agregar Insumo</h2>
        </div>
        <form class="p-6 space-y-4" method="POST" action="{{ route('supplies.quick-store') }}">
          @csrf
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
              <input name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: Harina 000" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
              <input name="qty" type="number" step="0.001" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
              <select name="unit" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
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
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Precio Total ($)</label>
              <input name="total_cost" type="number" step="0.01" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            </div>
          </div>
          <div class="flex justify-end">
            <button type="submit" class="button-primary">
              <span>Agregar Insumo</span>
            </button>
          </div>
        </form>
      </div>

      {{-- Listado con Editar / Eliminar --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Insumos Disponibles</h3>
        </div>
        <div class="p-6">
          <div class="grid gap-4">
            @forelse(($supplies ?? collect()) as $s)
              <div class="border border-gray-200 rounded-lg hover:bg-gray-50 transition"
                   x-data="supplyRow({
                     id: {{ $s->id }},
                     name: @js($s->name),
                     base_unit: @js($s->base_unit),
                     stock: {{ (float)($s->stock_base_qty ?? 0) }},
                     price: {{ (float)($s->avg_cost_per_base ?? 0) }},
                     updateUrl: @js(route('supplies.update', $s)),
                     deleteUrl: @js(route('supplies.destroy', $s)),
                     csrf: @js(csrf_token()),
                   })">
                <div class="p-4">
                  <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                      <h4 class="font-medium text-gray-900 truncate" x-text="name"></h4>
                      <p class="text-sm text-gray-600">
                        Stock: <span x-text="fmt(stock)"></span> <span x-text="base_unit"></span> •
                        Precio: $<span x-text="fmt(price)"></span>/<span x-text="base_unit"></span>
                      </p>
                    </div>
                    <div class="flex gap-3 shrink-0">
                      <button @click="toggleEdit()" class="text-blue-600 hover:text-blue-800 font-medium text-sm"><span x-show="!editing">Editar</span><span x-show="editing">Cancelar</span></button>
                      <form @submit.prevent="doDelete">
                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Eliminar</button>
                      </form>
                    </div>
                  </div>

                  {{-- Editor inline --}}
                  <div x-show="editing" class="mt-4 pt-4 border-t border-gray-200">
                    <form @submit.prevent="doSave" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                      <div class="sm:col-span-2">
                        <input x-model="form.name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Nombre">
                      </div>
                      <div>
                        <input type="number" step="0.0001" min="0" x-model.number="form.price" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :placeholder="'$/'+base_unit">
                      </div>
                      <div>
                        <input type="number" step="0.001" min="0" x-model.number="form.stock" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :placeholder="'Stock ('+base_unit+')'">
                      </div>
                      <div class="flex items-center">
                        <button type="submit" class="button-primary-sm" :disabled="loading" x-text="loading?'Guardando…':'Guardar'"></button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center py-10 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p>No hay insumos cargados</p>
                <p class="text-sm mt-1">Agrega el primero usando el formulario de arriba</p>
              </div>
            @endforelse
          </div>

          @if($supplies instanceof \Illuminate\Pagination\AbstractPaginator)
            <div class="mt-6">{{ $supplies->withQueryString()->links() }}</div>
          @endif
        </div>
      </div>
    </div>

    {{-- ======================= TAB: PRODUCTO ======================= --}}
    <div x-show="activeTab === 'product'" class="space-y-6 animate-fade-in">
      {{-- Configuración de producto --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 rounded-t-xl">
          <h2 class="text-lg font-semibold text-white">Configurar Producto</h2>
        </div>
        <div class="p-6" x-data="productPicker()">
          <form id="productForm" method="GET" action="{{ route('calculator.show') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="flex flex-col">
              <label class="text-sm font-medium text-gray-700 mb-1">Producto</label>
              <select name="product_id"
                      x-model="selectedId"
                      @change="onChange"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 text-gray-900">
                <option value="">— Seleccionar producto —</option>
                @foreach(($products ?? collect()) as $p)
                  <option value="{{ $p->id }}" @selected(request('product_id')==$p->id)>{{ $p->name }} (SKU {{ $p->sku ?? 'N/A' }})</option>
                @endforeach
              </select>
              <p class="text-xs text-gray-500 mt-2" x-show="selectedName">Seleccionado: <span class="font-medium" x-text="selectedName"></span></p>
            </div>

            <div class="flex flex-col">
              <label class="text-sm font-medium text-gray-700 mb-1">Rendimiento (unidades por batch)</label>
              <input value="{{ (int)(($product->yield_units ?? null) ?: 1) }}" disabled class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-gray-600">
              <p class="text-xs text-gray-500 mt-2">El rendimiento se puede ajustar en la receta.</p>
            </div>
          </form>
        </div>
      </div>

      {{-- Receta (builder) --}}
      <div class="bg-white rounded-xl shadow-sm border border-gray-200"
           x-data="recipeBuilder({ supplies: @js($allSupplies ?? []), defaultYield: {{ (int)(($product->yield_units ?? null) ?: 1) }} })">
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4 rounded-t-xl">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-white">Receta</h2>
            <button type="button" @click="addRow()" class="button-secondary">
              + Agregar Ingrediente
            </button>
          </div>
        </div>

        <div class="p-6 space-y-6">
          <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-gray-700">Rendimiento del batch:</label>
            <input type="number" min="1" class="w-28 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-center" x-model.number="yieldUnits">
            <span class="text-sm text-gray-700">unidades</span>
          </div>

          <div class="space-y-3">
            <template x-for="(row, i) in rows" :key="row.key">
              <div class="flex flex-wrap items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-100">
                <div class="min-w-[220px] flex-1">
                  <select x-model.number="row.supply_id" @change="onSupplyChange(row)"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                    <option value="">— Elegir insumo —</option>
                    <template x-for="s in supplies" :key="s.id">
                      <option :value="s.id" x-text="s.name"></option>
                    </template>
                  </select>
                  <p class="text-xs text-gray-600 mt-1" x-show="row.supply_id">
                    Base: <span x-text="row.base_unit"></span> · $/base: <span x-text="fmt(linePriceBase(row))"></span>
                  </p>
                </div>

                <div class="w-32">
                  <input x-model.number="row.qty" type="number" step="0.001" min="0"
                         class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-right"
                         placeholder="Cantidad">
                </div>

                <div class="w-28">
                  <select x-model="row.unit"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                    <template x-if="row.base_unit === 'g'">
                      <optgroup>
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                      </optgroup>
                    </template>
                    <template x-if="row.base_unit === 'ml'">
                      <optgroup>
                        <option value="ml">ml</option>
                        <option value="l">l</option>
                        <option value="cm3">cm3</option>
                      </optgroup>
                    </template>
                    <template x-if="row.base_unit === 'u'">
                      <option value="u">u</option>
                    </template>
                  </select>
                </div>

                <div class="ml-auto text-right font-semibold text-gray-900">
                  $<span x-text="fmt(lineCost(row))"></span>
                </div>

                <button type="button" @click="removeRow(i)" class="text-red-600 hover:text-red-800 p-1 transition">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
            </template>
          </div>

          {{-- Resumen de costos --}}
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="text-blue-700 text-sm font-medium mb-1">Costo por Batch</div>
              <div class="text-2xl font-bold text-blue-900">$<span x-text="fmt(totalBatch())"></span></div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="text-green-700 text-sm font-medium mb-1">Rendimiento</div>
              <div class="text-2xl font-bold text-green-900"><span x-text="yieldUnits"></span> unidades</div>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
              <div class="text-purple-700 text-sm font-medium mb-1">Costo por Unidad</div>
              <div class="text-2xl font-bold text-purple-900">$<span x-text="fmt(totalPerUnit())"></span></div>
            </div>
          </div>

          {{-- Guardar análisis --}}
          <div class="flex justify-end mt-6 pt-6 border-t border-gray-200">
            <button type="button" @click="confirm()" class="button-success">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span>Guardar Análisis</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- ======================= TAB: ANÁLISIS ======================= --}}
    <div x-show="activeTab === 'analysis'" class="space-y-6 animate-fade-in" x-data="costsPanel()">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
          <div class="flex items-center justify-between gap-4">
            <h2 class="text-lg font-semibold text-white">Análisis Guardados</h2>
            <div class="bg-white/90 rounded-lg px-2 py-1.5">
              <select x-model="filterProductId"
                      class="bg-transparent text-gray-900 rounded-md px-2 py-1 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="">Todos los productos</option>
                <template x-for="p in productOptions" :key="'filter-'+p.id">
                  <option :value="String(p.id)" x-text="p.name"></option>
                </template>
              </select>
            </div>
          </div>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="s in visible" :key="s.id || s._sig">
              <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition bg-white">
                <div class="flex items-start justify-between mb-4 gap-3">
                  <div class="min-w-0">
                    <h4 class="font-semibold text-gray-900 truncate" x-text="s.product_name || 'Sin producto'"></h4>
                    <p class="text-xs text-gray-600" x-text="formatDate(s.created_at)"></p>
                  </div>
                  <span class="px-2 py-0.5 text-xs rounded-full shrink-0"
                        :class="s.source === 'recipe' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700'"
                        x-text="s.source === 'recipe' ? 'Receta' : 'Rápido'"></span>
                </div>

                <div class="space-y-2">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-700">Costo por unidad</span>
                    <span class="font-medium text-gray-900">$<span x-text="fmt(s.unit_total)"></span></span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-700">Costo por batch</span>
                    <span class="font-medium text-gray-900">$<span x-text="fmt(s.batch_total)"></span></span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-700">Rendimiento</span>
                    <span class="font-medium text-gray-900"><span x-text="s.yield_units"></span> unidades</span>
                  </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200" x-show="s.lines && s.lines.length">
                  <div class="text-xs font-medium text-gray-700 mb-1">Ingredientes principales</div>
                  <div class="space-y-1">
                    <template x-for="ln in (s.lines || []).slice(0,3)" :key="ln.name + (ln.base_unit||'')">
                      <div class="flex justify-between text-xs text-gray-600">
                        <span class="truncate mr-2" x-text="ln.name"></span>
                        <span>$<span x-text="fmt(ln.per_unit_cost)"></span></span>
                      </div>
                    </template>
                  </div>
                </div>

                <div class="mt-4 flex justify-end">
                  <button @click="useSaved(s)" class="button-link">Usar este análisis</button>
                </div>
              </div>
            </template>
          </div>

          <div x-show="visible.length === 0" class="text-center py-12 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 01-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4"/></svg>
            <p class="text-lg font-medium">No hay análisis guardados</p>
            <p class="text-sm mt-1">Ve a la pestaña Productos para crear tu primer análisis</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Config global --}}
<script>
  window.APP = {
    csrf: @json(csrf_token()),
    product_id: @json(($product->id ?? null)),
    product_name: @json(($product->name ?? null)),
    products: @json((($products ?? collect())->map(fn($p)=>['id'=>$p->id,'name'=>$p->name])->values())),
    savedAnalyses: @json($savedAnalyses ?? []),
    counts: { supplies: @json($suppliesCount), saved: @json($savedCount) },
    routes: {
      costingsStoreByProduct: @json(isset($product) ? route('products.costings.store', $product) : null),
      costingsIndexByProduct: @json(isset($product) ? route('products.costings.index', $product) : null),
      costingsIndexAll: @json((Route::has('costings.index') ? route('costings.index') : null)),
      costingsStoreAny: @json((Route::has('costings.store') ? route('costings.store') : null)),
    }
  };
</script>

@push('styles')
<style>
  @keyframes fadeIn { from { opacity: 0; transform: translateY(6px);} to { opacity:1; transform:none;} }
  .animate-fade-in{ animation: fadeIn .25s ease-out; }
  .costing select, .costing input { color: #111827; }
  .costing option { color: #111827; }

  /* Estilos personalizados para botones - asegurándonos de que se vean bien */
  .button-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: #2563eb !important;
    color: white !important;
    font-weight: 500;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  }

  .button-primary:hover {
    background-color: #1d4ed8 !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }

  .button-primary:disabled {
    background-color: #9ca3af !important;
    cursor: not-allowed;
  }

  .button-primary-sm {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    background-color: #2563eb !important;
    color: white !important;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  }

  .button-primary-sm:hover {
    background-color: #1d4ed8 !important;
  }

  .button-primary-sm:disabled {
    background-color: #9ca3af !important;
    cursor: not-allowed;
  }

  .button-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  }

  .button-secondary:hover {
    background-color: rgba(255, 255, 255, 0.3) !important;
  }

  .button-success {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background-color: #7c3aed !important;
    color: white !important;
    font-weight: 600;
    padding: 0.625rem 1.5rem;
    border-radius: 0.5rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  }

  .button-success:hover {
    background-color: #6d28d9 !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }

  .button-success:focus {
    outline: 2px solid transparent;
    outline-offset: 2px;
    box-shadow: 0 0 0 2px #a855f7, 0 0 0 4px rgba(168, 85, 247, 0.2);
  }

  .button-link {
    color: #7c3aed !important;
    font-weight: 500;
    font-size: 0.875rem;
    text-decoration: none;
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.2s;
  }

  .button-link:hover {
    color: #6d28d9 !important;
  }
</style>
@endpush

@push('scripts')
<script>
/* ===== Shell (tabs y contadores) ===== */
function shell(){
  return {
    activeTab: 'product',
    counts: { supplies: Number(window.APP?.counts?.supplies || 0), saved: Number(window.APP?.counts?.saved || 0) },
    setTab(t){ this.activeTab = t; },
    init(){
      window.addEventListener('costing-saved', () => { this.counts.saved = (this.counts.saved || 0) + 1; });
      window.addEventListener('supply-created', () => { this.counts.supplies = (this.counts.supplies || 0) + 1; });
      window.addEventListener('supply-deleted', () => { this.counts.supplies = Math.max(0, (this.counts.supplies || 0) - 1); });
    }
  }
}

/* ===== Selector de producto ===== */
function productPicker(){
  const list = Array.isArray(window.APP?.products) ? window.APP.products : [];
  const findName = (id) => (list.find(p => String(p.id) === String(id))?.name) || null;

  return {
    selectedId: String(window.APP?.product_id || '') || '',
    selectedName: window.APP?.product_name || (window.APP?.product_id ? findName(window.APP.product_id) : ''),
    onChange(e){
      const id = e.target.value || '';
      const name = findName(id);
      this.selectedName = name || '';
      window.APP.product_id = id ? Number(id) : null;
      window.APP.product_name = name || null;
      e.target.form?.submit();
    }
  }
}

/* ===== CRUD inline de insumos ===== */
function supplyRow({id, name, base_unit, stock, price, updateUrl, deleteUrl, csrf}){
  return {
    id, name, base_unit, stock, price, updateUrl, deleteUrl, csrf,
    editing: false, loading: false, form: { name, stock, price },
    toggleEdit(){ this.editing = !this.editing; if(this.editing){ this.form = { name: this.name, stock: this.stock, price: this.price }; } },
    async doSave(){
      this.loading = true;
      try{
        const response = await fetch(this.updateUrl, {
          method: 'PUT',
          headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
          body: JSON.stringify({ name: this.form.name, avg_cost_per_base: this.form.price, stock_base_qty: this.form.stock })
        });
        const data = await response.json();
        if(!response.ok || !data.ok) throw new Error(data.message || 'Error al guardar');
        this.name = data.supply.name;
        this.price = parseFloat(data.supply.avg_cost_per_base || 0);
        this.stock = parseFloat(data.supply.stock_base_qty || 0);
        window.dispatchEvent(new CustomEvent('supply-updated', { detail: data.supply }));
        this.editing = false;
      }catch(err){ alert(err.message || 'Error al actualizar el insumo'); }
      finally{ this.loading = false; }
    },
    async doDelete(){
      if(!confirm('¿Eliminar este insumo? Esta acción no se puede deshacer.')) return;
      try{
        const response = await fetch(this.deleteUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' }});
        const data = await response.json();
        if(!response.ok || !data.ok) throw new Error(data.message || 'Error al eliminar');
        window.dispatchEvent(new CustomEvent('supply-deleted', { detail: { id: this.id } }));
        this.$root.remove();
      }catch(err){ alert(err.message || 'Error al eliminar el insumo'); }
    },
    fmt(n){ return Number(n || 0).toFixed(2); }
  }
}

/* ===== Helpers ===== */
function factorToBase(from, base){
  from = (from || '').toLowerCase(); base = (base || '').toLowerCase();
  const mass = { g: 1, kg: 1000 }, vol = { ml: 1, l: 1000, cm3: 1 }, unit = { u: 1 };
  if (base === 'g' && mass[from] !== undefined) return mass[from];
  if (base === 'ml' && vol[from] !== undefined) return vol[from];
  if (base === 'u' && unit[from] !== undefined) return unit[from];
  return NaN;
}
function fmt2(n){ return Number(n || 0).toFixed(2); }

/* ===== Persistir análisis ===== */
async function persistCosting(payload){
  const headers = { 'X-CSRF-TOKEN': window.APP.csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' };
  const url = window.APP.routes?.costingsStoreByProduct || window.APP.routes?.costingsStoreAny;
  if(!url){ alert('No hay ruta para guardar el análisis. Selecciona un producto o habilita la ruta global.'); return null; }
  try{
    const response = await fetch(url, { method: 'POST', headers, body: JSON.stringify(payload) });
    const data = await response.json();
    if(!response.ok || !data.ok) throw new Error(data.message || 'No se pudo guardar el análisis');
    return data.costing || null;
  }catch(err){ console.error(err); alert(err.message || 'Error al guardar el análisis'); return null; }
}

/* ===== Builder de receta ===== */
function recipeBuilder({supplies, defaultYield}){
  let suppliesIndex = Object.fromEntries((supplies || []).map(s => [s.id, s]));
  const productList = Array.isArray(window.APP?.products) ? window.APP.products : [];
  const productNameById = (id) => (productList.find(p => String(p.id) === String(id))?.name) || null;

  return {
    supplies,
    yieldUnits: defaultYield || 1,
    rows: [{ key: crypto?.randomUUID?.() || String(Date.now()), supply_id: '', base_unit: '', cost_base: 0, qty: 0, unit: '' }],

    init(){
      window.addEventListener('supply-updated', (e) => {
        const u = e.detail;
        const idx = this.supplies.findIndex(s => s.id === u.id);
        if (idx >= 0) Object.assign(this.supplies[idx], { name: u.name, base_unit: u.base_unit, avg_cost_per_base: Number(u.avg_cost_per_base || 0) });
        else this.supplies.push(u);
        suppliesIndex = Object.fromEntries(this.supplies.map(s => [s.id, s]));
        this.rows.forEach(r => { if (r.supply_id === u.id){ r.base_unit = u.base_unit; r.cost_base = Number(u.avg_cost_per_base || 0); if(!r.unit) r.unit = r.base_unit; }});
      });
      window.addEventListener('supply-deleted', (e) => {
        const id = e.detail.id;
        this.supplies = this.supplies.filter(s => s.id !== id);
        suppliesIndex = Object.fromEntries(this.supplies.map(s => [s.id, s]));
        this.rows.forEach(r => { if (r.supply_id === id) Object.assign(r, { supply_id:'', base_unit:'', cost_base:0, qty:0, unit:'' }); });
      });
    },

    addRow(){ this.rows.push({ key: crypto?.randomUUID?.() || String(Date.now()+Math.random()), supply_id:'', base_unit:'', cost_base:0, qty:0, unit:'' }); },
    removeRow(i){ this.rows.splice(i,1); if(this.rows.length===0) this.addRow(); },
    onSupplyChange(row){
      const s = suppliesIndex[row.supply_id] || null;
      row.base_unit = s ? s.base_unit : '';
      row.cost_base = s ? Number(s.avg_cost_per_base || 0) : 0;
      row.unit = row.base_unit || '';
      row.qty = row.qty || 0;
    },

    linePriceBase(row){ const s = suppliesIndex[row.supply_id]; return s ? Number(s.avg_cost_per_base || 0) : Number(row.cost_base || 0); },
    lineBaseQty(row){
      if(!row.supply_id || !row.base_unit) return 0;
      const factor = factorToBase(row.unit, row.base_unit);
      if(!isFinite(factor)) return 0;
      return Number(row.qty || 0) * factor;
    },
    lineCost(row){ return this.lineBaseQty(row) * this.linePriceBase(row); },
    totalBatch(){ return this.rows.reduce((acc, r) => acc + this.lineCost(r), 0); },
    totalPerUnit(){ const y = Math.max(1, Number(this.yieldUnits || 1)); return this.totalBatch() / y; },
    fmt(n){ return fmt2(n); },

    buildPayload(){
      const y = Math.max(1, Number(this.yieldUnits || 1));

      const lines = this.rows
        .filter(r => r.supply_id && r.base_unit)
        .map(r => {
          const s = suppliesIndex[r.supply_id];
          const price   = s ? Number(s.avg_cost_per_base || 0) : Number(r.cost_base || 0);
          const baseQty = this.lineBaseQty(r);
          return {
            id: r.supply_id,
            name: s ? s.name : '—',
            base_unit: r.base_unit,
            per_unit_qty: +(baseQty / y).toFixed(4),
            per_unit_cost: +((baseQty * price) / y).toFixed(4)
          };
        });

      const unitTotal  = lines.reduce((acc, l) => acc + l.per_unit_cost, 0);
      const batchTotal = unitTotal * y;

      // ✅ 'perc' requerido por backend
      lines.forEach(l => { l.perc = unitTotal > 0 ? +(l.per_unit_cost / unitTotal).toFixed(6) : 0; });

      // Asegurar nombre de producto
      let pid   = window.APP.product_id ?? null;
      let pname = window.APP.product_name ?? null;
      if (!pname && pid){
        const found = productList.find(p => String(p.id)===String(pid));
        pname = found?.name || null;
      }

      return {
        source: 'recipe',
        yield_units: y,
        unit_total: +unitTotal.toFixed(2),
        batch_total: +batchTotal.toFixed(2),
        lines,
        product_id: pid,
        product_name: pname || null
      };
    },

    async confirm(){
      const payload = this.buildPayload();
      if (!payload.product_id || !payload.product_name){
        alert('Selecciona un producto válido antes de guardar el análisis.');
        return;
      }
      if (payload.lines.length === 0){
        alert('Agrega al menos un ingrediente a la receta.');
        return;
      }
      const saved = await persistCosting(payload);
      const record = saved || { ...payload, created_at: new Date().toISOString() };
      window.dispatchEvent(new CustomEvent('costing-saved', { detail: record }));
      const shellEl = document.querySelector('[x-data*="shell()"]');
      if (shellEl && shellEl.__x && shellEl.__x.$data) shellEl.__x.$data.setTab('analysis');
    },
  }
}

/* ===== Panel de análisis ===== */
function costsPanel(){
  return {
    saved: [],
    filterProductId: '',
    get productOptions(){
      const base = Array.isArray(window.APP?.products) ? window.APP.products.slice() : [];
      const extra = (this.saved || []).map(s => ({ id: s.product_id, name: s.product_name })).filter(x => x.id && x.name);
      const map = new Map();
      [...base, ...extra].forEach(p => { if (p && p.id) map.set(String(p.id), { id: String(p.id), name: p.name }); });
      return Array.from(map.values()).sort((a,b)=>a.name.localeCompare(b.name));
    },
    get visible(){ if(!this.filterProductId) return this.saved; return this.saved.filter(s => String(s.product_id||'')===String(this.filterProductId)); },

    async init(){
      this.saved = this.dedupeAndSort(Array.isArray(window.APP?.savedAnalyses) ? window.APP.savedAnalyses : []);
      if (window.APP?.routes?.costingsIndexAll){
        try{ const r = await fetch(window.APP.routes.costingsIndexAll); if(r.ok){ const list = await r.json(); this.saved = this.dedupeAndSort([...(this.saved||[]), ...(Array.isArray(list)?list:[])]); } }catch(e){ console.error(e); }
      }else if (window.APP?.routes?.costingsIndexByProduct){
        try{ const r = await fetch(window.APP.routes.costingsIndexByProduct); if(r.ok){ const list = await r.json(); this.saved = this.dedupeAndSort([...(this.saved||[]), ...(Array.isArray(list)?list:[])]); } }catch(e){ console.error(e); }
      }
      window.addEventListener('costing-saved', (e)=>{ const item = e.detail; if(item) this.upsertSaved(item); });
    },

    signature(s){
      const round = (n,p=4)=>Number((Number(n||0)).toFixed(p));
      const lines = (s.lines||[]).map(l=>({ id: l.id ?? l.name, q: round(l.per_unit_qty,3), u:(l.base_unit||'') }))
                                 .sort((a,b)=>String(a.id).localeCompare(String(b.id)));
      return JSON.stringify({ pid: s.product_id ?? null, src: s.source, y: round(s.yield_units,0), u: round(s.unit_total,4), b: round(s.batch_total,4), lines });
    },
    dedupeAndSort(list){
      const map = new Map();
      for(const item of (list||[])){
        const key = item.id ? `id:${item.id}` : `sig:${this.signature(item)}`;
        map.set(key, { ...item, _sig: this.signature(item) });
      }
      return Array.from(map.values()).sort((a,b)=> new Date(b.created_at||0) - new Date(a.created_at||0));
    },
    upsertSaved(item){ this.saved = this.dedupeAndSort([item, ...this.saved]); },
    useSaved(s){
      alert(`Análisis seleccionado: ${s.product_name || 'Sin producto'}`);
      const shellEl = document.querySelector('[x-data*="shell()"]');
      if (shellEl && shellEl.__x && shellEl.__x.$data) shellEl.__x.$data.setTab('product');
    },
    fmt(n){ return Number(n||0).toFixed(2); },
    formatDate(iso){ try{ return new Date(iso).toLocaleString('es-AR',{year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'});}catch{ return iso||''; } }
  }
}
</script>
@endpush
@endsection