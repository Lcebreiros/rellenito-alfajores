@extends('layouts.app')

@section('header')
<div class="flex items-center gap-3">
  <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-100 transition-colors">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
    atrás
  </a>
  <span class="text-neutral-300 dark:text-neutral-600">/</span>
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100 truncate">
    {{ $product->name }}
  </h1>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-6 pb-8">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ============================================================
             COLUMNA IZQUIERDA: Información del producto
             ============================================================ --}}
        <div class="space-y-4">

            {{-- Card principal --}}
            <div class="bg-white dark:bg-neutral-900 shadow rounded-2xl overflow-hidden">
                {{-- Imagen --}}
                @php
                    $imgUrl = null;
                    if (!empty($product->image) && \Illuminate\Support\Facades\Storage::disk('public')->exists($product->image)) {
                        $imgUrl = \Illuminate\Support\Facades\Storage::url($product->image);
                    }
                @endphp
                @if($imgUrl)
                    <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="w-full h-56 object-cover">
                @else
                    <div class="w-full h-40 flex items-center justify-center bg-neutral-100 dark:bg-neutral-800">
                        <svg class="h-12 w-12 text-neutral-400 dark:text-neutral-500" viewBox="0 0 24 24" fill="none">
                            <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M7 15l3-3 3 3 4-4 2 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                @endif

                {{-- Datos del producto --}}
                <div class="p-5 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 leading-tight">{{ $product->name }}</h2>
                        <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full {{ $product->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400' }}">
                            {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <p class="text-xs text-neutral-400 dark:text-neutral-500 font-mono">SKU: {{ $product->sku }}</p>

                    @if($product->description)
                        <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ $product->description }}</p>
                    @endif

                    @if($product->category)
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            <span class="text-neutral-400 dark:text-neutral-500">Categoría:</span> {{ $product->category }}
                        </p>
                    @endif

                    <div class="pt-1 border-t border-neutral-100 dark:border-neutral-800">
                        <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                            $ {{ number_format($product->price, 2, ',', '.') }}
                        </p>
                    </div>

                    {{-- Sólo visible para master/company --}}
                    @php $auth = auth()->user(); @endphp
                    @if($auth && ((method_exists($auth,'isMaster') && $auth->isMaster()) || (method_exists($auth,'isCompany') && $auth->isCompany())))
                        @php
                            $owner = $product->user;
                            $companyName = $product->company?->name;
                            $chain = null;
                            if ($owner && $owner->representable_type === \App\Models\Branch::class) {
                                $branchName = optional($owner->representable)->name;
                                $chain = trim(($companyName ?: 'Empresa') . ' → ' . ($branchName ?: 'Sucursal'));
                            } elseif ($owner && method_exists($owner,'isCompany') && $owner->isCompany()) {
                                $chain = $owner->name;
                            } else {
                                $chain = $companyName ?: ($owner?->name ?? 'N/D');
                            }
                            $creatorText = null;
                            if ($owner && $owner->representable_type === \App\Models\Branch::class) {
                                $creatorText = 'Creado por sucursal: ' . (optional($owner->representable)->name ?? ('#'.$owner->representable_id));
                            } elseif ($owner && method_exists($owner,'isCompany') && $owner->isCompany()) {
                                $creatorText = 'Creado por empresa';
                            } else {
                                $creatorText = 'Creado por usuario';
                            }
                        @endphp
                        <div class="pt-2 space-y-0.5">
                            <p class="text-xs text-neutral-400 dark:text-neutral-500">
                                #{{ $product->user_id }} — {{ $product->user?->name ?? 'N/D' }}
                                @if(!empty($chain))<span class="ml-1">({{ $chain }})</span>@endif
                            </p>
                            @if($creatorText)
                                <p class="text-xs text-neutral-400 dark:text-neutral-500">{{ $creatorText }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Card de stock --}}
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Stock</h3>
                    <span class="text-lg font-bold text-neutral-900 dark:text-neutral-100">{{ $totalStock }}</span>
                </div>
                @if($locations->count())
                    <div class="space-y-1.5">
                        @foreach($locations as $loc)
                            <div class="flex justify-between items-center py-1.5 border-b border-neutral-50 dark:border-neutral-800 last:border-0 text-sm">
                                <span class="text-neutral-600 dark:text-neutral-300">{{ $loc->branch->name ?? 'Sucursal ' . $loc->branch_id }}</span>
                                <span class="font-medium {{ $loc->stock > 0 ? 'text-blue-600 dark:text-blue-300' : 'text-rose-500 dark:text-rose-400' }}">
                                    {{ $loc->stock }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Sin stock en sucursales.</p>
                @endif
            </div>

            {{-- Botones --}}
            <div class="flex gap-2">
                <a href="{{ route('products.index') }}" class="px-4 py-2 bg-neutral-200 dark:bg-neutral-800 rounded-lg text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-300 dark:hover:bg-neutral-700 transition-colors">
                    Volver
                </a>
                <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 bg-indigo-600 rounded-lg text-sm text-white hover:bg-indigo-700 transition-colors">
                    Editar
                </a>
            </div>

        </div>

        {{-- ============================================================
             COLUMNA DERECHA: Rentabilidad
             ============================================================ --}}
        <div class="space-y-4">

            {{-- Encabezado + selector de período --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Rentabilidad</h3>
                <div class="flex gap-1 text-xs">
                    @foreach([30 => '30 días', 90 => '90 días', 365 => '1 año'] as $p => $label)
                        <a href="{{ route('products.show', ['product' => $product->id, 'period' => $p]) }}"
                           class="px-3 py-1.5 rounded-lg border transition-colors
                                  {{ $period == $p
                                      ? 'bg-indigo-600 text-white border-indigo-600'
                                      : 'border-neutral-300 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Card: Costo por unidad --}}
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 p-5">
                @php
                    $badgeClass = match($costSource) {
                        'costing' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                        'recipe'  => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                        default   => 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400',
                    };
                    $badgeLabel = match($costSource) {
                        'costing' => 'Análisis de receta',
                        'recipe'  => 'Receta simple',
                        default   => 'Costo manual',
                    };
                    $barColor = match($marginHealth) {
                        'green'  => 'bg-emerald-500',
                        'yellow' => 'bg-amber-400',
                        default  => 'bg-rose-500',
                    };
                    $pctTextColor = match($marginHealth) {
                        'green'  => 'text-emerald-600 dark:text-emerald-400',
                        'yellow' => 'text-amber-600 dark:text-amber-400',
                        default  => 'text-rose-600 dark:text-rose-400',
                    };
                    $amountTextColor = $grossMargin >= 0
                        ? 'text-emerald-600 dark:text-emerald-400'
                        : 'text-rose-600 dark:text-rose-400';
                @endphp
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Costo por unidad</h4>
                    <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full {{ $badgeClass }}">
                        {{ $badgeLabel }}
                    </span>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Precio de venta</span>
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">
                            $ {{ number_format($salePrice, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Costo unitario</span>
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">
                            $ {{ number_format($unitCost, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Margen neto en $ y % --}}
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="bg-neutral-50 dark:bg-neutral-800 rounded-lg p-3">
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Ganancia neta</div>
                        <div class="text-xl font-bold {{ $amountTextColor }}">
                            $ {{ number_format($grossMargin, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-800 rounded-lg p-3">
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Margen neto</div>
                        <div class="text-xl font-bold {{ $pctTextColor }}">
                            {{ $marginPct }}%
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="w-full bg-neutral-100 dark:bg-neutral-800 rounded-full h-2">
                        <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ max(0, min(100, $marginPct)) }}%"></div>
                    </div>
                    <p class="mt-1.5 text-[10px] text-neutral-400 dark:text-neutral-500">
                        Verde ≥ 30% &nbsp;·&nbsp; Amarillo 15–29% &nbsp;·&nbsp; Rojo &lt; 15%
                    </p>
                </div>
            </div>

            {{-- Card: Ventas del período --}}
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 p-5">
                <h4 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-4">
                    Ventas en los últimos {{ $period }} días
                </h4>

                @if(!$hasSales)
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Sin ventas registradas en este período.</p>
                @else
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded-lg p-3">
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Unidades</div>
                            <div class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                                {{ number_format($unitsSold, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded-lg p-3">
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Ingresos</div>
                            <div class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                                $ {{ number_format($revenue, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded-lg p-3">
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Costo total</div>
                            <div class="text-xl font-bold text-neutral-900 dark:text-neutral-100">
                                $ {{ number_format($cogs, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded-lg p-3">
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Ganancia bruta</div>
                            <div class="text-xl font-bold {{ $grossProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                $ {{ number_format($grossProfit, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Card: Nexum Analytics --}}
            <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 p-5"
                 x-data="{ insight: null, loading: true, error: false }"
                 x-init="fetch('{{ route('products.nexum-insight', $product) }}')
                     .then(r => r.json())
                     .then(d => { insight = d.insight; loading = false; })
                     .catch(() => { error = true; loading = false; })">

                <div class="flex items-center gap-2 mb-4">
                    <div class="w-5 h-5 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-[8px] font-black">N</span>
                    </div>
                    <h4 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Nexum Analytics</h4>
                </div>

                {{-- Badges comparativos --}}
                <div class="flex flex-wrap gap-2 mb-4">
                    @if($revenueSharePct > 0)
                        <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300 font-medium">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zm6-4a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zm6-3a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                            {{ $revenueSharePct }}% de ingresos
                        </span>
                    @endif
                    @if($salesRank && $totalSoldProducts > 0)
                        <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium
                            {{ $salesRank <= 3 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' }}">
                            #{{ $salesRank }} en ventas
                            <span class="opacity-60">/ {{ $totalSoldProducts }}</span>
                        </span>
                    @endif
                    @if(!$salesRank && $revenueSharePct == 0)
                        <span class="text-xs text-neutral-400 dark:text-neutral-500 italic">Sin ventas en los últimos 30 días</span>
                    @endif
                </div>

                {{-- Análisis AI (lazy) --}}
                <div x-show="loading" class="flex items-center gap-2 text-xs text-neutral-400 dark:text-neutral-500">
                    <svg class="animate-spin h-3 w-3 flex-shrink-0" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Analizando...
                </div>
                <p x-show="!loading && !error && insight" x-text="insight"
                   class="text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed"></p>
                <p x-show="error" class="text-xs text-rose-500">No se pudo cargar el análisis.</p>
            </div>

        </div>
        {{-- fin columna derecha --}}

    </div>
</div>
@endsection
