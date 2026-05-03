@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ __('payment_methods.title') }}</h1>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-6">
  @if(session('ok'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('ok') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
      {{ session('error') }}
    </div>
  @endif

  {{-- Descripción --}}
  <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
    <div class="flex items-start gap-3">
      <x-heroicon-o-information-circle class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
      <div class="text-sm text-blue-800 dark:text-blue-200">
        <p class="font-medium mb-1">{{ __('payment_methods.info_title') }}</p>
        <p class="text-blue-700 dark:text-blue-300">{{ __('payment_methods.info_body') }}</p>
      </div>
    </div>
  </div>

  {{-- Grid de métodos de pago --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @foreach($globalMethods as $method)
      @php $isActivated = in_array($method->id, $activatedMethodIds); @endphp

      <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-800 p-5 transition-all hover:shadow-md {{ $isActivated ? 'ring-2 ring-indigo-500 dark:ring-indigo-400' : '' }}">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center">
                <x-dynamic-component :component="'heroicon-o-' . $method->getIcon()" class="w-6 h-6 text-neutral-600 dark:text-neutral-400" />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-neutral-900 dark:text-neutral-100 truncate">{{ $method->name }}</h3>
                @if($method->requires_gateway)
                  <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                    <x-heroicon-s-link class="w-2.5 h-2.5" /> {{ __('payment_methods.badge_automatic') }}
                  </span>
                @endif
              </div>
            </div>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 line-clamp-2">{{ $method->description }}</p>
          </div>

          <div class="flex-shrink-0">
            <form action="{{ route('payment-methods.toggle-global', $method) }}" method="POST" class="inline">
              @csrf
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" class="sr-only peer" onchange="this.form.submit()" {{ $isActivated ? 'checked' : '' }}>
                <div class="relative w-14 h-7 rounded-full transition-colors ease-in-out duration-200 border-2
                            {{ $isActivated ? 'bg-indigo-600 border-indigo-600' : 'bg-gray-200 dark:bg-neutral-700 border-gray-300 dark:border-neutral-600' }}">
                  <div class="absolute top-0.5 bg-white rounded-full h-5 w-5 transition-transform ease-in-out duration-200 shadow-lg"
                       style="transform: {{ $isActivated ? 'translateX(1.875rem)' : 'translateX(0.125rem)' }}"></div>
                </div>
              </label>
            </form>
          </div>
        </div>

        @if($isActivated)
          <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-800">
            <div class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400">
              <x-heroicon-s-check-circle class="w-4 h-4" />
              <span class="font-medium">{{ __('payment_methods.status_active_text') }}</span>
            </div>
          </div>
        @endif

        {{-- Panel OAuth de Mercado Pago (solo si es el método MP y está activado) --}}
        @if($method->gateway_provider === 'mercadopago' && $isActivated)
          <div class="mt-4 pt-4 border-t border-neutral-200 dark:border-neutral-800">
            @if($mpCredential)
              {{-- ── CONECTADO ── --}}
              <div
                x-data="{
                  devices: [],
                  selectedDevice: '{{ $mpCredential->selected_device_id }}',
                  loading: false,
                  saving: false,
                  activating: null,
                  error: null,
                  saved: false,
                  activated: false,

                  get selectedDeviceObj() {
                    return this.devices.find(d => d.id === this.selectedDevice) ?? null;
                  },

                  async loadDevices() {
                    this.loading = true;
                    this.error   = null;
                    try {
                      const res  = await fetch('{{ route('mercadopago.devices') }}', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
                      });
                      const data = await res.json();
                      if (!res.ok) throw new Error(data.error ?? 'Error');
                      this.devices = data.devices ?? [];
                    } catch(e) {
                      this.error = e.message;
                    } finally {
                      this.loading = false;
                    }
                  },

                  async saveDevice() {
                    if (!this.selectedDevice) return;
                    this.saving = true;
                    this.saved  = false;
                    try {
                      const res = await fetch('{{ route('mercadopago.device.select') }}', {
                        method: 'POST',
                        headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                          Accept: 'application/json'
                        },
                        body: JSON.stringify({ device_id: this.selectedDevice })
                      });
                      if (!res.ok) throw new Error('Error al guardar');
                      this.saved = true;
                      setTimeout(() => this.saved = false, 3000);
                    } catch(e) {
                      this.error = e.message;
                    } finally {
                      this.saving = false;
                    }
                  },

                  async activateDevice(deviceId) {
                    this.activating = deviceId;
                    this.error      = null;
                    this.activated  = false;
                    try {
                      const res = await fetch(`/mercadopago/devices/${deviceId}/activate`, {
                        method: 'POST',
                        headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                          Accept: 'application/json'
                        }
                      });
                      const data = await res.json();
                      if (!res.ok) throw new Error(data.error ?? '{{ __('mp.device_activate_error') }}');
                      const d = this.devices.find(d => d.id === deviceId);
                      if (d) d.operating_mode = 'PDV';
                      this.activated = true;
                      setTimeout(() => this.activated = false, 4000);
                    } catch(e) {
                      this.error = e.message;
                    } finally {
                      this.activating = null;
                    }
                  }
                }"
                x-init="loadDevices()"
              >
                {{-- Cuenta conectada --}}
                <div class="flex items-center justify-between gap-3 mb-4">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-[#009EE3]/10 flex items-center justify-center shrink-0">
                      <svg class="w-4 h-4 text-[#009EE3]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm4.5 7h-2.25c-.414 0-.75.336-.75.75v4.5c0 .414.336.75.75.75H16.5c.414 0 .75-.336.75-.75v-4.5c0-.414-.336-.75-.75-.75zm-6 0H8.25c-.414 0-.75.336-.75.75v4.5c0 .414.336.75.75.75H10.5c.414 0 .75-.336.75-.75v-4.5c0-.414-.336-.75-.75-.75z"/>
                      </svg>
                    </div>
                    <div>
                      <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-100">
                        {{ __('mp.connected_as') }}: <span class="text-[#009EE3]">{{ $mpCredential->displayName() }}</span>
                      </p>
                      @if($mpCredential->expires_at)
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                          {{ __('mp.expires') }}: {{ $mpCredential->expires_at->format('d/m/Y') }}
                        </p>
                      @endif
                    </div>
                  </div>

                  <form action="{{ route('mercadopago.disconnect') }}" method="POST"
                        onsubmit="return confirm(@js(__('mp.disconnect_confirm')))">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-xs text-rose-600 dark:text-rose-400 hover:underline whitespace-nowrap">
                      {{ __('mp.disconnect_btn') }}
                    </button>
                  </form>
                </div>

                {{-- Selector de dispositivo Point --}}
                <div class="rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50 p-3">
                  <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-300 mb-0.5">{{ __('mp.device_title') }}</p>
                  <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mb-3">{{ __('mp.device_desc') }}</p>

                  {{-- Loading --}}
                  <div x-show="loading" class="flex items-center gap-2 text-xs text-neutral-500">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    {{ __('mp.device_loading') }}
                  </div>

                  {{-- Error --}}
                  <p x-show="error && !loading" x-text="error"
                     class="text-xs text-rose-600 dark:text-rose-400 mb-2"></p>

                  {{-- Empty --}}
                  <p x-show="!loading && !error && devices.length === 0"
                     class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ __('mp.device_empty') }}
                  </p>

                  {{-- Select + Save --}}
                  <div x-show="devices.length > 0 && !loading" class="space-y-2">
                    <select
                      x-model="selectedDevice"
                      class="w-full rounded-lg border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 text-sm text-neutral-900 dark:text-neutral-100 focus:border-[#009EE3] focus:ring-[#009EE3] py-2 px-3"
                    >
                      <option value="">{{ __('mp.device_select_placeholder') }}</option>
                      <template x-for="d in devices" :key="d.id">
                        <option :value="d.id" x-text="d.id + (d.operating_mode ? ' [' + d.operating_mode + ']' : '')"></option>
                      </template>
                    </select>
                    <button
                      type="button"
                      @click="saveDevice()"
                      :disabled="!selectedDevice || saving"
                      class="w-full rounded-lg bg-[#009EE3] hover:bg-[#0087c4] disabled:opacity-40 disabled:cursor-not-allowed text-white py-2 px-4 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
                    >
                      <svg x-show="!saving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                      </svg>
                      <span x-show="!saving">{{ __('mp.device_save_btn') }}</span>
                      <span x-show="saving">{{ __('mp.device_saving') }}</span>
                    </button>
                  </div>

                  {{-- Botón activar PDV: solo si hay dispositivo seleccionado y está en modo STANDALONE --}}
                  <div x-show="selectedDeviceObj && selectedDeviceObj.operating_mode === 'STANDALONE'" class="mt-2 flex items-center gap-2">
                    <div class="flex-1 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 px-3 py-2 flex items-center justify-between gap-3">
                      <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <span class="text-xs text-amber-700 dark:text-amber-300 font-medium">{{ __('mp.device_mode_standalone') }} — {{ __('mp.device_activate_pdv') }}</span>
                      </div>
                      <button
                        type="button"
                        @click="activateDevice(selectedDevice)"
                        :disabled="activating === selectedDevice"
                        class="shrink-0 rounded-lg bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white px-3 py-1 text-xs font-semibold transition-colors"
                      >
                        <span x-show="activating !== selectedDevice">{{ __('mp.device_activate_pdv') }}</span>
                        <span x-show="activating === selectedDevice">{{ __('mp.device_activating') }}</span>
                      </button>
                    </div>
                  </div>

                  {{-- PDV mode badge: cuando ya está en PDV --}}
                  <div x-show="selectedDeviceObj && selectedDeviceObj.operating_mode === 'PDV'" class="mt-2">
                    <div class="flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400">
                      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                      </svg>
                      <span class="font-medium">{{ __('mp.device_mode_pdv') }}</span>
                    </div>
                  </div>

                  {{-- Feedback: guardado --}}
                  <p x-show="saved" x-transition
                     class="mt-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                    ✓ {{ __('mp.device_saved') }}
                  </p>

                  {{-- Feedback: activado PDV (con aviso de reinicio obligatorio) --}}
                  <div x-show="activated" x-transition
                       class="mt-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 px-3 py-2">
                    <p class="text-xs text-emerald-700 dark:text-emerald-300 font-semibold">
                      ✓ {{ __('mp.device_activated') }}
                    </p>
                  </div>
                </div>
              </div>

            @else
              {{-- ── NO CONECTADO ── --}}
              <div class="flex items-center justify-between gap-3 rounded-lg border border-dashed border-neutral-300 dark:border-neutral-600 p-3">
                <div>
                  <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-200">{{ __('mp.panel_title') }}</p>
                  <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">{{ __('mp.panel_desc') }}</p>
                </div>
                <a href="{{ route('mercadopago.connect') }}"
                   class="shrink-0 inline-flex items-center gap-2 rounded-lg bg-[#009EE3] hover:bg-[#0087c4] text-white px-3 py-2 text-xs font-semibold transition-colors whitespace-nowrap">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm4.5 7h-2.25c-.414 0-.75.336-.75.75v4.5c0 .414.336.75.75.75H16.5c.414 0 .75-.336.75-.75v-4.5c0-.414-.336-.75-.75-.75zm-6 0H8.25c-.414 0-.75.336-.75.75v4.5c0 .414.336.75.75.75H10.5c.414 0 .75-.336.75-.75v-4.5c0-.414-.336-.75-.75-.75z"/>
                  </svg>
                  {{ __('mp.connect_btn') }}
                </a>
              </div>
            @endif
          </div>
        @endif

      </div>
    @endforeach
  </div>

  @if($globalMethods->count() === 0)
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-8">
      <div class="text-center py-8">
        <x-heroicon-o-credit-card class="w-16 h-16 mx-auto text-neutral-300 dark:text-neutral-700 mb-4" />
        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">{{ __('payment_methods.empty_title') }}</h3>
        <p class="text-neutral-600 dark:text-neutral-400">{{ __('payment_methods.empty_body') }}</p>
      </div>
    </div>
  @endif
</div>
@endsection
