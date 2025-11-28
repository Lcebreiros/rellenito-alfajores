@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Nueva Factura</h1>
@endsection

@section('header_actions')
  <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition-all duration-150 active:scale-[0.98]">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    Volver
  </a>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6" x-data="invoiceForm()">
  {{-- Error messages --}}
  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
    </div>
  @endif

  <form action="{{ route('invoices.store') }}" method="POST">
    @csrf

    {{-- Client and invoice data --}}
    <div class="container-glass shadow-sm overflow-hidden mb-6">
      <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Datos de la factura</h2>
      </div>

      <div class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          {{-- Voucher type --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Tipo de comprobante <span class="text-rose-500">*</span>
            </label>
            <select name="voucher_type" required class="input-enhanced w-full">
              <option value="">Seleccionar...</option>
              <optgroup label="Facturas">
                <option value="FC-A" {{ old('voucher_type') === 'FC-A' ? 'selected' : '' }}>Factura A</option>
                <option value="FC-B" {{ old('voucher_type') === 'FC-B' ? 'selected' : '' }}>Factura B</option>
                <option value="FC-C" {{ old('voucher_type') === 'FC-C' ? 'selected' : '' }}>Factura C</option>
              </optgroup>
              <optgroup label="Notas de Crédito">
                <option value="NC-A" {{ old('voucher_type') === 'NC-A' ? 'selected' : '' }}>Nota de Crédito A</option>
                <option value="NC-B" {{ old('voucher_type') === 'NC-B' ? 'selected' : '' }}>Nota de Crédito B</option>
                <option value="NC-C" {{ old('voucher_type') === 'NC-C' ? 'selected' : '' }}>Nota de Crédito C</option>
              </optgroup>
              <optgroup label="Notas de Débito">
                <option value="ND-A" {{ old('voucher_type') === 'ND-A' ? 'selected' : '' }}>Nota de Débito A</option>
                <option value="ND-B" {{ old('voucher_type') === 'ND-B' ? 'selected' : '' }}>Nota de Débito B</option>
                <option value="ND-C" {{ old('voucher_type') === 'ND-C' ? 'selected' : '' }}>Nota de Débito C</option>
              </optgroup>
            </select>
          </div>

          {{-- Invoice date --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Fecha de emisión <span class="text-rose-500">*</span>
            </label>
            <input type="date"
                   name="invoice_date"
                   value="{{ old('invoice_date', date('Y-m-d')) }}"
                   required
                   class="input-enhanced w-full">
          </div>

          {{-- Client select --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Cliente (opcional)
            </label>
            <select name="client_id"
                    x-model="selectedClient"
                    @change="loadClientData()"
                    class="input-enhanced w-full">
              <option value="">Manual...</option>
              @foreach($clients as $client)
                <option value="{{ $client->id }}"
                        data-name="{{ $client->name }}"
                        data-cuit="{{ $client->document_number }}"
                        data-address="{{ $client->address }}">
                  {{ $client->name }} {{ $client->document_number ? '- ' . $client->document_number : '' }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Order select --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Pedido (opcional)
            </label>
            <select name="order_id" class="input-enhanced w-full">
              <option value="">Sin pedido asociado</option>
              @foreach($orders as $order)
                <option value="{{ $order->id }}">
                  Pedido #{{ $order->id }} - {{ $order->customer_name }} - ${{ number_format($order->total, 2) }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Client data --}}
        <div class="pt-6 border-t border-neutral-200 dark:border-neutral-700">
          <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Datos del cliente</h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Nombre / Razón Social <span class="text-rose-500">*</span>
              </label>
              <input type="text"
                     name="client_name"
                     x-model="clientName"
                     value="{{ old('client_name') }}"
                     required
                     class="input-enhanced w-full">
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                CUIT / DNI
              </label>
              <input type="text"
                     name="client_cuit"
                     x-model="clientCuit"
                     value="{{ old('client_cuit') }}"
                     placeholder="XX-XXXXXXXX-X"
                     maxlength="13"
                     class="input-enhanced w-full">
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Condición frente al IVA <span class="text-rose-500">*</span>
              </label>
              <select name="client_tax_condition" required class="input-enhanced w-full">
                <option value="Consumidor Final" {{ old('client_tax_condition') === 'Consumidor Final' ? 'selected' : '' }}>Consumidor Final</option>
                <option value="IVA Responsable Inscripto" {{ old('client_tax_condition') === 'IVA Responsable Inscripto' ? 'selected' : '' }}>IVA Responsable Inscripto</option>
                <option value="Monotributo" {{ old('client_tax_condition') === 'Monotributo' ? 'selected' : '' }}>Monotributo</option>
                <option value="Exento" {{ old('client_tax_condition') === 'Exento' ? 'selected' : '' }}>Exento</option>
                <option value="No Responsable" {{ old('client_tax_condition') === 'No Responsable' ? 'selected' : '' }}>No Responsable</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                Dirección
              </label>
              <input type="text"
                     name="client_address"
                     x-model="clientAddress"
                     value="{{ old('client_address') }}"
                     class="input-enhanced w-full">
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Invoice items --}}
    <div class="container-glass shadow-sm overflow-hidden mb-6">
      <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Items</h2>
        <button type="button"
                @click="addItem()"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
          </svg>
          Agregar item
        </button>
      </div>

      <div class="p-6">
        <div class="space-y-4">
          <template x-for="(item, index) in items" :key="index">
            <div class="panel-glass p-4 relative">
              <button type="button"
                      @click="removeItem(index)"
                      class="absolute top-2 right-2 text-rose-600 hover:text-rose-700 dark:text-rose-400">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Descripción <span class="text-rose-500">*</span>
                  </label>
                  <input type="text"
                         :name="`items[${index}][description]`"
                         x-model="item.description"
                         required
                         class="input-enhanced w-full text-sm">
                </div>

                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Cantidad <span class="text-rose-500">*</span>
                  </label>
                  <input type="number"
                         :name="`items[${index}][quantity]`"
                         x-model="item.quantity"
                         @input="calculateItem(index)"
                         min="0.01"
                         step="0.01"
                         required
                         class="input-enhanced w-full text-sm">
                </div>

                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Precio Unit. <span class="text-rose-500">*</span>
                  </label>
                  <input type="number"
                         :name="`items[${index}][unit_price]`"
                         x-model="item.unit_price"
                         @input="calculateItem(index)"
                         min="0"
                         step="0.01"
                         required
                         class="input-enhanced w-full text-sm">
                </div>

                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    IVA %
                  </label>
                  <select :name="`items[${index}][tax_rate]`"
                          x-model="item.tax_rate"
                          @change="calculateItem(index)"
                          class="input-enhanced w-full text-sm">
                    <option value="0">0%</option>
                    <option value="10.5">10.5%</option>
                    <option value="21">21%</option>
                    <option value="27">27%</option>
                  </select>
                </div>

                <div class="md:col-span-2">
                  <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Subtotal
                  </label>
                  <div class="input-enhanced w-full text-sm bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 font-medium" x-text="`$ ${item.subtotal.toFixed(2)}`"></div>
                </div>
              </div>
            </div>
          </template>

          <div x-show="items.length === 0" class="text-center py-8 text-neutral-500 dark:text-neutral-400">
            <svg class="mx-auto h-12 w-12 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            No hay items. Haz clic en "Agregar item" para comenzar.
          </div>
        </div>

        {{-- Totals --}}
        <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700">
          <div class="flex justify-end">
            <div class="w-full md:w-80 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-neutral-700 dark:text-neutral-300">Subtotal:</span>
                <span class="font-medium text-neutral-900 dark:text-neutral-100 tabular-nums" x-text="`$ ${subtotal.toFixed(2)}`"></span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-neutral-700 dark:text-neutral-300">IVA:</span>
                <span class="font-medium text-neutral-900 dark:text-neutral-100 tabular-nums" x-text="`$ ${tax.toFixed(2)}`"></span>
              </div>
              <div class="flex justify-between text-lg font-bold pt-2 border-t border-neutral-200 dark:border-neutral-700">
                <span class="text-neutral-900 dark:text-neutral-100">Total:</span>
                <span class="text-indigo-600 dark:text-indigo-400 tabular-nums" x-text="`$ ${total.toFixed(2)}`"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Submit buttons --}}
    <div class="flex items-center justify-end gap-3 mb-8">
      <a href="{{ route('invoices.index') }}"
         class="px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition-all duration-150">
        Cancelar
      </a>
      <button type="submit"
              :disabled="items.length === 0"
              :class="items.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        Crear factura
      </button>
    </div>
  </form>
</div>

<script>
function invoiceForm() {
  return {
    selectedClient: '',
    clientName: '',
    clientCuit: '',
    clientAddress: '',
    items: [],

    get subtotal() {
      return this.items.reduce((sum, item) => sum + item.subtotal, 0);
    },

    get tax() {
      return this.items.reduce((sum, item) => {
        const itemSubtotal = parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0);
        return sum + (itemSubtotal * parseFloat(item.tax_rate || 0) / 100);
      }, 0);
    },

    get total() {
      return this.subtotal + this.tax;
    },

    addItem() {
      this.items.push({
        description: '',
        quantity: 1,
        unit_price: 0,
        tax_rate: 21,
        subtotal: 0
      });
    },

    removeItem(index) {
      this.items.splice(index, 1);
    },

    calculateItem(index) {
      const item = this.items[index];
      item.subtotal = parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0);
    },

    loadClientData() {
      if (!this.selectedClient) {
        this.clientName = '';
        this.clientCuit = '';
        this.clientAddress = '';
        return;
      }

      const option = document.querySelector(`option[value="${this.selectedClient}"]`);
      if (option) {
        this.clientName = option.dataset.name || '';
        this.clientCuit = option.dataset.cuit || '';
        this.clientAddress = option.dataset.address || '';
      }
    }
  }
}
</script>
@endsection
