@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between">
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
    <i class="fas fa-box text-indigo-600"></i>
    Stock del producto
  </h1>
  <a href="{{ route('stock.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800">
    ← Volver al reporte
  </a>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6">

  {{-- Encabezado del producto --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 mb-4">
    <div class="flex flex-col sm:flex-row gap-4 sm:items-start sm:justify-between">
      <div>
        <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $product->name }}</h2>
        @if($product->sku)
          <div class="text-sm text-neutral-500 dark:text-neutral-400">SKU: {{ $product->sku }}</div>
        @endif
        <div class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">
          Creado por:
          @if($creator['type']==='company')
            <span class="font-medium">Empresa</span> — {{ $creator['company_name'] ?? '—' }}
          @elseif($creator['type']==='branch')
            <span class="font-medium">Sucursal</span> — {{ $creator['branch_name'] ?? $creator['user_name'] ?? '—' }}
          @else
            <span class="font-medium">Usuario</span> — {{ $creator['user_name'] ?? '—' }}
          @endif
        </div>
      </div>
      <div class="text-right">
        <div class="text-xs text-neutral-500 dark:text-neutral-400">Stock total empresa</div>
        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ number_format((float)$totalCompanyStock, 0, ',', '.') }}</div>
      </div>
    </div>
  </div>

  {{-- Grid: sucursales + panel Nexum --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Stock por sucursal --}}
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
      <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-3">Stock por sucursal</h3>

      @if($branchRows->isEmpty())
        <div class="text-sm text-neutral-500 dark:text-neutral-400">No hay sucursales o no hay stock distribuido.</div>
      @else
        <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
          @foreach($branchRows as $row)
            <div class="py-2 flex items-center justify-between">
              <div class="text-neutral-800 dark:text-neutral-200">{{ $row['name'] }}</div>
              <div class="font-semibold {{ $row['stock'] > 0 ? 'text-blue-600 dark:text-blue-300' : 'text-neutral-500 dark:text-neutral-400' }}">
                {{ number_format((float)$row['stock'], 0, ',', '.') }}
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Panel Nexum Intelligence --}}
    @include('stock.partials.intelligence-panel', ['intel' => $intel, 'subject' => 'product'])

  </div>

</div>
@endsection
