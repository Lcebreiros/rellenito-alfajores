@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Facturas Electrónicas</h1>
@endsection

@section('header_actions')
  <div class="flex items-center gap-2">
    <a href="{{ route('invoices.configuration') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition-all duration-150 active:scale-[0.98]">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>
      <span class="hidden sm:inline">Configuración</span>
    </a>
    <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
      </svg>
      Nueva factura
    </a>
  </div>
@endsection

@section('content')
<div class="max-w-screen-2xl mx-auto px-3 sm:px-6">
  {{-- Success message --}}
  @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300 flex items-center gap-2">
      <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
      </svg>
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300 flex items-center gap-2">
      <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
      {{ session('error') }}
    </div>
  @endif

  {{-- Stats cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card-glass p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide">Total Facturas</p>
          <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">{{ $invoices->total() }}</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
          <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
      </div>
    </div>

    <div class="card-glass p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide">Aprobadas</p>
          <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $invoices->where('status', 'approved')->count() }}</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
          <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>

    <div class="card-glass p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide">Borradores</p>
          <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $invoices->where('status', 'draft')->count() }}</p>
        </div>
        <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
          <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
        </div>
      </div>
    </div>

    <div class="card-glass p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide">Este mes</p>
          <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
            $ {{ number_format($invoices->where('invoice_date', '>=', now()->startOfMonth())->sum('total'), 2, ',', '.') }}
          </p>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
          <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- Invoices table or empty state --}}
  @if($invoices->count())
    <div class="container-glass shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-enhanced w-full min-w-[1000px] text-sm">
          <thead class="bg-neutral-100/70 dark:bg-neutral-800/60">
            <tr class="text-xs uppercase tracking-wide text-neutral-600 dark:text-neutral-300">
              <th class="px-3 py-3 text-left">Número</th>
              <th class="px-3 py-3 text-left">Tipo</th>
              <th class="px-3 py-3 text-left">Fecha</th>
              <th class="px-3 py-3 text-left">Cliente</th>
              <th class="px-3 py-3 text-right">Subtotal</th>
              <th class="px-3 py-3 text-right">IVA</th>
              <th class="px-3 py-3 text-right">Total</th>
              <th class="px-3 py-3 text-center">Estado</th>
              <th class="px-3 py-3 text-center">CAE</th>
              <th class="px-3 py-3 text-left">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
            @foreach($invoices as $invoice)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
                <td class="px-3 py-3 font-medium text-neutral-900 dark:text-neutral-100 tabular-nums">
                  {{ $invoice->full_number }}
                </td>
                <td class="px-3 py-3">
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                    {{ str_starts_with($invoice->voucher_type, 'FC') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                    {{ str_starts_with($invoice->voucher_type, 'NC') ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : '' }}
                    {{ str_starts_with($invoice->voucher_type, 'ND') ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}">
                    {{ $invoice->voucher_type }}
                  </span>
                </td>
                <td class="px-3 py-3 text-neutral-700 dark:text-neutral-300">
                  {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                </td>
                <td class="px-3 py-3 text-neutral-700 dark:text-neutral-300">
                  {{ $invoice->client_name }}
                </td>
                <td class="px-3 py-3 text-right text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                  $ {{ number_format($invoice->subtotal, 2, ',', '.') }}
                </td>
                <td class="px-3 py-3 text-right text-neutral-700 dark:text-neutral-300 tabular-nums">
                  $ {{ number_format($invoice->tax_amount, 2, ',', '.') }}
                </td>
                <td class="px-3 py-3 text-right text-neutral-900 dark:text-neutral-100 font-bold tabular-nums">
                  $ {{ number_format($invoice->total, 2, ',', '.') }}
                </td>
                <td class="px-3 py-3 text-center">
                  @if($invoice->status === 'approved')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                      Aprobada
                    </span>
                  @elseif($invoice->status === 'draft')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                      Borrador
                    </span>
                  @elseif($invoice->status === 'pending')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                      Pendiente
                    </span>
                  @elseif($invoice->status === 'rejected')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">
                      Rechazada
                    </span>
                  @elseif($invoice->status === 'cancelled')
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                      Anulada
                    </span>
                  @endif
                </td>
                <td class="px-3 py-3 text-center text-xs tabular-nums text-neutral-600 dark:text-neutral-400">
                  {{ $invoice->cae ?? '—' }}
                </td>
                <td class="px-3 py-3">
                  <div class="flex items-center gap-2">
                    <a href="{{ route('invoices.show', $invoice) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-300 px-2.5 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800 transition-colors">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      Ver
                    </a>
                    @if($invoice->status === 'draft')
                      <a href="{{ route('invoices.edit', $invoice) }}"
                         class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-300 px-2.5 py-1.5 text-xs text-indigo-700 hover:bg-indigo-50 dark:border-indigo-700 dark:text-indigo-400 dark:hover:bg-indigo-900/20 transition-colors">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Editar
                      </a>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- Pagination --}}
    @if($invoices->hasPages())
      <div class="mt-6">
        {{ $invoices->links() }}
      </div>
    @endif
  @else
    {{-- Empty state --}}
    <div class="container-glass shadow-sm text-center py-16">
      <svg class="mx-auto h-16 w-16 text-neutral-400 dark:text-neutral-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="mt-4 text-lg font-semibold text-neutral-900 dark:text-neutral-100">No hay facturas</h3>
      <p class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">Comienza creando tu primera factura electrónica</p>
      <div class="mt-6">
        <a href="{{ route('invoices.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          Nueva factura
        </a>
      </div>
    </div>
  @endif
</div>
@endsection
