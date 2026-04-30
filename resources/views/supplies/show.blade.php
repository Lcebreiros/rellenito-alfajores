@extends('layouts.app')

@section('header')
<div class="flex items-center gap-3">
  <a href="{{ route('calculator.show') }}" class="inline-flex items-center gap-1.5 text-sm text-neutral-500 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-100 transition-colors">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
    {{ __('stock.back') }}
  </a>
  <span class="text-neutral-300 dark:text-neutral-600">/</span>
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-900 dark:text-neutral-100 truncate">
    {{ $supply->name }}
  </h1>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6">

  {{-- Supply header --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 mb-4">
    <div class="flex flex-col sm:flex-row gap-4 sm:items-start sm:justify-between">
      <div>
        <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ $supply->name }}</h2>
        @if($supply->description)
          <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ $supply->description }}</p>
        @endif
        <div class="mt-2 flex flex-wrap gap-2">
          <span class="text-xs px-2 py-0.5 rounded-full bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 font-medium">
            {{ __('stock.supply_base_unit') }} {{ $supply->base_unit }}
          </span>
          @if($supply->supplier)
            <span class="text-xs px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
              {{ $supply->supplier->name }}
            </span>
          @endif
        </div>
      </div>
      <div class="text-right shrink-0">
        <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('stock.supply_stock_available') }}</div>
        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
          {{ $supply->formatted_stock }}
          <span class="text-base font-normal text-neutral-500">{{ $supply->base_unit }}</span>
        </div>
        <div class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">
          ${{ number_format((float)$supply->avg_cost_per_base, 4, ',', '.') }} / {{ $supply->base_unit }}
        </div>
      </div>
    </div>
  </div>

  {{-- Grid: Nexum panel + products using this supply --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- Nexum Intelligence panel --}}
    @include('stock.partials.intelligence-panel', ['intel' => $intel, 'subject' => 'supply'])

    {{-- Products using this supply --}}
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
      <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-3">{{ __('stock.supply_products_using') }}</h3>

      @if($recipeItems->isEmpty())
        <div class="text-sm text-neutral-500 dark:text-neutral-400">
          {{ __('stock.supply_no_recipe') }}
        </div>
      @else
        <div class="divide-y divide-neutral-100 dark:divide-neutral-800">
          @foreach($recipeItems as $recipe)
            @php
              $pc = collect($intel['productsUsing'])->firstWhere('name', $recipe->product->name);
            @endphp
            <div class="py-2.5">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200">{{ $recipe->product->name }}</span>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $recipe->qty }} {{ $recipe->unit }} {{ __('stock.intel_per_unit') }}</span>
              </div>
              @if($pc && $pc['daily_consumption'] > 0)
                <div class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">
                  {{ __('stock.supply_estimated') }} {{ number_format($pc['daily_consumption'], 3, ',', '.') }} {{ $supply->base_unit }}/{{ __('stock.supply_day_abbr') }}
                  · {{ number_format($pc['daily_sales'], 2, ',', '.') }} {{ __('stock.supply_sales_per_day') }}
                </div>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>

  </div>

  {{-- Last purchases --}}
  @if($supply->purchases->isNotEmpty())
    <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
      <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-3">{{ __('stock.supply_last_purchases') }}</h3>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-neutral-500 dark:text-neutral-400 border-b border-neutral-200 dark:border-neutral-700">
              <th class="pb-2 pr-4 font-medium">{{ __('stock.col_date') }}</th>
              <th class="pb-2 pr-4 font-medium">{{ __('stock.col_quantity') }}</th>
              <th class="pb-2 pr-4 font-medium">{{ __('stock.col_unit') }}</th>
              <th class="pb-2 text-right font-medium">{{ __('stock.col_total') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
            @foreach($supply->purchases as $purchase)
              <tr>
                <td class="py-2 pr-4 text-neutral-600 dark:text-neutral-400">{{ $purchase->created_at->format('d/m/Y') }}</td>
                <td class="py-2 pr-4 font-medium text-neutral-900 dark:text-neutral-100">{{ number_format($purchase->qty, 2, ',', '.') }}</td>
                <td class="py-2 pr-4 text-neutral-500 dark:text-neutral-400">{{ $purchase->unit }}</td>
                <td class="py-2 text-right font-semibold text-neutral-900 dark:text-neutral-100">${{ number_format($purchase->total_cost, 2, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

</div>
@endsection
