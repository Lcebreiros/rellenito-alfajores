@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between gap-3 min-w-0 w-full">
  <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-100 leading-tight">
    {{ __('purchases.title') }}
  </h1>
  <button
    type="button"
    x-data
    @click="$dispatch('open-purchase-form')"
    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors"
  >
    <x-heroicon-o-plus class="w-4 h-4" />
    {{ __('purchases.btn_new') }}
  </button>
</div>
@endsection

@section('content')
<div
  class="max-w-4xl mx-auto px-3 sm:px-6 py-4 space-y-5"
  x-data="purchasesApp()"
  x-init="init()"
  @open-purchase-form.window="openForm()"
  @keydown.escape.window="closeForm()"
>

  {{-- Flash messages --}}
  @if(session('ok'))
    <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-2.5 text-sm dark:border-green-700 dark:bg-green-900/20 dark:text-green-200">
      {!! session('ok') !!}
    </div>
  @endif
  @if($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 text-red-800 px-4 py-2.5 text-sm dark:border-red-700 dark:bg-red-900/20 dark:text-red-200">
      @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
  @endif

  {{-- Summary cards --}}
  <div class="grid grid-cols-3 gap-3">
    <div class="rounded-xl border border-slate-200 bg-white dark:border-neutral-700 dark:bg-neutral-900 p-4 space-y-1">
      <div class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ __('purchases.total_month') }}</div>
      <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
        ${{ number_format($totalPurchases + $totalExpenses, 2, ',', '.') }}
      </div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white dark:border-neutral-700 dark:bg-neutral-900 p-4 space-y-1">
      <div class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ __('purchases.card_supplies') }}</div>
      <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
        ${{ number_format($totalPurchases, 2, ',', '.') }}
      </div>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white dark:border-neutral-700 dark:bg-neutral-900 p-4 space-y-1">
      <div class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ __('purchases.card_expenses') }}</div>
      <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">
        ${{ number_format($totalExpenses, 2, ',', '.') }}
      </div>
    </div>
  </div>

  {{-- Month selector --}}
  @if($months->isNotEmpty())
    <div class="flex items-center gap-2 flex-wrap">
      @foreach($months as $m)
        @php
          $label = \Carbon\Carbon::createFromFormat('Y-m', $m)->translatedFormat('F Y');
        @endphp
        <a
          href="{{ route('purchases.index', ['month' => $m]) }}"
          wire:navigate
          class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors
                 {{ $m === $currentMonth
                   ? 'bg-indigo-600 text-white border-indigo-600'
                   : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 border-neutral-200 dark:border-neutral-700 hover:border-indigo-400' }}"
        >{{ $label }}</a>
      @endforeach
    </div>
  @endif

  {{-- Item list --}}
  @if($grouped->isEmpty())
    <div class="rounded-xl border-2 border-dashed border-slate-200 dark:border-neutral-700 p-10 text-center space-y-3">
      <div class="mx-auto w-12 h-12 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
        <x-heroicon-o-shopping-cart class="w-6 h-6 text-neutral-400" />
      </div>
      <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">{{ __('purchases.empty') }}</p>
      <button
        type="button"
        @click="openForm()"
        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 transition-colors"
      >
        <x-heroicon-o-plus class="w-3.5 h-3.5" />
        {{ __('purchases.btn_new') }}
      </button>
    </div>
  @else
    <div class="rounded-xl border border-slate-200 bg-white dark:border-neutral-700 dark:bg-neutral-900 divide-y divide-slate-100 dark:divide-neutral-800">
      @foreach($grouped as $date => $items)
        @php
          $dateLabel = \Carbon\Carbon::parse($date)->translatedFormat('d \d\e F');
        @endphp
        <div>
          {{-- Date group header --}}
          <div class="px-4 py-2 bg-slate-50 dark:bg-neutral-800/60 flex items-center justify-between">
            <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">{{ $dateLabel }}</span>
            <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">
              ${{ number_format($items->sum('amount'), 2, ',', '.') }}
            </span>
          </div>
          {{-- Items in this date --}}
          @foreach($items as $item)
            <div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50/60 dark:hover:bg-neutral-800/40 transition-colors group">
              {{-- Type icon --}}
              <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                          {{ $item['type'] === 'supply'
                             ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
                             : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' }}">
                @if($item['type'] === 'supply')
                  <x-heroicon-o-beaker class="w-4 h-4" />
                @else
                  <x-heroicon-o-document-text class="w-4 h-4" />
                @endif
              </div>

              {{-- Info --}}
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ $item['name'] }}</span>
                  <span class="shrink-0 text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded-full
                               {{ $item['type'] === 'supply'
                                  ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                  : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                    {{ $item['type'] === 'supply' ? __('purchases.badge_supply') : __('purchases.badge_expense') }}
                  </span>
                </div>
                <div class="flex items-center gap-2 mt-0.5">
                  <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $item['detail'] }}</span>
                  @if($item['supplier'])
                    <span class="text-neutral-300 dark:text-neutral-600">·</span>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $item['supplier'] }}</span>
                  @endif
                </div>
              </div>

              {{-- Amount --}}
              <div class="shrink-0 text-right">
                <span class="text-sm font-bold text-neutral-900 dark:text-neutral-100">
                  ${{ number_format($item['amount'], 2, ',', '.') }}
                </span>
              </div>

              {{-- Delete --}}
              <div class="shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                @if($item['type'] === 'supply')
                  <form method="POST" action="{{ route('purchases.supply.destroy', $item['id']) }}"
                        onsubmit="return confirm('{{ __('purchases.confirm_delete') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            title="{{ __('purchases.delete') }}">
                      <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                  </form>
                @else
                  <form method="POST" action="{{ route('purchases.expense.destroy', $item['id']) }}"
                        onsubmit="return confirm('{{ __('purchases.confirm_delete') }}')">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                            title="{{ __('purchases.delete') }}">
                      <x-heroicon-o-trash class="w-4 h-4" />
                    </button>
                  </form>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  @endif

</div>

{{-- ===================== SLIDE-OVER FORM ===================== --}}
<div
  x-show="open"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="fixed inset-0 z-40 flex"
  aria-modal="true"
>
  {{-- Backdrop --}}
  <div class="absolute inset-0 bg-black/40" @click="closeForm()"></div>

  {{-- Panel --}}
  <div
    class="relative ml-auto w-full max-w-md bg-white dark:bg-neutral-900 shadow-2xl flex flex-col h-full overflow-y-auto"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    @click.stop
  >
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-neutral-700 shrink-0">
      <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100">{{ __('purchases.form_title') }}</h2>
      <button type="button" @click="closeForm()" class="p-1.5 rounded-lg text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800">
        <x-heroicon-o-x-mark class="w-5 h-5" />
      </button>
    </div>

    {{-- Type tabs --}}
    <div class="flex border-b border-slate-200 dark:border-neutral-700 shrink-0">
      <button
        type="button"
        @click="tab = 'supply'"
        :class="tab === 'supply' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700'"
        class="flex-1 py-3 text-sm font-semibold transition-colors"
      >
        <x-heroicon-o-beaker class="w-4 h-4 inline mr-1" />
        {{ __('purchases.tab_supply') }}
      </button>
      <button
        type="button"
        @click="tab = 'expense'"
        :class="tab === 'expense' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700'"
        class="flex-1 py-3 text-sm font-semibold transition-colors"
      >
        <x-heroicon-o-document-text class="w-4 h-4 inline mr-1" />
        {{ __('purchases.tab_expense') }}
      </button>
    </div>

    {{-- ===== TAB: SUPPLY PURCHASE ===== --}}
    <div x-show="tab === 'supply'" class="flex-1 p-5">
      <form method="POST" action="{{ route('purchases.supply.store') }}" class="space-y-4">
        @csrf

        {{-- Supply --}}
        <div>
          <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('purchases.field_supply') }} <span class="text-red-500">*</span>
          </label>
          @if($supplies->isEmpty())
            <p class="text-xs text-amber-600 dark:text-amber-400">
              {!! __('purchases.no_supplies_hint', ['url' => route('calculator.show')]) !!}
            </p>
          @else
            <select
              name="supply_id"
              x-model="selectedSupplyId"
              @change="onSupplyChange()"
              required
              class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">{{ __('purchases.select_supply') }}</option>
              @foreach($supplies as $s)
                <option value="{{ $s->id }}" data-base="{{ $s->base_unit }}">{{ $s->name }} ({{ $s->base_unit }})</option>
              @endforeach
            </select>
          @endif
        </div>

        {{-- Qty + Unit --}}
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
              {{ __('purchases.field_qty') }} <span class="text-red-500">*</span>
            </label>
            <input
              type="number"
              name="qty"
              step="0.001"
              min="0.001"
              required
              placeholder="0"
              class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
          <div>
            <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
              {{ __('purchases.field_unit') }} <span class="text-red-500">*</span>
            </label>
            <select
              name="unit"
              x-model="supplyUnit"
              required
              class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <template x-if="compatibleUnits.length === 0">
                <option value="">—</option>
              </template>
              <template x-for="u in compatibleUnits" :key="u.value">
                <option :value="u.value" x-text="u.label"></option>
              </template>
            </select>
          </div>
        </div>

        {{-- Total cost --}}
        <div>
          <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('purchases.field_total_cost') }} <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500 text-sm font-medium">$</span>
            <input
              type="number"
              name="total_cost"
              step="0.01"
              min="0.01"
              required
              placeholder="0,00"
              class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm pl-7 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
        </div>

        {{-- Date --}}
        <div>
          <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('purchases.field_date') }}
          </label>
          <input
            type="date"
            name="purchased_at"
            value="{{ now()->toDateString() }}"
            max="{{ now()->toDateString() }}"
            class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div class="pt-2 flex gap-2">
          <button
            type="submit"
            class="flex-1 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors"
          >{{ __('purchases.btn_save') }}</button>
          <button
            type="button"
            @click="closeForm()"
            class="rounded-lg border border-slate-200 dark:border-neutral-700 px-4 py-2.5 text-sm font-semibold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors"
          >{{ __('purchases.btn_cancel') }}</button>
        </div>
      </form>
    </div>

    {{-- ===== TAB: GENERAL EXPENSE ===== --}}
    <div x-show="tab === 'expense'" x-cloak class="flex-1 p-5">
      <form method="POST" action="{{ route('purchases.expense.store') }}" class="space-y-4">
        @csrf

        {{-- Description --}}
        <div>
          <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('purchases.field_description') }} <span class="text-red-500">*</span>
          </label>
          <input
            type="text"
            name="description"
            required
            maxlength="255"
            placeholder="{{ __('purchases.desc_placeholder') }}"
            class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        {{-- Category --}}
        <div>
          <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('purchases.field_category') }}
          </label>
          <select
            name="category"
            class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            @foreach(\App\Models\SupplierExpense::CATEGORIES as $key => $label)
              <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        {{-- Amount --}}
        <div>
          <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('purchases.field_amount') }} <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-500 text-sm font-medium">$</span>
            <input
              type="number"
              name="cost"
              step="0.01"
              min="0.01"
              required
              placeholder="0,00"
              class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm pl-7 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>
        </div>

        {{-- Supplier (optional) --}}
        @if($suppliers->isNotEmpty())
          <div>
            <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
              {{ __('purchases.field_supplier') }}
            </label>
            <select
              name="supplier_id"
              class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">{{ __('purchases.no_supplier') }}</option>
              @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
              @endforeach
            </select>
          </div>
        @endif

        {{-- Date --}}
        <div>
          <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('purchases.field_date') }}
          </label>
          <input
            type="date"
            name="expense_date"
            value="{{ now()->toDateString() }}"
            max="{{ now()->toDateString() }}"
            class="w-full rounded-lg border border-slate-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />
        </div>

        <div class="pt-2 flex gap-2">
          <button
            type="submit"
            class="flex-1 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors"
          >{{ __('purchases.btn_save') }}</button>
          <button
            type="button"
            @click="closeForm()"
            class="rounded-lg border border-slate-200 dark:border-neutral-700 px-4 py-2.5 text-sm font-semibold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition-colors"
          >{{ __('purchases.btn_cancel') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function purchasesApp() {
  const UNITS = {
    g:  [{ value: 'g', label: 'Gramos (g)' }, { value: 'kg', label: 'Kilogramos (kg)' }],
    ml: [{ value: 'ml', label: 'Mililitros (ml)' }, { value: 'l', label: 'Litros (l)' }, { value: 'cm3', label: 'Centímetros cúbicos (cm3)' }],
    u:  [{ value: 'u', label: 'Unidades (u)' }],
  };

  return {
    open: false,
    tab: 'supply',
    selectedSupplyId: '',
    supplyUnit: '',
    compatibleUnits: [],

    init() {
      @if($errors->any())
        this.open = true;
      @endif
    },

    openForm() { this.open = true; },
    closeForm() { this.open = false; },

    onSupplyChange() {
      const sel = document.querySelector('select[name="supply_id"]');
      const opt = sel?.options[sel.selectedIndex];
      const base = opt?.dataset?.base ?? '';
      this.compatibleUnits = UNITS[base] ?? [];
      this.supplyUnit = this.compatibleUnits[0]?.value ?? '';
    },
  };
}
</script>
@endsection
