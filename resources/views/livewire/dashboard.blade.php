{{-- resources/views/livewire/dashboard.blade.php --}}
<div
  x-data="{ editMode: @entangle('editMode').live }"
  x-cloak
  class="w-full overflow-x-hidden"
>
  {{-- Estilos para scrollbar personalizado en widgets --}}
  <style>
    /* Scrollbar personalizado para widgets del dashboard */
    .dashboard-widget-scroll {
      scrollbar-width: thin;
      scrollbar-color: rgb(212 212 216 / 0.4) transparent;
    }

    .dark .dashboard-widget-scroll {
      scrollbar-color: rgb(82 82 91 / 0.4) transparent;
    }

    /* WebKit (Chrome, Safari, Edge) */
    .dashboard-widget-scroll::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    .dashboard-widget-scroll::-webkit-scrollbar-track {
      background: transparent;
    }

    .dashboard-widget-scroll::-webkit-scrollbar-thumb {
      background-color: rgb(212 212 216 / 0.4);
      border-radius: 9999px;
      border: 2px solid transparent;
      background-clip: padding-box;
    }

    .dashboard-widget-scroll::-webkit-scrollbar-thumb:hover {
      background-color: rgb(212 212 216 / 0.7);
    }

    .dark .dashboard-widget-scroll::-webkit-scrollbar-thumb {
      background-color: rgb(82 82 91 / 0.4);
    }

    .dark .dashboard-widget-scroll::-webkit-scrollbar-thumb:hover {
      background-color: rgb(82 82 91 / 0.7);
    }
  </style>

  {{-- GRID --}}
<div
  class="w-full px-4 sm:px-5 lg:px-6
         grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4
         gap-3 md:gap-4
         auto-rows-[14rem] sm:auto-rows-[15rem] lg:auto-rows-[16rem]
         items-stretch content-start justify-items-stretch
         isolate overflow-hidden"
>
  @foreach($layout as $slot)
    @php
      $meta    = $available[$slot['key']] ?? null;
      // Widgets que necesitan más espacio
      $isLargeWidget = in_array($slot['key'], ['revenue-widget', 'expenses-widget']);
      $rowSpan = $isLargeWidget ? 'sm:row-span-2' : '';
      $colSpan = $isLargeWidget ? 'sm:col-span-2' : '';
    @endphp

    <div class="relative w-full min-w-0 {{ $rowSpan }} {{ $colSpan }}" wire:key="cell-{{ $slot['id'] }}">
      @if ($editMode)
        <button
          type="button"
          wire:click="removeWidget('{{ $slot['id'] }}')"
          class="absolute -top-2 -right-2 z-10 w-8 h-8 rounded-full bg-red-600 text-white text-sm font-bold shadow ring-2 ring-white dark:ring-neutral-900"
          title="Quitar widget"
        >×</button>
      @endif

      {{-- El widget maneja sus propios estilos --}}
      <div class="h-full">
        @if ($meta)
          <livewire:is
            :component="$meta['component']"
            wire:key="w-{{ $slot['id'] }}"
            :compact="true"
          />
        @else
          <div class="h-full flex items-center justify-center text-sm text-neutral-500 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl">
            Widget no disponible ({{ $slot['key'] }})
          </div>
        @endif
      </div>
    </div>
  @endforeach
</div>

{{-- No se requiere script Alpine adicional: usamos objeto inline con @entangle --}}
</div>
