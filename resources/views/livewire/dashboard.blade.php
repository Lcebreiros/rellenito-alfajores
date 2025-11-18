{{-- resources/views/livewire/dashboard.blade.php --}}
<div
  x-data="{ editMode: @entangle('editMode').live }"
  x-cloak
  class="min-h-[70vh]"
>
  {{-- GRID --}}
<div
  class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4
         auto-rows-[14rem] md:auto-rows-[16rem]
         items-stretch content-start justify-items-stretch
         isolate"
>
  @foreach($layout as $slot)
    @php 
      $meta    = $available[$slot['key']] ?? null; 
      $rowSpan = $slot['key'] === 'revenue-widget' ? 'row-span-2' : '';
      $colSpan = $slot['key'] === 'revenue-widget' ? 'col-span-2' : '';
    @endphp

    <div class="relative min-w-0 {{ $rowSpan }} {{ $colSpan }}" wire:key="cell-{{ $slot['id'] }}">
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

</div>

{{-- No se requiere script Alpine adicional: usamos objeto inline con @entangle --}}
