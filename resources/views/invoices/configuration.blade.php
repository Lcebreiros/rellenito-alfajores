@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Configuración ARCA</h1>
@endsection

@section('header_actions')
  <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition-all duration-150 active:scale-[0.98]">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
    Ver facturas
  </a>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">
  {{-- Success message --}}
  @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300 flex items-center gap-2">
      <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
      </svg>
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300 flex items-center gap-2">
      <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
      {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>• {{ $e }}</div> @endforeach
    </div>
  @endif

  {{-- Info alert --}}
  <div class="mb-6 panel-glass p-4 border-l-4 border-indigo-500">
    <div class="flex gap-3">
      <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4m0-4h.01" />
      </svg>
      <div class="text-sm text-neutral-700 dark:text-neutral-300">
        <p class="font-semibold mb-1">Configuración de facturación electrónica</p>
        <p>Configure su certificado digital de ARCA para emitir facturas electrónicas. Esta configuración se guarda de forma segura y encriptada.</p>
        <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-400">Para obtener su certificado digital, visite <a href="https://www.afip.gob.ar" target="_blank" class="text-indigo-600 hover:underline">AFIP</a> o consulte la documentación.</p>
      </div>
    </div>
  </div>

  {{-- Configuration status --}}
  @if($config && $config->isConfigured())
    <div class="mb-6 panel-glass p-4 border-l-4 border-emerald-500">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="flex-1">
          <p class="font-semibold text-emerald-900 dark:text-emerald-100">Configuración activa</p>
          <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-1">
            CUIT: {{ $config->cuit }} | Ambiente: {{ $config->environment === 'production' ? 'Producción' : 'Testing' }}
            @if($config->certificate_expires_at)
              | Certificado vence: {{ $config->certificate_expires_at->format('d/m/Y') }}
            @endif
          </p>
        </div>
      </div>
    </div>
  @endif

  {{-- Configuration form --}}
  <div class="container-glass shadow-sm overflow-hidden">
    <div class="bg-neutral-100/70 dark:bg-neutral-800/60 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
      <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Datos de la empresa y certificado</h2>
    </div>

    <form action="{{ route('invoices.configuration.save') }}" method="POST" enctype="multipart/form-data" class="p-6">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- CUIT --}}
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
            CUIT <span class="text-rose-500">*</span>
          </label>
          <input type="text"
                 name="cuit"
                 value="{{ old('cuit', $config->cuit ?? '') }}"
                 placeholder="XX-XXXXXXXX-X"
                 maxlength="13"
                 required
                 class="input-enhanced w-full">
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Ingrese el CUIT sin guiones</p>
        </div>

        {{-- Business name --}}
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
            Razón Social <span class="text-rose-500">*</span>
          </label>
          <input type="text"
                 name="business_name"
                 value="{{ old('business_name', $config->business_name ?? '') }}"
                 required
                 class="input-enhanced w-full">
        </div>

        {{-- Tax condition --}}
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
            Condición frente al IVA <span class="text-rose-500">*</span>
          </label>
          <select name="tax_condition" required class="input-enhanced w-full">
            <option value="IVA Responsable Inscripto" {{ old('tax_condition', $config->tax_condition ?? '') === 'IVA Responsable Inscripto' ? 'selected' : '' }}>IVA Responsable Inscripto</option>
            <option value="Monotributo" {{ old('tax_condition', $config->tax_condition ?? '') === 'Monotributo' ? 'selected' : '' }}>Monotributo</option>
            <option value="Exento" {{ old('tax_condition', $config->tax_condition ?? '') === 'Exento' ? 'selected' : '' }}>Exento</option>
            <option value="No Responsable" {{ old('tax_condition', $config->tax_condition ?? '') === 'No Responsable' ? 'selected' : '' }}>No Responsable</option>
            <option value="Consumidor Final" {{ old('tax_condition', $config->tax_condition ?? '') === 'Consumidor Final' ? 'selected' : '' }}>Consumidor Final</option>
          </select>
        </div>

        {{-- Environment --}}
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
            Ambiente <span class="text-rose-500">*</span>
          </label>
          <select name="environment" required class="input-enhanced w-full">
            <option value="testing" {{ old('environment', $config->environment ?? 'testing') === 'testing' ? 'selected' : '' }}>Testing (Homologación)</option>
            <option value="production" {{ old('environment', $config->environment ?? '') === 'production' ? 'selected' : '' }}>Producción</option>
          </select>
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Comience en Testing para pruebas</p>
        </div>

        {{-- Default sale point --}}
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
            Punto de venta <span class="text-rose-500">*</span>
          </label>
          <input type="number"
                 name="default_sale_point"
                 value="{{ old('default_sale_point', $config->default_sale_point ?? 1) }}"
                 min="1"
                 required
                 class="input-enhanced w-full">
          <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Punto de venta configurado en ARCA</p>
        </div>
      </div>

      {{-- Certificate files --}}
      <div class="mt-6 pt-6 border-t border-neutral-200 dark:border-neutral-700">
        <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Certificados digitales</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          {{-- Certificate file --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Certificado (.crt, .pem)
              @if(!$config || !$config->certificate)
                <span class="text-rose-500">*</span>
              @endif
            </label>
            <input type="file"
                   name="certificate"
                   accept=".crt,.pem,.txt"
                   class="block w-full text-sm text-neutral-700 dark:text-neutral-300
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-lg file:border-0
                          file:text-sm file:font-medium
                          file:bg-indigo-50 file:text-indigo-700
                          hover:file:bg-indigo-100
                          dark:file:bg-indigo-900/20 dark:file:text-indigo-400
                          dark:hover:file:bg-indigo-900/30">
            @if($config && $config->certificate)
              <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">✓ Certificado cargado</p>
            @endif
          </div>

          {{-- Private key file --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Clave privada (.key)
              @if(!$config || !$config->private_key)
                <span class="text-rose-500">*</span>
              @endif
            </label>
            <input type="file"
                   name="private_key"
                   accept=".key,.pem,.txt"
                   class="block w-full text-sm text-neutral-700 dark:text-neutral-300
                          file:mr-4 file:py-2 file:px-4
                          file:rounded-lg file:border-0
                          file:text-sm file:font-medium
                          file:bg-indigo-50 file:text-indigo-700
                          hover:file:bg-indigo-100
                          dark:file:bg-indigo-900/20 dark:file:text-indigo-400
                          dark:hover:file:bg-indigo-900/30">
            @if($config && $config->private_key)
              <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">✓ Clave privada cargada</p>
            @endif
          </div>

          {{-- Certificate password --}}
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
              Contraseña del certificado
            </label>
            <input type="password"
                   name="certificate_password"
                   placeholder="••••••••"
                   class="input-enhanced w-full">
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Si el certificado tiene contraseña</p>
          </div>
        </div>
      </div>

      {{-- Submit button --}}
      <div class="mt-8 flex items-center justify-end gap-3">
        <a href="{{ route('inicio') }}"
           class="px-4 py-2.5 rounded-lg border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-800 text-sm font-medium transition-all duration-150">
          Cancelar
        </a>
        <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-all duration-150 active:scale-[0.98]">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          Guardar configuración
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
