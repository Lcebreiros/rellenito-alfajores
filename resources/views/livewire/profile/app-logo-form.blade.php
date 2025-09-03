<div
  x-data="{ preview: @entangle('currentLogoUrl') }"
  class="max-w-xl">

  {{-- Vista previa --}}
  <div class="flex items-center gap-4 mb-4">
    <div class="w-20 h-20 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden">
      <template x-if="preview">
        <img :src="preview" alt="Logo actual" class="max-w-full max-h-full object-contain">
      </template>
      <template x-if="!preview">
        <span class="text-xs text-slate-500">Sin logo</span>
      </template>
    </div>

    <div class="text-sm text-slate-600">
      <p class="font-medium text-slate-800">Logo actual</p>
      <p>Recomendado: PNG con fondo transparente, mínimo 128×128.</p>
    </div>
  </div>

  {{-- Input de archivo --}}
  <div class="mb-4">
    <input type="file"
           wire:model="logo"
           accept=".png,.jpg,.jpeg,.webp"
           class="block w-full text-sm text-slate-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg
                  file:border file:border-slate-200 file:bg-white file:text-slate-700
                  hover:file:bg-slate-50 transition">

    @error('logo')
    <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
    @enderror

    {{-- Preview instantáneo cuando se elige un archivo --}}
    <div class="mt-3" wire:loading wire:target="logo">
      <p class="text-sm text-slate-500">Subiendo…</p>
    </div>
  </div>

  {{-- Botones --}}
  <div class="flex items-center gap-2">
    <button type="button"
            wire:click="save"
            wire:loading.attr="disabled"
            wire:target="save,logo"
            class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:opacity-95 disabled:opacity-50">
      Guardar logo
    </button>

    <button type="button"
            wire:click="remove"
            wire:loading.attr="disabled"
            class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 disabled:opacity-50">
      Quitar logo
    </button>
  </div>
</div>
