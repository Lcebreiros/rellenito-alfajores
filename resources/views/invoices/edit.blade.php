@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Editar Factura {{ $invoice->full_number }}</h1>
@endsection

@section('header_actions')
  <a href="{{ route('invoices.show', $invoice) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition-all duration-150 active:scale-[0.98]">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    Volver
  </a>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">
  {{-- Error messages --}}
  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
    </div>
  @endif

  {{-- Warning --}}
  <div class="mb-6 panel-glass p-4 border-l-4 border-amber-500">
    <div class="flex gap-3">
      <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
      <div class="text-sm text-neutral-700 dark:text-neutral-300">
        <p class="font-semibold mb-1">Solo se pueden editar datos básicos</p>
        <p>Los items de la factura no se pueden modificar una vez creados. Si necesita cambiar los items, deberá crear una nueva factura.</p>
      </div>
    </div>
  </div>

  <form action="{{ route('invoices.update', $invoice) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Client data --}}
    <div class="container-glass shadow-sm overflow-hidden mb-6">
      <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Datos del cliente y factura</h2>
      </div>

      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          {{-- Client name --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Nombre / Razón Social <span class="text-rose-500">*</span>
            </label>
            <input type="text"
                   name="client_name"
                   value="{{ old('client_name', $invoice->client_name) }}"
                   required
                   class="input-enhanced w-full">
          </div>

          {{-- Client CUIT --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              CUIT / DNI
            </label>
            <input type="text"
                   name="client_cuit"
                   value="{{ old('client_cuit', $invoice->client_cuit) }}"
                   placeholder="XX-XXXXXXXX-X"
                   maxlength="13"
                   class="input-enhanced w-full">
          </div>

          {{-- Client address --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Dirección
            </label>
            <input type="text"
                   name="client_address"
                   value="{{ old('client_address', $invoice->client_address) }}"
                   class="input-enhanced w-full">
          </div>

          {{-- Invoice date --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Fecha de emisión <span class="text-rose-500">*</span>
            </label>
            <input type="date"
                   name="invoice_date"
                   value="{{ old('invoice_date', $invoice->invoice_date) }}"
                   required
                   class="input-enhanced w-full">
          </div>
        </div>
      </div>
    </div>

    {{-- Current items (readonly) --}}
    <div class="container-glass shadow-sm overflow-hidden mb-6">
      <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Items (no editables)</h2>
      </div>

      <div class="overflow-x-auto">
        <table class="table-enhanced w-full text-sm">
          <thead class="bg-neutral-50 dark:bg-neutral-800/40">
            <tr class="text-xs uppercase tracking-wide text-neutral-600 dark:text-neutral-400">
              <th class="px-4 py-3 text-left">Descripción</th>
              <th class="px-4 py-3 text-right">Cantidad</th>
              <th class="px-4 py-3 text-right">P. Unitario</th>
              <th class="px-4 py-3 text-right">IVA %</th>
              <th class="px-4 py-3 text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @foreach($invoice->items as $item)
              <tr class="bg-neutral-50/50 dark:bg-neutral-800/20">
                <td class="px-4 py-3 text-neutral-700 dark:text-neutral-300">{{ $item->description }}</td>
                <td class="px-4 py-3 text-right text-neutral-700 dark:text-neutral-300 tabular-nums">
                  {{ number_format($item->quantity, 2, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-right text-neutral-700 dark:text-neutral-300 tabular-nums">
                  $ {{ number_format($item->unit_price, 2, ',', '.') }}
                </td>
                <td class="px-4 py-3 text-right text-neutral-700 dark:text-neutral-300 tabular-nums">
                  {{ number_format($item->tax_rate, 1) }}%
                </td>
                <td class="px-4 py-3 text-right font-medium text-neutral-900 dark:text-neutral-100 tabular-nums">
                  $ {{ number_format($item->total, 2, ',', '.') }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="bg-neutral-50 dark:bg-neutral-800/40 px-6 py-4 border-t border-neutral-200 dark:border-neutral-700">
        <div class="flex justify-end">
          <div class="w-full sm:w-80 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-neutral-700 dark:text-neutral-300">Subtotal:</span>
              <span class="font-medium text-neutral-900 dark:text-neutral-100 tabular-nums">
                $ {{ number_format($invoice->subtotal, 2, ',', '.') }}
              </span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-neutral-700 dark:text-neutral-300">IVA:</span>
              <span class="font-medium text-neutral-900 dark:text-neutral-100 tabular-nums">
                $ {{ number_format($invoice->tax_amount, 2, ',', '.') }}
              </span>
            </div>
            <div class="flex justify-between text-xl font-bold pt-2 border-t border-neutral-200 dark:border-neutral-700">
              <span class="text-neutral-900 dark:text-neutral-100">Total:</span>
              <span class="text-indigo-600 dark:text-indigo-400 tabular-nums">
                $ {{ number_format($invoice->total, 2, ',', '.') }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Submit buttons --}}
    <div class="flex items-center justify-end gap-3 mb-8">
      <a href="{{ route('invoices.show', $invoice) }}"
         class="px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition-all duration-150">
        Cancelar
      </a>
      <button type="submit"
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        Guardar cambios
      </button>
    </div>
  </form>
</div>
@endsection
