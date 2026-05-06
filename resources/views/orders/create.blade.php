@extends('layouts.app')

@push('styles')
<style>
@media (min-width: 1024px) {
  html, body { overflow: hidden !important; height: 100% !important; }

  .app-main {
    height: 100dvh !important;
    overflow: hidden !important;
  }

  /* header no participa en el reparto de altura — solo ocupa lo que necesita */
  .app-main > header { flex-shrink: 0 !important; }

  /* main = columna flex que ocupa el resto después del header */
  .app-main > main {
    flex: 1 1 0% !important;
    min-height: 0 !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    padding: 0.5rem !important;
  }

  /* ── cadena de layout del POS ──────────────────────────────────── */
  /* Cada nodo especifica display, dirección y flex para no depender  */
  /* de que Tailwind genere valores arbitrarios como [flex:2_1_0%].  */

  .pos-wrap         { display: flex; flex-direction: column; flex: 1 1 0%; min-height: 0; overflow: hidden; }
  .pos-cols         { display: flex; flex-direction: row;    flex: 1 1 0%; min-height: 0; overflow: hidden; gap: 1rem; }
  .pos-left         { display: flex; flex-direction: column; flex: 2 1 0%; min-height: 0; overflow: hidden; }
  .pos-right        { display: flex; flex-direction: column; flex: 1 1 0%; min-height: 0; overflow: hidden; gap: 0.75rem; }
  .pos-sidebar-wrap { display: flex; flex-direction: column; flex: 1 1 0%; min-height: 0; overflow: hidden; }
}
</style>
@endpush

@section('header')
<div class="flex flex-col gap-1.5 min-w-0 w-full lg:flex-row lg:items-center lg:gap-3">

  {{-- Fila 1: título + caja registradora --}}
  <div class="flex items-center gap-3 min-w-0 flex-1">
    <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-100 leading-tight transition-colors shrink-0">
      {{ __('orders.create_title') }}
    </h1>
    <div class="ml-auto lg:ml-0 shrink-0">
      <livewire:cash-register :compact="true" :key="'cash-register'" />
    </div>
  </div>

  {{-- Fila 2 en móvil / inline en desktop: medios de pago a todo el ancho con scroll lateral --}}
  <div class="min-w-0 lg:shrink-0">
    <livewire:payment-method-selector :compact="true" :key="'payment-method-selector'" />
  </div>

</div>
@endsection

@section('content')
<div
  class="pos-wrap px-3 sm:px-4 lg:px-0"
  x-data="receiptUI()"
  x-init="init()"
>

  {{-- Mensajes de error/éxito --}}
  @if(session('ok') || $errors->any())
    <div class="flex-shrink-0 mb-3">
      @if(session('ok'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-3 py-2 text-sm
                    dark:border-green-700 dark:bg-green-900/20 dark:text-green-200">
          {!! session('ok') !!}
        </div>
      @endif
      @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm
                    dark:border-red-700 dark:bg-red-900/20 dark:text-red-200">
          @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
      @endif
    </div>
  @endif

  {{-- Dos columnas: flex-col en mobile, flex-row en desktop vía .pos-cols --}}
  <div class="flex flex-col gap-4 min-w-0 pos-cols">

    {{-- IZQUIERDA: 2/3 del espacio --}}
    <section class="min-w-0 pos-left">
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900
                  flex flex-col min-h-[calc(100svh-9rem)] lg:flex-1 lg:min-h-0 lg:overflow-hidden"
           x-data="{ activeTab: 'products' }">

        {{-- Fila superior fija: tabs + scanner --}}
        <div class="flex items-center gap-3 mb-3 flex-shrink-0">

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

          {{-- Scanner compacto --}}
          <div class="flex-1 flex justify-end">
            <div class="w-72">
              <livewire:pos-scanner :key="'pos-scanner'" />
            </div>
          </div>
        </div>

        {{-- Solo el catálogo scrollea; tabs + scanner quedan fijos --}}
        <div class="flex-1 overflow-y-auto min-h-0 pr-1">
          <div x-show="activeTab === 'products'">
            <livewire:product-catalog :key="'product-catalog'" />
          </div>
          <div x-show="activeTab === 'services'" x-cloak>
            <livewire:service-catalog :key="'service-catalog'" />
          </div>
        </div>

      </div>
    </section>

    {{-- DERECHA: 1/3 del espacio --}}
    <aside class="min-w-0 space-y-4 lg:space-y-0 pos-right">
      <div class="lg:flex-shrink-0">
        <livewire:schedule-order :key="'schedule-order'" />
      </div>

      <div class="pos-sidebar-wrap">
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
