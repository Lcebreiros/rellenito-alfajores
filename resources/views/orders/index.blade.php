@extends('layouts.app')

@section('header')
<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
  <h1 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2.5">
    <x-svg-icon name="document" size="5" class="text-indigo-500 shrink-0" />
    {{ __('orders.title') }}
  </h1>

  <div class="flex flex-wrap items-center gap-2">

    {{-- Búsqueda compacta --}}
    <form method="GET" class="relative">
      @foreach(['status','period','from','to','client','client_id','sort'] as $keep)
        @if(request($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif
      @endforeach
      <div class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center">
        <svg class="w-3.5 h-3.5 text-neutral-400" viewBox="0 0 24 24" fill="none">
          <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
          <path d="M21 21l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <input name="q" value="{{ request('q') }}"
             placeholder="{{ __('orders.search_placeholder') }}"
             autocomplete="off"
             class="w-44 pl-8 pr-3 py-1.5 text-sm rounded-lg
                    border border-neutral-200 dark:border-neutral-700
                    bg-white dark:bg-neutral-900
                    text-neutral-700 dark:text-neutral-200
                    placeholder-neutral-400 dark:placeholder-neutral-500
                    focus:outline-none focus:ring-2 focus:ring-indigo-400/40 focus:border-indigo-400
                    focus:w-56 transition-all duration-200">
    </form>

    {{-- Importar CSV --}}
    <form method="POST" action="{{ route('orders.import-csv') }}" enctype="multipart/form-data">
      @csrf
      <label title="{{ __('orders.import_csv') }}"
             class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg cursor-pointer transition-colors
                    border border-neutral-200 dark:border-neutral-700
                    bg-white dark:bg-neutral-900
                    text-neutral-600 dark:text-neutral-300
                    hover:bg-neutral-50 dark:hover:bg-neutral-800">
        <x-svg-icon name="document" size="4" />
        <span class="hidden md:inline text-xs font-medium">{{ __('orders.import_csv') }}</span>
        <input type="file" name="csv" accept=".csv,text/csv" class="hidden" onchange="this.form.submit()" />
      </label>
    </form>

    {{-- Descargar Reporte --}}
    <button type="button" onclick="window.dispatchEvent(new Event('open-download'))"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all duration-150 active:scale-[0.98]
                   bg-emerald-600 hover:bg-emerald-700 text-white">
      <x-svg-icon name="download" size="4" />
      <span class="hidden md:inline text-xs font-medium">{{ __('orders.download') }}</span>
    </button>

    {{-- Nueva Venta --}}
    <livewire:order-quick-modal />
  </div>
</div>
@endsection

@section('content')
@php
  $currentPeriod = request('period', '');
  $periods = [
    ''             => __('orders.periods.all'),
    'today'        => __('orders.periods.today'),
    'yesterday'    => __('orders.periods.yesterday'),
    'this_week'    => __('orders.periods.this_week'),
    'last_week'    => __('orders.periods.last_week'),
    'last_7_days'  => __('orders.periods.last_7_days'),
    'this_month'   => __('orders.periods.this_month'),
    'last_month'   => __('orders.periods.last_month'),
    'last_30_days' => __('orders.periods.last_30_days'),
  ];
  $statusBadge = function ($s) {
    $key = ($s instanceof \BackedEnum) ? $s->value : (($s instanceof \UnitEnum) ? $s->name : (is_string($s) ? $s : (string) ($s ?? '')));
    return match ($key) {
      'completed' => ['text' => __('orders.status_completed'), 'cls' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
      'canceled'  => ['text' => __('orders.status_canceled'),  'cls' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'],
      'draft'     => ['text' => __('orders.status_draft'),     'cls' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
      default     => ['text' => ucfirst($key ?: '—'),          'cls' => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-200'],
    };
  };
  $receiptRoute    = \Illuminate\Support\Facades\Route::has('orders.ticket');
  $fmt             = fn ($n) => '$' . number_format((float) $n, 2, ',', '.');
  $hasAdvFilters   = request()->anyFilled(['status', 'from', 'to', 'client', 'client_id']);
  $hasActiveFilters = request()->anyFilled(['status', 'period', 'from', 'to', 'q', 'client', 'client_id']);
  $colCount        = 9 + (!empty($isCompany) ? 1 : 0) + (!empty($isMaster) ? 1 : 0);
@endphp

<div class="max-w-screen-2xl mx-auto px-3 sm:px-6"
     x-data="ordersPage()"
     @open-download.window="showDownload = true"
     @keydown.escape.window="showDownload = false">

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-4 flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm
                bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/60
                text-emerald-800 dark:text-emerald-200">
      <x-svg-icon name="check" size="4" class="shrink-0 text-emerald-500" />
      {{ session('ok') }}
    </div>
  @endif
  @if($errors->any())
    <div class="mb-4 flex items-start gap-3 px-4 py-2.5 rounded-xl text-sm
                bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/60
                text-rose-800 dark:text-rose-200">
      <x-svg-icon name="exclamation" size="4" class="shrink-0 mt-0.5 text-rose-500" />
      <div>@foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
    </div>
  @endif
  @if(session('import_errors'))
    <div class="mb-4 px-4 py-2.5 rounded-xl text-sm
                bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/60
                text-amber-800 dark:text-amber-200">
      <div class="font-semibold mb-1">{{ __('orders.import_errors') }}</div>
      <ul class="list-disc ml-4 space-y-0.5">
        @foreach(session('import_errors') as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- ── Barra de filtros ─────────────────────────────────────── --}}
  <div class="mb-4">

    {{-- Períodos + toggle avanzado --}}
    <div class="flex flex-wrap items-center gap-1.5">
      <span class="text-xs font-medium text-neutral-400 dark:text-neutral-500 mr-0.5">
        {{ __('orders.period_label') }}
      </span>

      @foreach($periods as $period => $label)
        <a href="{{ request()->fullUrlWithQuery(['period' => $period ?: null, 'from' => null, 'to' => null, 'page' => null]) }}"
           class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                  {{ $currentPeriod === $period
                     ? 'bg-indigo-600 text-white shadow-sm'
                     : 'text-neutral-500 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-700 dark:hover:text-neutral-200' }}">
          {{ $label }}
        </a>
      @endforeach

      <div class="ml-auto flex items-center gap-1.5">
        @if($hasActiveFilters)
          <a href="{{ route('orders.index') }}"
             class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                    text-neutral-500 dark:text-neutral-400 hover:text-rose-600 dark:hover:text-rose-400
                    hover:bg-rose-50 dark:hover:bg-rose-900/20">
            <x-svg-icon name="x" size="3" /> {{ __('orders.clear_all') }}
          </a>
        @endif

        <button type="button" @click="showAdvanced = !showAdvanced"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-colors border"
                :class="showAdvanced
                  ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-200 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300'
                  : 'border-neutral-200 dark:border-neutral-700 text-neutral-500 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800'">
          <x-svg-icon name="filter" size="3" />
          {{ __('orders.advanced_filters') }}
          <span class="inline-flex transition-transform duration-200" :class="showAdvanced ? 'rotate-180' : ''">
            <x-svg-icon name="chevron-down" size="3" />
          </span>
        </button>
      </div>
    </div>

    {{-- Chips de filtros activos --}}
    @if($hasActiveFilters)
      @php
        $filterLabels = [
          'q'         => __('orders.search_placeholder'),
          'status'    => __('orders.filter_labels.status'),
          'period'    => __('orders.filter_labels.period'),
          'from'      => __('orders.filter_labels.from'),
          'to'        => __('orders.filter_labels.to'),
          'client'    => __('orders.filter_labels.client'),
          'client_id' => __('orders.filter_labels.client_id'),
        ];
      @endphp
      <div class="mt-2 flex flex-wrap gap-1.5">
        @foreach($filterLabels as $key => $label)
          @if(request($key))
            <span class="inline-flex items-center gap-1 pl-2.5 pr-1 py-0.5 rounded-full text-[11px] font-medium
                         bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300
                         border border-neutral-200 dark:border-neutral-700">
              {{ $label }}: <b class="font-semibold ml-0.5">{{ $key === 'period' ? ($periods[request('period')] ?? request('period')) : request($key) }}</b>
              <a href="{{ request()->fullUrlWithQuery([$key => null]) }}"
                 class="ml-1 p-0.5 rounded-full hover:bg-neutral-200 dark:hover:bg-neutral-700 transition-colors">
                <x-svg-icon name="x" size="3" />
              </a>
            </span>
          @endif
        @endforeach
      </div>
    @endif

    {{-- Panel de filtros avanzados --}}
    <div x-show="showAdvanced"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="mt-3 p-4 rounded-xl bg-white dark:bg-neutral-900
                border border-neutral-100 dark:border-neutral-800 shadow-sm">
      <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
        @if(request('q'))      <input type="hidden" name="q"      value="{{ request('q') }}"> @endif
        @if(request('period')) <input type="hidden" name="period" value="{{ request('period') }}"> @endif
        @if(request('sort'))   <input type="hidden" name="sort"   value="{{ request('sort') }}"> @endif
        <div>
          <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1.5">{{ __('orders.filter_status') }}</label>
          <select name="status" class="input-enhanced w-full py-2 text-sm">
            <option value="">{{ __('orders.filter_all') }}</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('orders.filter_completed') }}</option>
            <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>{{ __('orders.filter_draft') }}</option>
            <option value="canceled"  {{ request('status') === 'canceled'  ? 'selected' : '' }}>{{ __('orders.filter_canceled') }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1.5">{{ __('orders.filter_from') }}</label>
          <input type="date" name="from" value="{{ request('from') }}" class="input-enhanced w-full py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1.5">{{ __('orders.filter_to') }}</label>
          <input type="date" name="to" value="{{ request('to') }}" class="input-enhanced w-full py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 mb-1.5">{{ __('orders.filter_client') }}</label>
          <input type="text" name="client" value="{{ request('client') }}"
                 placeholder="{{ __('orders.filter_client_ph') }}" class="input-enhanced w-full py-2 text-sm">
        </div>
        <div class="sm:col-span-2 lg:col-span-4 flex gap-2 pt-1">
          <button type="submit"
                  class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium
                         bg-indigo-600 hover:bg-indigo-700 text-white transition-all duration-150 active:scale-[0.98]">
            <x-svg-icon name="search" size="4" /> {{ __('orders.filter_apply') }}
          </button>
          <a href="{{ route('orders.index') }}"
             class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm
                    border border-neutral-200 dark:border-neutral-700
                    text-neutral-600 dark:text-neutral-300
                    hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors">
            <x-svg-icon name="trash" size="4" /> {{ __('orders.filter_reset') }}
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Meta: resultados + orden ─────────────────────────────── --}}
  @if($orders->total() > 0)
    <div class="mb-2.5 flex items-center justify-between px-0.5">
      <span class="text-xs text-neutral-400 dark:text-neutral-500">
        {{ $orders->firstItem() }}–{{ $orders->lastItem() }} de {{ $orders->total() }}
      </span>
      <select onchange="window.location.href = this.value"
              class="text-xs bg-transparent border-0 text-neutral-400 dark:text-neutral-500
                     hover:text-neutral-600 dark:hover:text-neutral-300
                     focus:outline-none focus:ring-0 cursor-pointer py-1 appearance-none">
        <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"     {{ request('sort', 'newest') === 'newest'     ? 'selected' : '' }}>{{ __('orders.sort_newest') }}</option>
        <option value="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}"     {{ request('sort') === 'oldest'               ? 'selected' : '' }}>{{ __('orders.sort_oldest') }}</option>
        <option value="{{ request()->fullUrlWithQuery(['sort' => 'total_desc']) }}" {{ request('sort') === 'total_desc'           ? 'selected' : '' }}>{{ __('orders.sort_total_desc') }}</option>
        <option value="{{ request()->fullUrlWithQuery(['sort' => 'total_asc']) }}"  {{ request('sort') === 'total_asc'            ? 'selected' : '' }}>{{ __('orders.sort_total_asc') }}</option>
      </select>
    </div>
  @endif

  {{-- ── Tabla ─────────────────────────────────────────────────── --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-100 dark:border-neutral-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[900px] text-sm">

        <thead>
          <tr class="border-b border-neutral-100 dark:border-neutral-800 bg-neutral-50/80 dark:bg-neutral-800/50">
            <th class="w-10 px-3 py-3 text-center">
              <input type="checkbox"
                     :checked="isAllSelected"
                     :indeterminate="isSomeSelected"
                     @change="toggleAll()"
                     class="w-4 h-4 rounded border-neutral-300 dark:border-neutral-600
                            text-indigo-600 bg-white dark:bg-neutral-800 focus:ring-0 cursor-pointer">
            </th>
            <th class="px-4 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('orders.col_sale') }}</th>
            <th class="px-3 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('orders.col_client') }}</th>
            <th class="px-3 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('orders.col_date') }}</th>
            @if(!empty($isCompany))
              <th class="px-3 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('orders.col_branch') }}</th>
            @endif
            @if(!empty($isMaster))
              <th class="px-3 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('orders.col_user') }}</th>
            @endif
            <th class="px-3 py-3 text-center text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('orders.col_items') }}</th>
            <th class="px-3 py-3 text-right text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('orders.col_total') }}</th>
            <th class="px-4 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('orders.col_payment') }}</th>
            <th class="px-3 py-3 text-left text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 uppercase tracking-wider">{{ __('orders.col_status') }}</th>
            <th class="px-4 py-3 w-20"></th>
          </tr>
        </thead>

        <tbody class="divide-y divide-neutral-50 dark:divide-neutral-800/60">
          @forelse($orders as $o)
            @php $badge = $statusBadge($o->status); @endphp
            <tr class="group transition-colors duration-100"
                :class="isSelected({{ $o->id }})
                  ? 'bg-indigo-50/50 dark:bg-indigo-900/10'
                  : 'hover:bg-neutral-50/60 dark:hover:bg-neutral-800/30'">

              <td class="w-10 px-3 py-3.5 text-center">
                <input type="checkbox"
                       :checked="isSelected({{ $o->id }})"
                       @change="toggle({{ $o->id }})"
                       class="w-4 h-4 rounded border-neutral-300 dark:border-neutral-600
                              text-indigo-600 bg-white dark:bg-neutral-800 focus:ring-0 cursor-pointer">
              </td>

              <td class="px-4 py-3.5">
                <span class="font-mono font-semibold text-neutral-800 dark:text-neutral-100">
                  #{{ $o->order_number ?? $o->id }}
                </span>
              </td>

              <td class="px-3 py-3.5 max-w-[180px]">
                <div class="font-medium text-neutral-800 dark:text-neutral-100 truncate">
                  {{ optional($o->client)->name ?? __('orders.no_client') }}
                </div>
                @if(!empty($o->note))
                  <div class="text-xs text-neutral-400 dark:text-neutral-500 truncate mt-0.5">{{ $o->note }}</div>
                @endif
              </td>

              <td class="px-3 py-3.5 whitespace-nowrap">
                <div class="text-neutral-700 dark:text-neutral-200">{{ $o->created_at?->format('d/m/Y') }}</div>
                <div class="text-xs text-neutral-400 dark:text-neutral-500">{{ $o->created_at?->format('H:i') }}</div>
              </td>

              @if(!empty($isCompany))
                <td class="px-3 py-3.5 text-neutral-600 dark:text-neutral-300">
                  {{ optional($o->branch)->name ?? __('orders.no_branch') }}
                </td>
              @endif
              @if(!empty($isMaster))
                <td class="px-3 py-3.5">
                  <div class="text-neutral-700 dark:text-neutral-200">#{{ $o->user_id }}</div>
                  <div class="text-xs text-neutral-400 dark:text-neutral-500">{{ $o->user?->name ?? 'N/D' }}</div>
                </td>
              @endif

              <td class="px-3 py-3.5 text-center">
                <span class="inline-flex items-center justify-center min-w-[1.75rem] h-6 px-2 rounded-md
                             bg-neutral-100 dark:bg-neutral-800 text-xs font-semibold
                             text-neutral-600 dark:text-neutral-300">
                  {{ (int) ($o->items_qty ?? 0) }}
                </span>
              </td>

              <td class="px-3 py-3.5 text-right">
                <span class="font-semibold tabular-nums text-neutral-900 dark:text-neutral-100">{{ $fmt($o->total) }}</span>
              </td>

              <td class="px-4 py-3.5">
                @if($o->paymentMethods && $o->paymentMethods->isNotEmpty())
                  <div class="flex items-center gap-1">
                    @foreach($o->paymentMethods->take(2) as $pm)
                      @if($pm->hasLogo())
                        <img src="{{ asset('images/' . $pm->getLogo()) }}"
                             alt="{{ $pm->name }}" title="{{ $pm->name }}"
                             class="h-7 w-auto object-contain rounded" style="max-width:38px">
                      @else
                        <div class="w-7 h-7 flex items-center justify-center rounded-lg
                                    bg-neutral-100 dark:bg-neutral-800" title="{{ $pm->name }}">
                          <x-dynamic-component :component="'heroicon-o-' . $pm->getIcon()"
                                               class="w-4 h-4 text-neutral-500 dark:text-neutral-400" />
                        </div>
                      @endif
                    @endforeach
                    @if($o->paymentMethods->count() > 2)
                      <span class="text-xs text-neutral-400 dark:text-neutral-500 pl-0.5">
                        +{{ $o->paymentMethods->count() - 2 }}
                      </span>
                    @endif
                  </div>
                @else
                  <span class="text-neutral-300 dark:text-neutral-600">—</span>
                @endif
              </td>

              <td class="px-3 py-3.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium {{ $badge['cls'] }}">
                  <span class="w-1.5 h-1.5 rounded-full bg-current shrink-0"></span>
                  {{ $badge['text'] }}
                </span>
              </td>

              <td class="px-4 py-3.5">
                <div class="flex items-center justify-end gap-0.5">
                  <a href="{{ route('orders.show', $o) }}" title="{{ __('orders.view') }}"
                     class="p-1.5 rounded-lg text-neutral-400 transition-colors
                            hover:text-indigo-600 hover:bg-indigo-50
                            dark:hover:text-indigo-400 dark:hover:bg-indigo-900/30">
                    <x-svg-icon name="eye" size="4" />
                  </a>
                  @if($receiptRoute)
                    <a href="{{ route('orders.ticket', $o) }}" title="{{ __('orders.receipt') }}"
                       class="p-1.5 rounded-lg text-neutral-400 transition-colors
                              hover:text-emerald-600 hover:bg-emerald-50
                              dark:hover:text-emerald-400 dark:hover:bg-emerald-900/30">
                      <x-svg-icon name="document" size="4" />
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ $colCount }}" class="px-6 py-20 text-center">
                <div class="flex flex-col items-center max-w-xs mx-auto">
                  <div class="w-14 h-14 rounded-2xl bg-neutral-100 dark:bg-neutral-800
                              flex items-center justify-center mb-4">
                    <x-svg-icon name="search" size="6" class="text-neutral-300 dark:text-neutral-600" />
                  </div>
                  <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-1">{{ __('orders.empty_title') }}</h3>
                  <p class="text-xs text-neutral-400 dark:text-neutral-500 mb-5">{{ __('orders.empty_desc') }}</p>
                  <div class="flex gap-2">
                    <a href="{{ route('orders.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm rounded-lg transition-colors
                              bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300
                              hover:bg-neutral-200 dark:hover:bg-neutral-700">
                      <x-svg-icon name="x" size="4" /> {{ __('orders.clear_filters') }}
                    </a>
                    <a href="{{ route('orders.create') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-white rounded-lg
                              bg-indigo-600 hover:bg-indigo-700 transition-all active:scale-[0.98]">
                      <x-svg-icon name="plus" size="4" /> {{ __('orders.create_btn') }}
                    </a>
                  </div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Paginación --}}
  @if($orders->hasPages())
    <div class="mt-5">{{ $orders->withQueryString()->links() }}</div>
  @endif

  {{-- ── Toolbar flotante de selección ──────────────────────── --}}
  <div x-show="count > 0"
       x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 translate-y-3"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 translate-y-3"
       class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50
              flex items-center gap-3 px-4 py-2.5 rounded-2xl
              bg-white dark:bg-neutral-900
              shadow-xl shadow-neutral-900/10 dark:shadow-black/50
              border border-neutral-200/80 dark:border-neutral-700/80">
    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-200"
          x-text="`${count} seleccionada${count !== 1 ? 's' : ''}`"></span>
    <div class="w-px h-4 bg-neutral-200 dark:bg-neutral-700 shrink-0"></div>
    <button @click="deleteSelected()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                   bg-rose-600 hover:bg-rose-700 text-white">
      <x-svg-icon name="trash" size="4" /> Eliminar
    </button>
    <button @click="clearSelection()"
            class="inline-flex items-center gap-1 px-2 py-1.5 rounded-lg text-xs font-medium transition-colors
                   text-neutral-400 dark:text-neutral-500 hover:text-neutral-600 dark:hover:text-neutral-300
                   hover:bg-neutral-100 dark:hover:bg-neutral-800">
      <x-svg-icon name="x" size="4" />
    </button>
  </div>

  {{-- ── Modal de descarga ───────────────────────────────────── --}}
  <div x-show="showDownload"
       x-cloak
       @click.self="showDownload = false"
       x-transition:enter="transition ease-out duration-150"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-100"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
    <div x-show="showDownload"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.stop
         class="w-full max-w-sm bg-white dark:bg-neutral-900 rounded-2xl
                border border-neutral-100 dark:border-neutral-800
                shadow-2xl shadow-black/20">
      <div class="flex items-center justify-between px-5 pt-5 pb-4
                  border-b border-neutral-100 dark:border-neutral-800">
        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
          <x-svg-icon name="download" size="4" class="text-emerald-500" />
          {{ __('orders.download_modal_title') }}
        </h3>
        <button @click="showDownload = false"
                class="p-1 rounded-lg text-neutral-400 transition-colors
                       hover:text-neutral-600 dark:hover:text-neutral-300
                       hover:bg-neutral-100 dark:hover:bg-neutral-800">
          <x-svg-icon name="x" size="4" />
        </button>
      </div>

      <div class="p-5 space-y-2">
        <a href="{{ route('orders.download-report', array_merge(request()->query(), ['format' => 'csv'])) }}"
           class="flex items-center gap-3 p-3.5 rounded-xl border transition-all group
                  border-neutral-100 dark:border-neutral-800
                  hover:border-neutral-200 dark:hover:border-neutral-700
                  hover:bg-neutral-50 dark:hover:bg-neutral-800/60">
          <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
            <x-svg-icon name="document" size="5" class="text-emerald-600 dark:text-emerald-400" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ __('orders.csv_format') }}</div>
            <div class="text-xs text-neutral-400 dark:text-neutral-500">{{ __('orders.csv_subtitle') }}</div>
          </div>
          <x-svg-icon name="download" size="4" class="text-neutral-300 dark:text-neutral-600 group-hover:text-neutral-500 transition-colors shrink-0" />
        </a>

        <a href="{{ route('orders.download-report', array_merge(request()->query(), ['format' => 'excel'])) }}"
           class="flex items-center gap-3 p-3.5 rounded-xl border transition-all group
                  border-neutral-100 dark:border-neutral-800
                  hover:border-neutral-200 dark:hover:border-neutral-700
                  hover:bg-neutral-50 dark:hover:bg-neutral-800/60">
          <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
            <x-svg-icon name="document" size="5" class="text-blue-600 dark:text-blue-400" />
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ __('orders.excel_format') }}</div>
            <div class="text-xs text-neutral-400 dark:text-neutral-500">{{ __('orders.excel_subtitle') }}</div>
          </div>
          <x-svg-icon name="download" size="4" class="text-neutral-300 dark:text-neutral-600 group-hover:text-neutral-500 transition-colors shrink-0" />
        </a>

        <div class="flex items-center gap-2 p-3 rounded-xl text-xs
                    bg-neutral-50 dark:bg-neutral-800/60
                    text-neutral-500 dark:text-neutral-400">
          <x-svg-icon name="info" size="4" class="shrink-0 text-neutral-400" />
          {{ __('orders.will_include', ['count' => $orders->total()]) }}
        </div>
      </div>
    </div>
  </div>

</div>

<script>
function ordersPage() {
  return {
    showDownload: false,
    showAdvanced: {{ $hasAdvFilters ? 'true' : 'false' }},
    selected: [],
    allIds: @js(collect($orders->items())->pluck('id')->values()->toArray()),

    get count()          { return this.selected.length; },
    get isAllSelected()  { return this.allIds.length > 0 && this.selected.length === this.allIds.length; },
    get isSomeSelected() { return this.selected.length > 0 && this.selected.length < this.allIds.length; },

    isSelected(id) { return this.selected.includes(id); },

    toggle(id) {
      const idx = this.selected.indexOf(id);
      if (idx === -1) this.selected.push(id);
      else this.selected.splice(idx, 1);
    },

    toggleAll() {
      this.selected = this.isAllSelected ? [] : [...this.allIds];
    },

    clearSelection() { this.selected = []; },

    async deleteSelected() {
      const n   = this.count;
      const msg = n === 1
        ? @json(__('orders.confirm_delete'))
        : @json(__('orders.confirm_delete_many')).replace(':count', n);
      if (!confirm(msg)) return;

      const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
      try {
        const res  = await fetch('{{ route("orders.bulk-delete") }}', {
          method:  'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept':       'application/json',
          },
          body: JSON.stringify({ ids: this.selected }),
        });
        const data = await res.json();
        if (res.ok && data.success) window.location.reload();
        else alert(data.message || 'Error al eliminar');
      } catch {
        alert('Error de conexión');
      }
    },
  };
}
</script>
@endsection
