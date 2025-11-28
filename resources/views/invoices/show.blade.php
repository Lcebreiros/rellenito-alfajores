@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
    Factura {{ $invoice->full_number }}
  </h1>
@endsection

@section('header_actions')
  <div class="flex items-center gap-2">
    @if($invoice->status === 'draft')
      <a href="{{ route('invoices.edit', $invoice) }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-sm font-medium transition-all duration-150 active:scale-[0.98]">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Editar
      </a>
    @endif

    @if($invoice->status === 'approved' && $invoice->pdf_path)
      <a href="{{ route('invoices.download-pdf', $invoice) }}"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition-all duration-150 active:scale-[0.98]">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        Descargar PDF
      </a>
    @endif

    <a href="{{ route('invoices.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition-all duration-150 active:scale-[0.98]">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
      </svg>
      Volver
    </a>
  </div>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">
  {{-- Success/Error messages --}}
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

  {{-- Status banner --}}
  @if($invoice->status === 'approved')
    <div class="mb-6 panel-glass p-4 border-l-4 border-emerald-500">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
          <p class="font-semibold text-emerald-900 dark:text-emerald-100">Factura aprobada por ARCA</p>
          <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-1">
            CAE: <span class="font-mono">{{ $invoice->cae }}</span> |
            Vencimiento: {{ $invoice->cae_expiration ? \Carbon\Carbon::parse($invoice->cae_expiration)->format('d/m/Y') : 'N/A' }}
          </p>
        </div>
      </div>
    </div>
  @elseif($invoice->status === 'rejected')
    <div class="mb-6 panel-glass p-4 border-l-4 border-rose-500">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-rose-600 dark:text-rose-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
          <p class="font-semibold text-rose-900 dark:text-rose-100">Factura rechazada por ARCA</p>
          @if($invoice->arca_response)
            <p class="text-sm text-rose-700 dark:text-rose-300 mt-1">Revise los detalles de la respuesta de ARCA</p>
          @endif
        </div>
      </div>
    </div>
  @elseif($invoice->status === 'draft')
    <div class="mb-6 panel-glass p-4 border-l-4 border-amber-500">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        <div class="flex-1">
          <p class="font-semibold text-amber-900 dark:text-amber-100">Borrador</p>
          <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">Esta factura no ha sido enviada a ARCA aún</p>
        </div>
      </div>
    </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main invoice details --}}
    <div class="lg:col-span-2 space-y-6">
      {{-- Invoice header --}}
      <div class="container-glass shadow-sm overflow-hidden">
        <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Información del comprobante</h2>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium
              {{ str_starts_with($invoice->voucher_type, 'FC') ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
              {{ str_starts_with($invoice->voucher_type, 'NC') ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : '' }}
              {{ str_starts_with($invoice->voucher_type, 'ND') ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}">
              {{ $invoice->voucher_type }}
            </span>
          </div>
        </div>

        <div class="p-6 grid grid-cols-2 gap-6">
          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Número</p>
            <p class="text-lg font-bold text-neutral-900 dark:text-neutral-100 font-mono">{{ $invoice->full_number }}</p>
          </div>

          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Fecha de emisión</p>
            <p class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
              {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
            </p>
          </div>

          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Punto de venta</p>
            <p class="text-base font-medium text-neutral-900 dark:text-neutral-100">{{ $invoice->sale_point }}</p>
          </div>

          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Estado</p>
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
            @endif
          </div>
        </div>
      </div>

      {{-- Client information --}}
      <div class="container-glass shadow-sm overflow-hidden">
        <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
          <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Cliente</h2>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Nombre / Razón Social</p>
            <p class="text-base font-semibold text-neutral-900 dark:text-neutral-100">{{ $invoice->client_name }}</p>
          </div>

          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">CUIT / DNI</p>
            <p class="text-base font-medium text-neutral-900 dark:text-neutral-100 font-mono">{{ $invoice->client_cuit ?: '—' }}</p>
          </div>

          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Condición IVA</p>
            <p class="text-base font-medium text-neutral-900 dark:text-neutral-100">{{ $invoice->client_tax_condition }}</p>
          </div>

          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Dirección</p>
            <p class="text-base font-medium text-neutral-900 dark:text-neutral-100">{{ $invoice->client_address ?: '—' }}</p>
          </div>
        </div>
      </div>

      {{-- Items --}}
      <div class="container-glass shadow-sm overflow-hidden">
        <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
          <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Items</h2>
        </div>

        <div class="overflow-x-auto">
          <table class="table-enhanced w-full text-sm">
            <thead class="bg-neutral-50 dark:bg-neutral-800/40">
              <tr class="text-xs uppercase tracking-wide text-neutral-600 dark:text-neutral-400">
                <th class="px-4 py-3 text-left">Descripción</th>
                <th class="px-4 py-3 text-right">Cant.</th>
                <th class="px-4 py-3 text-right">P. Unit.</th>
                <th class="px-4 py-3 text-right">IVA %</th>
                <th class="px-4 py-3 text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
              @foreach($invoice->items as $item)
                <tr>
                  <td class="px-4 py-3 text-neutral-900 dark:text-neutral-100">{{ $item->description }}</td>
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
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
      {{-- ARCA Info --}}
      @if($invoice->cae)
        <div class="container-glass shadow-sm overflow-hidden">
          <div class="bg-emerald-100/70 dark:bg-emerald-900/30 px-6 py-4 border-b border-emerald-200 dark:border-emerald-800">
            <h3 class="text-base font-semibold text-emerald-900 dark:text-emerald-100">Datos ARCA</h3>
          </div>

          <div class="p-6 space-y-4">
            <div>
              <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">CAE</p>
              <p class="text-sm font-mono font-medium text-neutral-900 dark:text-neutral-100 break-all">
                {{ $invoice->cae }}
              </p>
            </div>

            @if($invoice->cae_expiration)
              <div>
                <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Vencimiento CAE</p>
                <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                  {{ \Carbon\Carbon::parse($invoice->cae_expiration)->format('d/m/Y') }}
                </p>
              </div>
            @endif
          </div>
        </div>
      @endif

      {{-- Actions --}}
      <div class="container-glass shadow-sm overflow-hidden">
        <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
          <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Acciones</h3>
        </div>

        <div class="p-6 space-y-3">
          @if($invoice->status === 'draft')
            <form action="{{ route('invoices.send-to-arca', $invoice) }}" method="POST" onsubmit="return confirm('¿Está seguro de enviar esta factura a ARCA? No podrá modificarla después.')">
              @csrf
              <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Enviar a ARCA
              </button>
            </form>
          @endif

          @if($invoice->status !== 'approved')
            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta factura?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-rose-300 dark:border-rose-700 text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 text-sm font-medium transition-all duration-150">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Eliminar
              </button>
            </form>
          @endif
        </div>
      </div>

      {{-- Metadata --}}
      <div class="container-glass shadow-sm overflow-hidden">
        <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
          <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Información adicional</h3>
        </div>

        <div class="p-6 space-y-4 text-sm">
          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Creada</p>
            <p class="text-neutral-900 dark:text-neutral-100">
              {{ $invoice->created_at->format('d/m/Y H:i') }}
            </p>
          </div>

          <div>
            <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Última modificación</p>
            <p class="text-neutral-900 dark:text-neutral-100">
              {{ $invoice->updated_at->format('d/m/Y H:i') }}
            </p>
          </div>

          @if($invoice->order)
            <div>
              <p class="text-xs text-neutral-600 dark:text-neutral-400 uppercase tracking-wide mb-1">Pedido asociado</p>
              <a href="{{ route('orders.show', $invoice->order) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                Pedido #{{ $invoice->order->id }}
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
