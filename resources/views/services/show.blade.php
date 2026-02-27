@extends('layouts.app')

@section('header')
<h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">
    Detalle de Servicio
</h1>
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-3 sm:px-6 pb-8">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ============================================================
             COLUMNA IZQUIERDA: Información del servicio
             ============================================================ --}}
        <div class="space-y-4">

            {{-- Card principal --}}
            <div class="bg-white dark:bg-neutral-900 shadow rounded-2xl overflow-hidden">
                <div class="p-5 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-xl font-bold text-neutral-900 dark:text-neutral-100 leading-tight">{{ $service->name }}</h2>
                        <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full {{ $service->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400' }}">
                            {{ $service->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    @if($service->category)
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            <span class="text-neutral-400 dark:text-neutral-500">Categoría:</span> {{ $service->category->name }}
                        </p>
                    @endif

                    @if($service->description)
                        <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ $service->description }}</p>
                    @endif

                    @if(!empty($service->tags))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($service->tags as $tag)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-300">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <div class="pt-1 border-t border-neutral-100 dark:border-neutral-800">
                        <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                            $ {{ number_format($service->price, 2, ',', '.') }}
                            <span class="text-sm font-normal text-neutral-400">precio base</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Variantes --}}
            @if($service->variants->count())
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 p-5">
                    <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-3">Variantes</h3>
                    <div class="space-y-2">
                        @foreach($service->variants as $variant)
                            <div class="flex items-center justify-between py-2 border-b border-neutral-50 dark:border-neutral-800 last:border-0">
                                <div>
                                    <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">{{ $variant->name }}</p>
                                    @if($variant->duration_minutes)
                                        <p class="text-xs text-neutral-400 dark:text-neutral-500">
                                            {{ $variant->duration_minutes >= 60
                                                ? floor($variant->duration_minutes / 60) . 'h ' . ($variant->duration_minutes % 60 ? ($variant->duration_minutes % 60) . 'min' : '')
                                                : $variant->duration_minutes . 'min' }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">$ {{ number_format($variant->price, 0, ',', '.') }}</p>
                                    @if(!$variant->is_active)
                                        <span class="text-xs text-neutral-400">Inactiva</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Insumos --}}
            @if($service->supplies->count())
                <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 p-5">
                    <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-3">Insumos</h3>
                    <div class="space-y-1.5">
                        @foreach($service->supplies as $sup)
                            <div class="flex items-center justify-between py-1.5 border-b border-neutral-50 dark:border-neutral-800 last:border-0 text-sm">
                                <span class="text-neutral-700 dark:text-neutral-300">{{ $sup->supply->name ?? 'Insumo' }}</span>
                                <span class="text-neutral-500 dark:text-neutral-400">
                                    {{ $sup->qty }} {{ $sup->unit }}
                                    @if($sup->waste_pct > 0)
                                        <span class="text-xs text-neutral-400">(+{{ $sup->waste_pct }}% desperdicio)</span>
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Botones --}}
            <div class="flex gap-2">
                <a href="{{ route('services.index') }}" class="px-4 py-2 bg-neutral-200 dark:bg-neutral-800 rounded-lg text-sm text-neutral-700 dark:text-neutral-200 hover:bg-neutral-300 dark:hover:bg-neutral-700 transition-colors">
                    Volver
                </a>
                <a href="{{ route('services.edit', $service) }}" class="px-4 py-2 bg-indigo-600 rounded-lg text-sm text-white hover:bg-indigo-700 transition-colors">
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
                        <a href="{{ route('services.show', ['service' => $service->id, 'period' => $p]) }}"
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
                    $badgeClass = $costSource === 'supplies'
                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                        : 'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400';
                    $badgeLabel = $costSource === 'supplies' ? 'Insumos y gastos' : 'Sin datos de costo';

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
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">$ {{ number_format($salePrice, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500 dark:text-neutral-400">Costo unitario</span>
                        <span class="font-medium text-neutral-900 dark:text-neutral-100">$ {{ number_format($unitCost, 0, ',', '.') }}</span>
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
                            <div class="text-xl font-bold text-neutral-900 dark:text-neutral-100">{{ number_format($unitsSold, 0, ',', '.') }}</div>
                        </div>
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded-lg p-3">
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Ingresos</div>
                            <div class="text-xl font-bold text-neutral-900 dark:text-neutral-100">$ {{ number_format($revenue, 0, ',', '.') }}</div>
                        </div>
                        <div class="bg-neutral-50 dark:bg-neutral-800 rounded-lg p-3">
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Costo total</div>
                            <div class="text-xl font-bold text-neutral-900 dark:text-neutral-100">$ {{ number_format($cogs, 0, ',', '.') }}</div>
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
                 x-init="fetch('{{ route('services.nexum-insight', $service) }}')
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
                    @if($salesRank && $totalSoldServices > 0)
                        <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full font-medium
                            {{ $salesRank <= 3 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' }}">
                            #{{ $salesRank }} en ventas
                            <span class="opacity-60">/ {{ $totalSoldServices }}</span>
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
