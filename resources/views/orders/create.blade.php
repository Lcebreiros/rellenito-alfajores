@extends('layouts.app')

@section('header')
  <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-100 leading-tight transition-colors">
    Crear venta
  </h1>
@endsection

@section('content')
<div 
  class="max-w-screen-2xl mx-auto px-3 sm:px-6"
  x-data="receiptUI()"
  x-init="init()"
>

  {{-- Mensajes --}}
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-3 py-2 text-sm
                dark:border-green-700 dark:bg-green-900/20 dark:text-green-200">
      {!! session('ok') !!}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm
                dark:border-red-700 dark:bg-red-900/20 dark:text-red-200">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  {{-- Selector de Métodos de Pago --}}
  <div class="mb-6">
    <livewire:payment-method-selector :key="'payment-method-selector'" />
  </div>

  {{-- Layout responsive: IZQ productos (8/12) + DER venta (4/12) --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start min-w-0">

    {{-- IZQUIERDA: Productos y Servicios --}}
    <section class="lg:col-span-8 min-w-0">
      <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900
                  min-h-[calc(100svh-9rem)]">
        @if($products->isEmpty() && ($services->isEmpty() ?? true))
          <div class="h-40 grid place-items-center text-sm text-slate-500 dark:text-neutral-400">
            Aún no hay productos ni servicios. Crea uno para empezar.
          </div>
        @else
          {{-- Productos --}}
          @if(!$products->isEmpty())
            <h2 class="px-1 mb-2 text-sm font-semibold text-neutral-700 dark:text-neutral-300">Productos</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              @foreach($products as $p)
                <livewire:product-card
                  :product-id="$p->id"
                  :key="'product-card-'.$p->id"
                />
              @endforeach
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
          @endif

          {{-- Servicios --}}
          @if(isset($services) && !$services->isEmpty())
            <h2 class="px-1 mt-6 mb-2 text-sm font-semibold text-neutral-700 dark:text-neutral-300">Servicios</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              @foreach($services as $s)
                <livewire:service-card
                  :service-id="$s->id"
                  :key="'service-card-'.$s->id"
                />
              @endforeach
            </div>
            <div class="mt-4">{{ $services->links() }}</div>
          @endif
        @endif
      </div>
    </section>

    {{-- DERECHA: Venta en curso --}}
    <aside class="lg:col-span-4 space-y-4 min-w-0">
      {{-- Agendar (arriba del sidebar) --}}
      <livewire:schedule-order :key="'schedule-order'" />

      <div class="sticky top-24 min-w-0">
        {{-- Wrapper que iguala altura al contenedor de productos y la impone al root del componente --}}
        <div class="max-h-[calc(100svh-9rem)] h-full overflow-hidden w-full min-w-0
                    [&>*]:max-h-[calc(100svh-9rem)] [&>*]:h-full [&>*]:w-full">
          {{-- OrderSidebar usa el draft de sesión --}}
          <livewire:order-sidebar :key="'order-sidebar'" />
        </div>
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
          <div class="font-semibold">Venta agregada</div>
          <div class="text-sm opacity-90">Se creó la venta <span class="font-semibold">#<span x-text="toast.orderId"></span></span>.</div>
          <div class="mt-2 flex gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700"
              @click="openReceipt(toast.orderId)"
            >
              Ver comprobante
            </button>
            <a
              :href="receiptUrl(toast.orderId)"
              target="_blank"
              class="inline-flex items-center gap-1 rounded-lg border border-emerald-300 px-3 py-1.5 text-xs font-semibold text-emerald-900 hover:bg-emerald-100 dark:text-emerald-100 dark:border-emerald-700 dark:hover:bg-emerald-900/50"
            >
              Abrir en nueva pestaña
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
          ← Atrás
        </button>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow
                 hover:bg-indigo-700"
          @click="downloadEmbedded()"
        >
          Descargar PDF
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
