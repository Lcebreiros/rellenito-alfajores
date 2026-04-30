@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">{{ __('expenses.title') }}</h1>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6" x-data="{ activeTab: 'general' }">

  <!-- Sistema de Pestañas -->
  <div class="mb-6">
    <div class="border-b border-neutral-200 dark:border-neutral-700">
      <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        <button @click="activeTab = 'general'"
                :class="activeTab === 'general' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
          {{ __('expenses.tab_general') }}
        </button>
        <button @click="activeTab = 'suppliers'"
                :class="activeTab === 'suppliers' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
          {{ __('expenses.tab_suppliers') }}
        </button>
        <button @click="activeTab = 'services'"
                :class="activeTab === 'services' ? 'border-green-500 text-green-600 dark:text-green-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
          {{ __('expenses.tab_services') }}
        </button>
        <button @click="activeTab = 'third-party'"
                :class="activeTab === 'third-party' ? 'border-purple-500 text-purple-600 dark:text-purple-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
          {{ __('expenses.tab_third_party') }}
        </button>
        <button @click="activeTab = 'production'"
                :class="activeTab === 'production' ? 'border-orange-500 text-orange-600 dark:text-orange-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
          {{ __('expenses.tab_production') }}
        </button>
        <button @click="activeTab = 'supplies'"
                :class="activeTab === 'supplies' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
          {{ __('expenses.tab_supplies') }}
        </button>
      </nav>
    </div>
  </div>

  <!-- Panel General -->
  <div x-show="activeTab === 'general'" x-cloak>
    <div class="mb-6">
      <p class="text-sm text-neutral-600 dark:text-neutral-400">
        {{ __('expenses.general_desc') }}
      </p>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 mb-8">
      @php
        $expenseCards = [
          ['title' => __('expenses.card_suppliers'), 'total' => $totalSupplier, 'subtitle' => __('expenses.subtitle_annual'), 'color' => 'blue', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
          ['title' => __('expenses.card_services'), 'total' => $totalService, 'subtitle' => __('expenses.subtitle_total'), 'color' => 'green', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
          ['title' => __('expenses.card_third_party'), 'total' => $totalThirdParty, 'subtitle' => __('expenses.subtitle_annual'), 'color' => 'purple', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
          ['title' => __('expenses.card_production'), 'total' => $totalProduction, 'subtitle' => __('expenses.subtitle_total'), 'color' => 'orange', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
          ['title' => __('expenses.card_supplies'), 'total' => $totalSupplies, 'subtitle' => __('expenses.subtitle_in_stock'), 'color' => 'amber', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ];
      @endphp

      @foreach($expenseCards as $card)
      <div class="rounded-lg border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $card['title'] }}</p>
            <p class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
              ${{ number_format($card['total'], 2, ',', '.') }}
            </p>
            <p class="text-xs text-neutral-500 dark:text-neutral-500">{{ $card['subtitle'] }}</p>
          </div>
          <div class="h-10 w-10 rounded-full bg-{{ $card['color'] }}-100 dark:bg-{{ $card['color'] }}-900/30 flex items-center justify-center">
            <svg class="h-6 w-6 text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/>
            </svg>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <!-- Gráfico de distribución de gastos -->
    <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900 mb-8">
      <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">{{ __('expenses.distribution_title') }}</h3>
      @php
        $total = $totalSupplier + $totalService + $totalThirdParty + $totalProduction + $totalSupplies;
        $percentages = [
          ['name' => __('expenses.card_suppliers'), 'value' => $totalSupplier, 'color' => 'bg-blue-500', 'percent' => $total > 0 ? ($totalSupplier / $total) * 100 : 0],
          ['name' => __('expenses.card_services'), 'value' => $totalService, 'color' => 'bg-green-500', 'percent' => $total > 0 ? ($totalService / $total) * 100 : 0],
          ['name' => __('expenses.card_third_party'), 'value' => $totalThirdParty, 'color' => 'bg-purple-500', 'percent' => $total > 0 ? ($totalThirdParty / $total) * 100 : 0],
          ['name' => __('expenses.card_production'), 'value' => $totalProduction, 'color' => 'bg-orange-500', 'percent' => $total > 0 ? ($totalProduction / $total) * 100 : 0],
          ['name' => __('expenses.card_supplies'), 'value' => $totalSupplies, 'color' => 'bg-amber-500', 'percent' => $total > 0 ? ($totalSupplies / $total) * 100 : 0],
        ];
      @endphp

      <!-- Barra horizontal de distribución -->
      <div class="w-full h-8 bg-neutral-100 dark:bg-neutral-800 rounded-lg overflow-hidden flex mb-6">
        @foreach($percentages as $item)
          @if($item['percent'] > 0)
            <div class="{{ $item['color'] }} h-full"
                 style="width: {{ $item['percent'] }}%"
                 title="{{ $item['name'] }}: {{ number_format($item['percent'], 1) }}%">
            </div>
          @endif
        @endforeach
      </div>

      <!-- Leyenda -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach($percentages as $item)
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 rounded {{ $item['color'] }}"></div>
            <div class="flex-1">
              <p class="text-xs text-neutral-600 dark:text-neutral-400">{{ $item['name'] }}</p>
              <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">${{ number_format($item['value'], 0, ',', '.') }}</p>
              <p class="text-xs text-neutral-500">{{ number_format($item['percent'], 1) }}%</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <!-- Información útil -->
    <div class="grid gap-6 grid-cols-1 lg:grid-cols-2">
      <!-- Top Gastos más Altos -->
      <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('expenses.top_expenses_title') }}</h3>
          <svg class="h-5 w-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
          </svg>
        </div>
        @php
          $topExpenses = collect([
            ...$supplierExpenses->map(fn($e) => ['name' => $e->supplier_name, 'amount' => $e->annualized_cost, 'type' => __('expenses.tab_suppliers'), 'color' => 'blue', 'frequency' => $e->frequency]),
            ...$serviceExpenses->map(fn($e) => ['name' => $e->expense_name, 'amount' => $e->cost, 'type' => __('expenses.tab_services'), 'color' => 'green', 'frequency' => null]),
            ...$thirdPartyServices->map(fn($e) => ['name' => $e->service_name, 'amount' => $e->annualized_cost, 'type' => __('expenses.tab_third_party'), 'color' => 'purple', 'frequency' => $e->frequency]),
            ...$productionExpenses->map(fn($e) => ['name' => $e->expense_name, 'amount' => $e->total_cost, 'type' => __('expenses.tab_production'), 'color' => 'orange', 'frequency' => null]),
          ])->sortByDesc('amount')->take(8);
        @endphp

        @if($topExpenses->count() > 0)
          <div class="space-y-3">
            @foreach($topExpenses as $expense)
              <div class="flex items-center justify-between py-2 border-b border-neutral-100 dark:border-neutral-800 last:border-0">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                    {{ $expense['name'] }}
                  </p>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-{{ $expense['color'] }}-100 text-{{ $expense['color'] }}-700 dark:bg-{{ $expense['color'] }}-900/30 dark:text-{{ $expense['color'] }}-300">
                      {{ $expense['type'] }}
                    </span>
                    @if($expense['frequency'])
                      <span class="text-xs text-neutral-500 dark:text-neutral-400 capitalize">{{ $expense['frequency'] }}</span>
                    @endif
                  </div>
                </div>
                <div class="ml-4 text-right">
                  <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
                    ${{ number_format($expense['amount'], 0, ',', '.') }}
                  </p>
                  @if($expense['frequency'])
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('expenses.annual_label') }}</p>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-8">
            <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('expenses.no_expenses') }}</p>
          </div>
        @endif
      </div>

      <!-- Próximos Pagos y Resumen -->
      <div class="space-y-6">
        <!-- Próximos Pagos -->
        <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('expenses.upcoming_title') }}</h3>
            <svg class="h-5 w-5 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          @php
            $upcomingPayments = $thirdPartyServices
              ->filter(fn($s) => $s->next_payment_date)
              ->sortBy('next_payment_date')
              ->take(5);
          @endphp

          @if($upcomingPayments->count() > 0)
            <div class="space-y-3">
              @foreach($upcomingPayments as $payment)
                @php
                  $daysUntil = \Carbon\Carbon::parse($payment->next_payment_date)->diffInDays(now(), false);
                  $isOverdue = $daysUntil > 0;
                  $isUpcoming = $daysUntil >= -7 && $daysUntil <= 0;
                @endphp
                <div class="flex items-center justify-between py-2 border-b border-neutral-100 dark:border-neutral-800 last:border-0">
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                      {{ $payment->service_name }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                      {{ \Carbon\Carbon::parse($payment->next_payment_date)->format('d/m/Y') }}
                      @if($isOverdue)
                        <span class="text-rose-600 dark:text-rose-400">{{ __('expenses.overdue') }}</span>
                      @elseif($isUpcoming)
                        <span class="text-amber-600 dark:text-amber-400">{{ __('expenses.upcoming_soon') }}</span>
                      @endif
                    </p>
                  </div>
                  <div class="ml-4 text-right">
                    <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums">
                      ${{ number_format($payment->cost, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 capitalize">{{ $payment->frequency }}</p>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center py-6">
              <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('expenses.no_payments') }}</p>
            </div>
          @endif
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="rounded-lg border border-neutral-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-900">
          <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">{{ __('expenses.stats_title') }}</h3>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('expenses.stat_total_expenses') }}</p>
              <p class="text-xl font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums mt-1">
                {{ $supplierExpenses->count() + $serviceExpenses->count() + $thirdPartyServices->count() + $productionExpenses->count() }}
              </p>
            </div>
            <div>
              <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('expenses.stat_total_supplies') }}</p>
              <p class="text-xl font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums mt-1">
                {{ $supplies->count() }}
              </p>
            </div>
            <div>
              <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('expenses.stat_annual_cost') }}</p>
              <p class="text-xl font-semibold text-neutral-900 dark:text-neutral-100 tabular-nums mt-1">
                ${{ number_format($totalSupplier + $totalThirdParty, 0, ',', '.') }}
              </p>
            </div>
            <div>
              <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('expenses.stat_active') }}</p>
              <p class="text-xl font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums mt-1">
                {{ $supplierExpenses->where('is_active', true)->count() + $serviceExpenses->where('is_active', true)->count() + $thirdPartyServices->where('is_active', true)->count() }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Panel Proveedores -->
  <div x-show="activeTab === 'suppliers'" x-cloak>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('expenses.supplier_panel_title') }}</h2>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
          {{ __('expenses.supplier_panel_total') }} <span class="font-semibold">${{ number_format($totalSupplier, 2, ',', '.') }}</span>
        </p>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('suppliers.index') }}"
           class="inline-flex items-center px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-700 transition text-sm">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          {{ __('expenses.manage_suppliers') }}
        </a>
        <a href="{{ route('expenses.suppliers') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ __('expenses.new_expense') }}
        </a>
      </div>
    </div>

    @include('expenses.partials.suppliers-table')
  </div>

  <!-- Panel Servicios -->
  <div x-show="activeTab === 'services'" x-cloak>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('expenses.services_panel_title') }}</h2>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
          {{ __('expenses.services_panel_total') }} <span class="font-semibold">${{ number_format($totalService, 2, ',', '.') }}</span>
        </p>
      </div>
      <a href="{{ route('expenses.services') }}"
         class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('expenses.new_expense') }}
      </a>
    </div>

    @include('expenses.partials.services-table')
  </div>

  <!-- Panel Terceros -->
  <div x-show="activeTab === 'third-party'" x-cloak>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('expenses.third_panel_title') }}</h2>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
          {{ __('expenses.third_panel_total') }} <span class="font-semibold">${{ number_format($totalThirdParty, 2, ',', '.') }}</span>
        </p>
      </div>
      <a href="{{ route('expenses.third-party') }}"
         class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('expenses.new_service') }}
      </a>
    </div>

    @include('expenses.partials.third-party-table')
  </div>

  <!-- Panel Producción -->
  <div x-show="activeTab === 'production'" x-cloak>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('expenses.production_panel_title') }}</h2>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
          {{ __('expenses.production_panel_total') }} <span class="font-semibold">${{ number_format($totalProduction, 2, ',', '.') }}</span>
        </p>
      </div>
      <a href="{{ route('expenses.production') }}"
         class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('expenses.new_expense') }}
      </a>
    </div>

    @include('expenses.partials.production-table')
  </div>

  <!-- Panel Insumos -->
  <div x-show="activeTab === 'supplies'" x-cloak>
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('expenses.supplies_panel_title') }}</h2>
        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
          {{ __('expenses.supplies_panel_total') }} <span class="font-semibold">${{ number_format($totalSupplies, 2, ',', '.') }}</span>
        </p>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('suppliers.index') }}"
           class="inline-flex items-center px-4 py-2 bg-neutral-600 text-white rounded-lg hover:bg-neutral-700 transition text-sm">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          {{ __('expenses.manage_suppliers') }}
        </a>
        <a href="{{ route('expenses.supplies') }}"
           class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ __('expenses.new_supply') }}
        </a>
      </div>
    </div>

    @include('expenses.partials.supplies-table')
  </div>

</div>

<style>
  [x-cloak] { display: none !important; }
</style>
@endsection
