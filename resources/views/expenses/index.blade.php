@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">Gastos</h1>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">

  <div class="mb-6">
    <p class="text-sm text-neutral-600 dark:text-neutral-400">
      Gestiona todos tus gastos en un solo lugar. Registra gastos de proveedores, servicios, producción y más.
    </p>
  </div>

  <!-- Tarjetas de resumen -->
  <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 mb-6">
    <!-- Gastos de Proveedores -->
    <div class="rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">Proveedores</p>
          <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
            ${{ number_format($totalSupplier, 2, ',', '.') }}
          </p>
          <p class="text-xs text-neutral-500 dark:text-neutral-500">anual</p>
        </div>
        <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Gastos de Servicios -->
    <div class="rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">Servicios</p>
          <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
            ${{ number_format($totalService, 2, ',', '.') }}
          </p>
          <p class="text-xs text-neutral-500 dark:text-neutral-500">total</p>
        </div>
        <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Servicios de Terceros -->
    <div class="rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">Terceros</p>
          <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
            ${{ number_format($totalThirdParty, 2, ',', '.') }}
          </p>
          <p class="text-xs text-neutral-500 dark:text-neutral-500">anual</p>
        </div>
        <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Gastos de Producción -->
    <div class="rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">Producción</p>
          <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
            ${{ number_format($totalProduction, 2, ',', '.') }}
          </p>
          <p class="text-xs text-neutral-500 dark:text-neutral-500">total</p>
        </div>
        <div class="h-10 w-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Insumos -->
    <div class="rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">Insumos</p>
          <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
            ${{ number_format($totalSupplies, 2, ',', '.') }}
          </p>
          <p class="text-xs text-neutral-500 dark:text-neutral-500">en stock</p>
        </div>
        <div class="h-10 w-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Accesos rápidos -->
  <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
    <!-- Gastos de Proveedores -->
    <a href="{{ route('expenses.suppliers') }}"
       class="group rounded-lg border border-neutral-200 bg-white p-6 hover:border-indigo-300 hover:shadow-md transition
              dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-indigo-500/50">
      <div class="flex items-center gap-4">
        <div class="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <div>
          <h3 class="font-medium text-neutral-900 dark:text-neutral-100">Proveedores</h3>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $supplierExpenses->count() }} gastos</p>
        </div>
      </div>
      <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">
        Gestiona gastos relacionados con productos y proveedores
      </p>
    </a>

    <!-- Gastos de Servicios -->
    <a href="{{ route('expenses.services') }}"
       class="group rounded-lg border border-neutral-200 bg-white p-6 hover:border-indigo-300 hover:shadow-md transition
              dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-indigo-500/50">
      <div class="flex items-center gap-4">
        <div class="h-12 w-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h3 class="font-medium text-neutral-900 dark:text-neutral-100">Servicios</h3>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $serviceExpenses->count() }} gastos</p>
        </div>
      </div>
      <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">
        Costos de ofrecer tus servicios propios
      </p>
    </a>

    <!-- Servicios de Terceros -->
    <a href="{{ route('expenses.third-party') }}"
       class="group rounded-lg border border-neutral-200 bg-white p-6 hover:border-indigo-300 hover:shadow-md transition
              dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-indigo-500/50">
      <div class="flex items-center gap-4">
        <div class="h-12 w-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <div>
          <h3 class="font-medium text-neutral-900 dark:text-neutral-100">Terceros</h3>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $thirdPartyServices->count() }} servicios</p>
        </div>
      </div>
      <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">
        Servicios externos y su frecuencia de pago
      </p>
    </a>

    <!-- Gastos de Producción -->
    <a href="{{ route('expenses.production') }}"
       class="group rounded-lg border border-neutral-200 bg-white p-6 hover:border-indigo-300 hover:shadow-md transition
              dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-indigo-500/50">
      <div class="flex items-center gap-4">
        <div class="h-12 w-12 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <h3 class="font-medium text-neutral-900 dark:text-neutral-100">Producción</h3>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $productionExpenses->count() }} gastos</p>
        </div>
      </div>
      <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">
        Gastos por producto y acceso a calculadora
      </p>
    </a>

    <!-- Insumos -->
    <a href="{{ route('expenses.supplies') }}"
       class="group rounded-lg border border-neutral-200 bg-white p-6 hover:border-indigo-300 hover:shadow-md transition
              dark:border-neutral-800 dark:bg-neutral-900 dark:hover:border-indigo-500/50">
      <div class="flex items-center gap-4">
        <div class="h-12 w-12 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
          <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <div>
          <h3 class="font-medium text-neutral-900 dark:text-neutral-100">Insumos</h3>
          <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $supplies->count() }} insumos</p>
        </div>
      </div>
      <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">
        Emboltorio, etiquetas y otros materiales
      </p>
    </a>
  </div>

</div>
@endsection
