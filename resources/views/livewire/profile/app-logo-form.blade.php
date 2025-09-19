<div x-data="{ 
        toast: '', 
        showToast: false, 
        previewTemp: null,
        previewUrl: @entangle('currentLogoUrl') 
    }"
     x-on:notify.window="toast = $event.detail.message; showToast = true; setTimeout(()=> showToast = false, 3500)"
     class="max-w-xl">

    {{-- Toast simple --}}
    <div x-show="showToast"
         x-transition
         class="fixed bottom-6 right-6 bg-slate-900 text-white px-4 py-2 rounded shadow-lg z-50"
         x-text="toast" style="display: none;"></div>

    {{-- Vista previa --}}
    <div class="flex items-center gap-4 mb-4">
        <div class="w-20 h-20 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden">
            
            <!-- Preview instantáneo si se selecciona archivo -->
            <template x-if="previewTemp">
                <img :src="previewTemp" alt="Preview nuevo logo" class="max-w-full max-h-full object-contain">
            </template>

            <!-- Logo actual si no hay archivo seleccionado -->
            <template x-if="!previewTemp && previewUrl">
                <img :src="previewUrl" alt="Logo actual" class="max-w-full max-h-full object-contain">
            </template>

            <!-- Placeholder -->
            <template x-if="!previewTemp && !previewUrl">
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
               x-on:change="previewTemp = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
               wire:model="logo"
               accept=".png,.jpg,.jpeg,.webp"
               class="block w-full text-sm text-slate-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg
                      file:border file:border-slate-200 file:bg-white file:text-slate-700
                      hover:file:bg-slate-50 transition">

        @error('logo')
        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
        @enderror

        {{-- Indicador de carga --}}
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
                x-on:click="previewTemp = null" 
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
