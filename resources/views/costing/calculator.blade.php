@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100 leading-tight transition-colors">
    Calculadora de Costos
  </h1>
  <p class="text-gray-600 dark:text-neutral-300 transition-colors">
    Gestiona tus insumos, crea recetas y analiza costos de producción
  </p>
@endsection

@section('content')
<div class="bg-gray-50 dark:bg-neutral-900 min-h-screen transition-colors">
  <div class="max-w-6xl mx-auto p-6 space-y-8">

    @php
      $tab = request('tab','product'); // supplies | product | analysis
      $tabClasses = 'flex-1 px-6 py-3 font-medium transition-all text-center rounded-md';
      $active = [
        'supplies' => $tab === 'supplies',
        'product'  => $tab === 'product',
        'analysis' => $tab === 'analysis',
      ];
      $url = fn($t)=> request()->fullUrlWithQuery(['tab'=>$t]);
    @endphp

    {{-- Tabs (contenedor transparente) --}}
    <div class="rounded-xl mb-2 overflow-hidden transition-colors bg-transparent dark:bg-transparent border border-transparent shadow-none">
      <nav class="flex flex-col sm:flex-row gap-1 sm:gap-0 p-1 sm:p-0">
        <a href="{{ $url('supplies') }}"
           @if($active['supplies']) aria-current="page" @endif
           class="{{ $tabClasses }} {{ $active['supplies']
              ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-500 sm:border-b-0 sm:border-b-transparent sm:border-t-2 sm:border-t-blue-500 dark:bg-neutral-800/40 dark:text-neutral-100 dark:border-t-neutral-500'
              : 'text-gray-700 hover:bg-gray-50 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
          Insumos
        </a>

        <a href="{{ $url('product') }}"
           @if($active['product']) aria-current="page" @endif
           class="{{ $tabClasses }} {{ $active['product']
              ? 'bg-green-50 text-green-700 border-b-2 border-green-500 sm:border-b-0 sm:border-b-transparent sm:border-t-2 sm:border-t-green-500 dark:bg-neutral-800/40 dark:text-neutral-100 dark:border-t-neutral-500'
              : 'text-gray-700 hover:bg-gray-50 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
          Productos
        </a>

        <a href="{{ $url('analysis') }}"
           @if($active['analysis']) aria-current="page" @endif
           class="{{ $tabClasses }} {{ $active['analysis']
              ? 'bg-purple-50 text-purple-700 border-b-2 border-purple-500 sm:border-b-0 sm:border-b-transparent sm:border-t-2 sm:border-t-purple-500 dark:bg-neutral-800/40 dark:text-neutral-100 dark:border-t-neutral-500'
              : 'text-gray-700 hover:bg-gray-50 dark:text-neutral-200 dark:hover:bg-neutral-800' }}">
          Análisis
        </a>
      </nav>
    </div>

    {{-- Contenido por tab --}}
    @if($active['supplies'])
      <livewire:calculator.supplies-manager />
    @elseif($active['product'])
      {{-- Podés pasar product_id si querés preseleccionar --}}
      <livewire:calculator.product-recipe :productId="request()->integer('product_id')" />
    @else
      <livewire:calculator.analyses-panel />
    @endif

  </div>
</div>
@endsection
