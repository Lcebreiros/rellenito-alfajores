@extends('layouts.app')

@push('styles')
<style>
@media (min-width: 1024px) {
  html, body { overflow: hidden !important; height: 100% !important; }
  .app-main { overflow: hidden !important; min-height: 0 !important; height: 100dvh !important; }
  .app-main > main { overflow: hidden !important; }
}
</style>
@endpush

@section('header')
<div class="flex items-center gap-3 min-w-0">
  <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-100 leading-tight transition-colors shrink-0">
    {{ __('orders.create_title') }}
  </h1>
  <div class="flex-1 min-w-0 flex items-center gap-2 justify-end">
    <livewire:cash-register :compact="true" :key="'cash-register'" />
    <livewire:payment-method-selector :compact="true" :key="'payment-method-selector'" />
  </div>
</div>
@endsection

@section('content')
<div
  class="max-w-screen-2xl mx-auto px-3 sm:px-6 lg:flex lg:flex-col lg:overflow-hidden lg:h-full"
  x-data="receiptUI()"
  x-init="init()"
>

  {{-- Mensajes de error/éxito --}}
  @if(session('ok') || $errors->any())
    <div class="lg:flex-shrink-0">
      @if(session('ok'))
        <div class="mb-3 rounded-lg border border-green-200 bg-green-50 text-green-800 px-3 py-2 text-sm
                    dark:border-green-700 dark:bg-green-900/20 dark:text-green-200">
          {!! session('ok') !!}
        </div>
      @endif
      @if($errors->any())
        <div class="mb-3 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm
                    dark:border-red-700 dark:bg-red-900/20 dark:text-red-200">
          @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
      @endif
    </div>
  @endif

  {{-- Layout responsive: en desktop ocupa el espacio restante sin scroll de página --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 min-w-0 lg:flex-1 lg:min-h-0 lg:overflow-hidden">

    {{-- IZQUIERDA: en desktop scrollea internamente --}}
    <section class="lg:col-span-8 min-w-0 lg:h-full lg:overflow-y-auto">
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900
                  min-h-[calc(100svh-9rem)] lg:min-h-0"
           x-data="{ activeTab: 'products' }">

        {{-- Fila superior: tabs izquierda + scanner derecha --}}
        <div class="flex items-center gap-3 mb-3">

          {{-- Selector Productos / Servicios --}}
          <div class="flex items-center gap-0.5 p-0.5 bg-neutral-100 dark:bg-neutral-800 rounded-lg shrink-0">
            <button
              @click="activeTab = 'products'"
              :class="activeTab === 'products'
                ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 shadow-sm'
                : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
              class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-150"
            >{{ __('orders.create.tab_products') }}</button>

            <button
              @click="activeTab = 'services'"
              :class="activeTab === 'services'
                ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 shadow-sm'
                : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200'"
              class="px-3 py-1.5 rounded-md text-xs font-semibold transition-all duration-150"
            >{{ __('orders.create.tab_services') }}</button>
          </div>

          {{-- Scanner compacto a la derecha --}}
          <div class="flex-1 flex justify-end">
            <div class="w-72">
              <livewire:pos-scanner :key="'pos-scanner'" />
            </div>
          </div>
        </div>

        {{-- Catálogo de productos --}}
        <div x-show="activeTab === 'products'">
          <livewire:product-catalog :key="'product-catalog'" />
        </div>

        {{-- Catálogo de servicios --}}
        <div x-show="activeTab === 'services'" x-cloak>
          <livewire:service-catalog :key="'service-catalog'" />
        </div>

      </div>
    </section>

    {{-- DERECHA: en desktop flex column fija, sin scroll de página --}}
    <aside class="lg:col-span-4 min-w-0 space-y-4 lg:space-y-0 lg:h-full lg:flex lg:flex-col lg:gap-3 lg:overflow-hidden">
      <div class="lg:flex-shrink-0">
        <livewire:schedule-order :key="'schedule-order'" />
      </div>

      {{-- OrderSidebar: flex-1, wrapper como flex column para que h-full resuelva bien --}}
      <div class="lg:flex-1 lg:min-h-0 lg:overflow-hidden lg:flex lg:flex-col">
        <livewire:order-sidebar :key="'order-sidebar'" />
      </div>
    </aside>

  </div>

  {{-- =================== TOAST "PEDIDO AGREGADO" =================== --}}
  <div
    x-show="toast.show"
    x-transition.opacity
    x-transition:enter.duration.200ms
    x-transition:leave.duration.200ms
    class="fixed inset-x-0 bottom-6 z-50 flex justify-center pointer-events-none"
    aria-live="polite"
  >
    <div class="pointer-events-auto w-full max-w-md rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 shadow-lg dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-100">
      <div class="flex items-start gap-3 p-3">
        <div class="mt-0.5 shrink-0">
          {{-- check ok --}}
          <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
               viewBox="0 0 24 24" fill="none">
            <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" fill="none"/>
          </svg>
        </div>
        <div class="flex-1">
          <div class="font-semibold">{{ __('orders.create.sale_added') }}</div>
          <div class="text-sm opacity-90">{{ __('orders.create.sale_created') }} <span class="font-semibold">#<span x-text="toast.orderId"></span></span>.</div>
          <div class="mt-2 flex gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
              @click="openReceipt(toast.orderId)"
            >
              {{ __('orders.create.view_receipt') }}
            </button>
            <a
              :href="receiptUrl(toast.orderId)"
              target="_blank"
              class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-semibold text-emerald-900 hover:bg-emerald-100 dark:text-emerald-100 dark:border-emerald-700 dark:hover:bg-emerald-900/50"
            >
              {{ __('orders.create.open_new_tab') }}
            </a>
          </div>
        </div>
        <button class="mt-0.5 rounded-lg p-1 text-emerald-900/70 hover:bg-emerald-100 dark:text-emerald-100/70 dark:hover:bg-emerald-900/40" @click="toast.show=false" aria-label="Cerrar">
          ✕
        </button>
      </div>
    </div>
  </div>

  {{-- =================== MODAL: COMPROBANTE (solo ticket, sin marco) =================== --}}
  <div
    x-show="modal.open"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center"
    aria-modal="true"
    role="dialog"
  >
    <div class="absolute inset-0 bg-black/50" @click="closeModal()"></div>

    {{-- Contenedor suelto --}}
    <div class="relative z-10 w-[96vw] max-w-[480px]">
      {{-- Controles flotantes (fuera del ticket) --}}
      <div class="absolute -top-12 right-0 flex gap-2">
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg bg-white/90 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow
                 hover:bg-white dark:bg-neutral-900/90 dark:text-neutral-200 dark:hover:bg-neutral-900"
          @click="closeModal()"
        >
          {{ __('orders.create.back') }}
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow
                 hover:bg-indigo-700"
          @click="downloadEmbedded()"
        >
          {{ __('orders.create.download_pdf') }}
        </button>
      </div>

      {{-- Ticket embebido, sin layout ni controles internos --}}
      <iframe
        id="receiptFrame"
        x-show="modal.orderId"
        :src="embedUrl(modal.orderId)"
        class="h-[82vh] w-full rounded-xl border-0 bg-transparent"
        loading="lazy"
        referrerpolicy="no-referrer"
      ></iframe>
    </div>
  </div>
</div>

{{-- =================== SCRIPT Alpine helpers =================== --}}
<script>
  function receiptUI(){
    return {
      toast: { show:false, orderId:null, timer:null },
      modal: { open:false, orderId:null },

      init(){
        // Escuchar eventos desde Livewire
        window.addEventListener('order-confirmed', (e)=> this.onConfirmed(e.detail));
        window.addEventListener('order:confirmed', (e)=> this.onConfirmed(e.detail)); // alias
        if (window.Livewire && Livewire.on) {
          Livewire.on('order-confirmed', (payload)=> this.onConfirmed(payload));
        }
        // ESC para cerrar
        window.addEventListener('keydown', (ev)=>{
          if(ev.key === 'Escape' && this.modal.open) this.closeModal();
        });
      },

      onConfirmed(detail){
        const id = detail?.orderId ?? detail?.id ?? detail;
        if (!id) return;
        this.toast.orderId = id;
        this.toast.show = true;
        clearTimeout(this.toast.timer);
        this.toast.timer = setTimeout(()=> this.toast.show = false, 6000);
      },

      openReceipt(id){
        this.modal.orderId = id;
        this.modal.open = true;
        this.toast.show = false;
      },

      closeModal(){
        this.modal.open = false;
        this.modal.orderId = null;
      },

      receiptUrl(id){
        return `${@json(url('/orders'))}/${id}/ticket`;
      },

      // pedimos el ticket sin layout y sin controles internos
      embedUrl(id){
        return `${@json(url('/orders'))}/${id}/ticket?embed=1&controls=0`;
      },

      // descargar PDF del ticket embebido (llama a la función del iframe)
      downloadEmbedded(){
        const f = document.getElementById('receiptFrame');
        if (!f || !f.contentWindow) return;
        try {
          f.contentWindow.downloadTicketPdf?.(this.modal.orderId);
        } catch (e) {
          // Fallback: abrir en pestaña nueva
          window.open(this.receiptUrl(this.modal.orderId), '_blank');
        }
      }
    }
  }
</script>
@endsection
