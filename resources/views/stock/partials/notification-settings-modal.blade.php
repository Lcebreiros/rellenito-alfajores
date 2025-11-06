@php
  $user = auth()->user();
  $notifyLowStock = $user->notify_low_stock ?? true;
  $lowStockThreshold = $user->low_stock_threshold ?? 5;
  $notifyOutOfStock = $user->notify_out_of_stock ?? true;
  $notifyByEmail = $user->notify_by_email ?? false;
@endphp

<div id="notificationSettingsModal"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="notification-modal-title">
  <div class="bg-white dark:bg-neutral-900 rounded-xl max-w-lg w-full border border-gray-100 dark:border-neutral-700 dark:ring-1 dark:ring-indigo-500/10 shadow-2xl">

    {{-- Header --}}
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-neutral-700">
      <div class="flex items-center gap-3">
        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30">
          <i class="fas fa-bell text-indigo-600 dark:text-indigo-400" aria-hidden="true"></i>
        </div>
        <div>
          <h3 id="notification-modal-title" class="text-lg font-semibold text-gray-900 dark:text-neutral-100">
            Configurar Notificaciones
          </h3>
          <p class="text-xs text-gray-500 dark:text-neutral-400">Alertas de stock</p>
        </div>
      </div>
      <button type="button"
              id="closeNotificationModal"
              aria-label="Cerrar modal"
              class="text-gray-400 hover:text-gray-600 dark:text-neutral-500 dark:hover:text-neutral-300 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 transition-colors">
        <i class="fas fa-times text-xl" aria-hidden="true"></i>
      </button>
    </div>

    {{-- Form --}}
    <form id="notificationSettingsForm" class="p-6">
      <div class="space-y-5">
        {{-- Alerta de stock bajo --}}
        <div class="p-4 rounded-xl bg-amber-50/50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/30">
          <div class="flex items-start justify-between gap-4 mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1.5">
                <i class="fas fa-triangle-exclamation text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                <h4 class="font-semibold text-gray-900 dark:text-neutral-100">Alerta de Stock Bajo</h4>
              </div>
              <p class="text-sm text-gray-600 dark:text-neutral-400">
                Notificación cuando el stock esté bajo
              </p>
            </div>

            {{-- Toggle switch --}}
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
              <input type="checkbox"
                     id="notify_low_stock"
                     name="notify_low_stock"
                     {{ $notifyLowStock ? 'checked' : '' }}
                     class="sr-only peer">
              <div class="relative w-14 h-7 rounded-full transition-colors ease-in-out duration-200 border-2
                          {{ $notifyLowStock ? 'bg-amber-600 border-amber-600' : 'bg-gray-200 dark:bg-neutral-700 border-gray-300 dark:border-neutral-600' }}">
                <div class="toggle-knob absolute top-0.5 bg-white rounded-full h-5 w-5 transition-transform ease-in-out duration-200 shadow-lg"
                     style="transform: {{ $notifyLowStock ? 'translateX(1.875rem)' : 'translateX(0.125rem)' }}"></div>
              </div>
            </label>
          </div>

          {{-- Threshold input --}}
          <div id="thresholdSection" class="{{ $notifyLowStock ? '' : 'hidden' }} mt-3 pt-3 border-t border-amber-200 dark:border-amber-900/30">
            <label for="low_stock_threshold" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">
              Umbral de stock bajo
            </label>
            <div class="flex items-center gap-3">
              <div class="relative flex-1">
                <input type="number"
                       id="low_stock_threshold"
                       name="low_stock_threshold"
                       value="{{ $lowStockThreshold }}"
                       min="1"
                       max="1000"
                       required
                       class="w-full px-4 py-2.5 rounded-lg border-gray-300 dark:border-neutral-600
                              dark:bg-neutral-900 dark:text-neutral-100
                              focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                       placeholder="Ej: 5">
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 dark:text-neutral-400 pointer-events-none">
                  unidades
                </div>
              </div>
            </div>
            <p class="mt-2 text-xs text-gray-600 dark:text-neutral-400">
              <i class="fas fa-info-circle mr-1" aria-hidden="true"></i>
              Alertar cuando queden <span id="thresholdDisplay">{{ $lowStockThreshold }}</span> o menos unidades
            </p>
          </div>
        </div>

        {{-- Alerta sin stock --}}
        <div class="p-4 rounded-xl bg-rose-50/50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-900/30">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1.5">
                <i class="fas fa-circle-xmark text-rose-600 dark:text-rose-400" aria-hidden="true"></i>
                <h4 class="font-semibold text-gray-900 dark:text-neutral-100">Alerta Sin Stock</h4>
              </div>
              <p class="text-sm text-gray-600 dark:text-neutral-400">
                Notificación cuando un producto se quede sin stock
              </p>
            </div>

            {{-- Toggle switch --}}
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
              <input type="checkbox"
                     id="notify_out_of_stock"
                     name="notify_out_of_stock"
                     {{ $notifyOutOfStock ? 'checked' : '' }}
                     class="sr-only peer">
              <div class="relative w-14 h-7 rounded-full transition-colors ease-in-out duration-200 border-2
                          {{ $notifyOutOfStock ? 'bg-rose-600 border-rose-600' : 'bg-gray-200 dark:bg-neutral-700 border-gray-300 dark:border-neutral-600' }}">
                <div class="toggle-knob absolute top-0.5 bg-white rounded-full h-5 w-5 transition-transform ease-in-out duration-200 shadow-lg"
                     style="transform: {{ $notifyOutOfStock ? 'translateX(1.875rem)' : 'translateX(0.125rem)' }}"></div>
              </div>
            </label>
          </div>
        </div>

        {{-- Notificaciones por Email --}}
        <div class="p-4 rounded-xl bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-200 dark:border-indigo-900/30">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1.5">
                <i class="fas fa-envelope text-indigo-600 dark:text-indigo-400" aria-hidden="true"></i>
                <h4 class="font-semibold text-gray-900 dark:text-neutral-100">Notificaciones por Email</h4>
              </div>
              <p class="text-sm text-gray-600 dark:text-neutral-400">
                Recibir notificaciones de stock por correo electrónico
              </p>
            </div>

            {{-- Toggle switch --}}
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
              <input type="checkbox"
                     id="notify_by_email"
                     name="notify_by_email"
                     {{ $notifyByEmail ? 'checked' : '' }}
                     class="sr-only peer">
              <div class="relative w-14 h-7 rounded-full transition-colors ease-in-out duration-200 border-2
                          {{ $notifyByEmail ? 'bg-indigo-600 border-indigo-600' : 'bg-gray-200 dark:bg-neutral-700 border-gray-300 dark:border-neutral-600' }}">
                <div class="toggle-knob absolute top-0.5 bg-white rounded-full h-5 w-5 transition-transform ease-in-out duration-200 shadow-lg"
                     style="transform: {{ $notifyByEmail ? 'translateX(1.875rem)' : 'translateX(0.125rem)' }}"></div>
              </div>
            </label>
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <div class="flex items-center justify-between mt-6 pt-5 border-t border-gray-200 dark:border-neutral-700">
        <a href="{{ route('settings') }}"
           class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
          <i class="fas fa-cog mr-1" aria-hidden="true"></i>
          Ver configuración completa
        </a>

        <div class="flex items-center gap-2">
          <button type="button"
                  id="cancelNotificationBtn"
                  class="px-4 py-2 rounded-lg text-sm font-medium
                         border border-gray-300 dark:border-neutral-600
                         text-gray-700 dark:text-neutral-200
                         hover:bg-gray-50 dark:hover:bg-neutral-800
                         transition-colors">
            Cancelar
          </button>
          <button type="submit"
                  id="saveNotificationBtn"
                  class="px-6 py-2 rounded-lg text-sm font-medium
                         bg-indigo-600 hover:bg-indigo-700
                         text-white shadow-sm hover:shadow-md
                         focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2
                         transition-all duration-200">
            <i class="fas fa-save mr-1.5" aria-hidden="true"></i>
            Guardar
          </button>
        </div>
      </div>
    </form>

    {{-- Loading overlay --}}
    <div id="notificationLoadingOverlay" class="hidden absolute inset-0 bg-white/80 dark:bg-neutral-900/80 rounded-xl flex items-center justify-center">
      <div class="text-center">
        <i class="fas fa-spinner fa-spin text-3xl text-indigo-600 dark:text-indigo-400 mb-2"></i>
        <p class="text-sm text-gray-600 dark:text-neutral-400">Guardando...</p>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';

  const modal = {
    element: null,
    form: null,
    loadingOverlay: null,
    thresholdSection: null,
    thresholdInput: null,
    thresholdDisplay: null,
    lowStockCheckbox: null,

    init() {
      this.element = document.getElementById('notificationSettingsModal');
      this.form = document.getElementById('notificationSettingsForm');
      this.loadingOverlay = document.getElementById('notificationLoadingOverlay');
      this.thresholdSection = document.getElementById('thresholdSection');
      this.thresholdInput = document.getElementById('low_stock_threshold');
      this.thresholdDisplay = document.getElementById('thresholdDisplay');
      this.lowStockCheckbox = document.getElementById('notify_low_stock');

      if (!this.element || !this.form) return;

      this.attachEvents();
    },

    attachEvents() {
      // Abrir modal
      const openBtn = document.getElementById('openNotificationSettingsBtn');
      if (openBtn) {
        openBtn.addEventListener('click', () => this.show());
      }

      // Cerrar modal
      const closeBtn = document.getElementById('closeNotificationModal');
      const cancelBtn = document.getElementById('cancelNotificationBtn');

      if (closeBtn) closeBtn.addEventListener('click', () => this.hide());
      if (cancelBtn) cancelBtn.addEventListener('click', () => this.hide());

      this.element.addEventListener('click', (e) => {
        if (e.target === this.element) this.hide();
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && this.isVisible()) this.hide();
      });

      // Toggle threshold section
      if (this.lowStockCheckbox) {
        this.lowStockCheckbox.addEventListener('change', (e) => {
          const track = e.target.nextElementSibling;
          const knob = track.querySelector('.toggle-knob');

          if (e.target.checked) {
            this.thresholdSection.classList.remove('hidden');
            track.classList.remove('bg-gray-200', 'dark:bg-neutral-700', 'border-gray-300', 'dark:border-neutral-600');
            track.classList.add('bg-amber-600', 'border-amber-600');
            knob.style.transform = 'translateX(1.875rem)';
          } else {
            this.thresholdSection.classList.add('hidden');
            track.classList.remove('bg-amber-600', 'border-amber-600');
            track.classList.add('bg-gray-200', 'dark:bg-neutral-700', 'border-gray-300', 'dark:border-neutral-600');
            knob.style.transform = 'translateX(0.125rem)';
          }
        });
      }

      // Toggle para notify_out_of_stock
      const outOfStockCheckbox = document.getElementById('notify_out_of_stock');
      if (outOfStockCheckbox) {
        outOfStockCheckbox.addEventListener('change', (e) => {
          const track = e.target.nextElementSibling;
          const knob = track.querySelector('.toggle-knob');

          if (e.target.checked) {
            track.classList.remove('bg-gray-200', 'dark:bg-neutral-700', 'border-gray-300', 'dark:border-neutral-600');
            track.classList.add('bg-rose-600', 'border-rose-600');
            knob.style.transform = 'translateX(1.875rem)';
          } else {
            track.classList.remove('bg-rose-600', 'border-rose-600');
            track.classList.add('bg-gray-200', 'dark:bg-neutral-700', 'border-gray-300', 'dark:border-neutral-600');
            knob.style.transform = 'translateX(0.125rem)';
          }
        });
      }

      // Toggle para notify_by_email
      const emailCheckbox = document.getElementById('notify_by_email');
      if (emailCheckbox) {
        emailCheckbox.addEventListener('change', (e) => {
          const track = e.target.nextElementSibling;
          const knob = track.querySelector('.toggle-knob');

          if (e.target.checked) {
            track.classList.remove('bg-gray-200', 'dark:bg-neutral-700', 'border-gray-300', 'dark:border-neutral-600');
            track.classList.add('bg-indigo-600', 'border-indigo-600');
            knob.style.transform = 'translateX(1.875rem)';
          } else {
            track.classList.remove('bg-indigo-600', 'border-indigo-600');
            track.classList.add('bg-gray-200', 'dark:bg-neutral-700', 'border-gray-300', 'dark:border-neutral-600');
            knob.style.transform = 'translateX(0.125rem)';
          }
        });
      }

      // Update threshold display
      if (this.thresholdInput) {
        this.thresholdInput.addEventListener('input', (e) => {
          this.thresholdDisplay.textContent = e.target.value || '5';
        });
      }

      // Submit form
      this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    },

    async handleSubmit(e) {
      e.preventDefault();

      const formData = new FormData(this.form);
      const data = {
        notify_low_stock: formData.get('notify_low_stock') === 'on',
        low_stock_threshold: parseInt(formData.get('low_stock_threshold')) || 5,
        notify_out_of_stock: formData.get('notify_out_of_stock') === 'on',
        notify_by_email: formData.get('notify_by_email') === 'on',
      };

      this.loadingOverlay.classList.remove('hidden');

      try {
        const response = await fetch('{{ route("stock.notifications.update") }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
          body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
          // Mostrar mensaje de éxito
          this.showSuccessMessage(result.message || 'Configuración guardada correctamente');
          setTimeout(() => this.hide(), 1500);
        } else {
          this.showErrorMessage(result.message || 'Error al guardar la configuración');
        }
      } catch (error) {
        console.error('Error:', error);
        this.showErrorMessage('Error al guardar la configuración');
      } finally {
        this.loadingOverlay.classList.add('hidden');
      }
    },

    show() {
      this.element.classList.remove('hidden');
      this.element.classList.add('flex');
      document.body.style.overflow = 'hidden';
    },

    hide() {
      this.element.classList.add('hidden');
      this.element.classList.remove('flex');
      document.body.style.overflow = '';
    },

    isVisible() {
      return !this.element.classList.contains('hidden');
    },

    showSuccessMessage(message) {
      // Crear toast de éxito
      const toast = document.createElement('div');
      toast.className = 'fixed top-4 right-4 z-[60] bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 animate-slide-in';
      toast.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
      `;
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    },

    showErrorMessage(message) {
      const toast = document.createElement('div');
      toast.className = 'fixed top-4 right-4 z-[60] bg-rose-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 animate-slide-in';
      toast.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
      `;
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => modal.init());
  } else {
    modal.init();
  }
})();
</script>

<style>
@keyframes slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-slide-in {
  animation: slide-in 0.3s ease-out;
}
</style>
