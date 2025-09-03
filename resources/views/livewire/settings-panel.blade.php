<div class="max-w-3xl mx-auto space-y-6">

  {{-- AVISO OK --}}
  @if (session('ok'))
    <div class="p-3 rounded-md bg-green-100 text-green-800
                dark:bg-green-900 dark:text-green-200">
      {{ session('ok') }}
    </div>
  @endif

  {{-- CARD: Información de la aplicación --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900
              flex flex-col items-center text-center">
    {{-- Logo centrado --}}
    <div class="mb-4">
      <img src="{{ asset('images/Gestior.png') }}" alt="Gestior Logo" class="w-80 h-54">
    </div>

    {{-- Info --}}
    <p class="text-sm text-neutral-600 dark:text-neutral-400">
      Versión {{ config('app.version', '1.0.0') }}
    </p>
    <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-2">
      Herramienta de gestión y control económico
    </p>
  </div>

  {{-- CARD: Apariencia (switch) --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Apariencia</h2>

    <div class="flex items-center justify-between gap-6">
      <div>
        <p class="text-neutral-800 dark:text-neutral-200 font-medium">Tema</p>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">Cambiá entre Modo Día y Modo Noche</p>
      </div>

      {{-- SWITCH Día/Noche --}}
      <div x-data="{ t: @entangle('theme').live }" class="select-none">
        <button type="button"
                @click="t = (t === 'light' ? 'dark' : 'light'); $wire.setTheme(t)"
                @keydown.space.prevent="t = (t === 'light' ? 'dark' : 'light'); $wire.setTheme(t)"
                @keydown.enter.prevent="t = (t === 'light' ? 'dark' : 'light'); $wire.setTheme(t)"
                role="switch" :aria-checked="t === 'dark'"
                :aria-label="t === 'dark' ? 'Night mode' : 'Day mode'"
                class="relative w-[11.5rem] h-14 rounded-full border p-1 overflow-hidden
                       transition-colors duration-300 ease-out focus:outline-none focus-visible:ring-2 focus-visible:ring-neutral-400"
                :class="t === 'dark' ? 'bg-black border-black' : 'bg-neutral-200 border-neutral-300'">

          {{-- Label DAY (izq, sólo en día) --}}
          <span x-cloak
                class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-semibold whitespace-nowrap
                       transition-opacity duration-200"
                :class="t === 'light' ? 'opacity-100 text-neutral-900' : 'opacity-0 pointer-events-none'">
            DAY MODE
          </span>

          {{-- Label NIGHT (der, sólo en noche) --}}
          <span x-cloak
                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold whitespace-nowrap
                       transition-opacity duration-200"
                :class="t === 'dark' ? 'opacity-100 text-white' : 'opacity-0 pointer-events-none'">
            NIGHT MODE
          </span>

          {{-- KNOB: IZQ (noche) / DER (día) — SIEMPRE BLANCO --}}
          <div class="absolute top-1 h-12 w-12 rounded-full border shadow flex items-center justify-center
                      transition-[left,background-color,border-color] duration-300 ease-out
                      bg-white border-neutral-300 text-neutral-900"
               :class="t === 'dark' ? 'left-1' : 'left-[calc(100%-3.25rem)]'">
            <template x-if="t === 'light'">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"></circle>
                <path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5L19 19M5 19l1.5-1.5M17.5 6.5L19 5"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </template>
            <template x-if="t === 'dark'">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                <path d="M21 12.8a9 9 0 1 1-9.8-9 7 7 0 0 0 9.8 9z"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </template>
          </div>
        </button>

        <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-400">Se aplica sin recargar.</p>
      </div>
    </div>
  </div>

  {{-- CARD: Título del sitio --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Identidad</h2>

    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Título del sitio</label>
    <div class="flex items-center gap-3">
      <input type="text" wire:model.defer="site_title"
             class="flex-1 rounded-lg border-neutral-300 dark:border-neutral-700
                    dark:bg-neutral-950 dark:text-neutral-100
                    focus:ring-2 focus:ring-indigo-500"
             placeholder="Ej. Rellenito Alfajores">
      <button wire:click="save"
              wire:loading.attr="disabled"
              class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white
                     dark:bg-indigo-500 dark:hover:bg-indigo-600 transition disabled:opacity-60">
        Guardar
      </button>
    </div>
  </div>

  {{-- CARD: Logo del comprobante --}}
  <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow
              dark:border-neutral-800 dark:bg-neutral-900">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Logo del Comprobante</h2>

    <div class="flex items-start gap-6">
      {{-- Dropzone / input --}}
      <label class="flex-1 border-2 border-dashed rounded-xl cursor-pointer
                     border-neutral-300 dark:border-neutral-600
                     hover:border-indigo-400 dark:hover:border-indigo-500
                     bg-neutral-50 dark:bg-neutral-950/40 transition p-6 text-center">
        <input type="file" class="hidden" wire:model="receipt_logo" accept=".png,.jpg,.jpeg,.webp" />
        <div class="space-y-2">
          <div class="mx-auto w-12 h-12 rounded-full border border-neutral-300 dark:border-neutral-600 flex items-center justify-center">
            <svg class="w-6 h-6 text-neutral-500 dark:text-neutral-400" viewBox="0 0 24 24" fill="none">
              <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <p class="font-medium text-neutral-700 dark:text-neutral-200">Arrastrá una imagen o hacé clic</p>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">Acepta .png, .jpg, .jpeg, .webp (máx 2 MB)</p>
        </div>
      </label>

      {{-- Preview actual / nueva --}}
      <div class="w-48">
        <p class="text-xs uppercase tracking-wide text-neutral-600 dark:text-neutral-400 mb-2">Vista previa</p>

        @if ($receipt_logo)
          <img src="{{ $receipt_logo->temporaryUrl() }}" class="max-h-16 object-contain rounded shadow" alt="preview">
        @elseif($receipt_logo_url)
          <img src="{{ $receipt_logo_url }}" class="max-h-16 object-contain rounded shadow" alt="logo comprobante">
        @else
          <div class="h-16 rounded bg-neutral-200 dark:bg-neutral-700"></div>
        @endif

        <div class="mt-3 grid grid-cols-2 gap-2">
          <button wire:click="saveReceiptLogo"
                  wire:loading.attr="disabled"
                  class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm transition disabled:opacity-60">
            Guardar
          </button>
          <button wire:click="removeReceiptLogo"
                  wire:loading.attr="disabled"
                  class="px-3 py-2 rounded-lg border text-sm
                         hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
            Eliminar
          </button>
        </div>

        @error('receipt_logo')
          <div class="mt-3 text-sm text-rose-600">{{ $message }}</div>
        @enderror
      </div>
    </div>

    <p class="mt-3 text-xs text-neutral-600 dark:text-neutral-400">
      Consejo: subí una versión horizontal con fondo transparente para que se vea bien en el ticket.
    </p>
  </div>

  
  
    </div>
  </div>
</div>
