{{-- resources/views/livewire/dashboard.blade.php --}}
<div
  x-data="{ addOpen:false, editMode: @entangle('editMode').live }"
  x-cloak
  class="min-h-[70vh]"
>
  {{-- Barra de acciones --}}
  <div class="flex items-center justify-end gap-2 mb-3">
    <div class="relative">
      <button
        type="button"
        @click="if (!editMode) { $wire.toggleEdit(); editMode = true } ; addOpen = !addOpen"
        class="px-3 py-2 rounded-lg bg-neutral-100 hover:bg-neutral-200 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-sm font-semibold"
        title="Añadir un widget al tablero"
      >
        + Añadir widget
      </button>

      <div
        x-show="addOpen && editMode"
        @click.outside="addOpen=false"
        x-transition.opacity
        class="absolute right-0 mt-2 w-56 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-xl shadow-lg p-2 z-10"
      >
        @forelse($available as $key => $meta)
          <button
            type="button"
            @click="addOpen=false"
            wire:click="addWidget('{{ $key }}')"
            class="w-full text-left px-3 py-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 text-sm"
          >
            {{ $meta['label'] ?? $key }}
          </button>
        @empty
          <div class="px-3 py-2 text-sm text-neutral-500">No hay widgets detectados.</div>
        @endforelse
      </div>
    </div>

    <button
      type="button"
      wire:click="toggleEdit"
      class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold"
    >
      {{ $editMode ? 'Salir de edición' : 'Editar' }}
    </button>
  </div>

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
            :wire:key="'w-'.$slot['id']"
            lazy
            :compact="true"
            :editMode="$editMode"
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
