<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-slate-800 dark:text-neutral-100 leading-tight">
      {{ __('Mi perfil') }}
    </h2>
  </x-slot>

  <div class="bg-gray-50 dark:bg-neutral-950">
    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      {{-- Intro --}}
      <div class="mb-8 text-center">
        <h3 class="text-2xl font-semibold text-slate-900 dark:text-neutral-100">Configuración de la cuenta</h3>
        <p class="mt-1 text-slate-600 dark:text-neutral-400">Gestiona tu información personal, seguridad y preferencias de la app.</p>
      </div>

      {{-- Grid responsiva de tarjetas --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
          <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
              <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">Información personal</h4>
              <p class="text-sm text-slate-600 dark:text-neutral-400">Nombre, email y foto de perfil.</p>
            </div>
            <div class="p-6">
              @livewire('profile.update-profile-information-form')
            </div>
          </div>
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
          <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
              <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">Seguridad: Contraseña</h4>
              <p class="text-sm text-slate-600 dark:text-neutral-400">Actualiza tu contraseña periódicamente.</p>
            </div>
            <div class="p-6">
              @livewire('profile.update-password-form')
            </div>
          </div>
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
          <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
              <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">Doble factor (2FA)</h4>
              <p class="text-sm text-slate-600 dark:text-neutral-400">Protege tu cuenta con un segundo paso de verificación.</p>
            </div>
            <div class="p-6">
              @livewire('profile.two-factor-authentication-form')
            </div>
          </div>
        @endif

        {{-- Logo de la aplicación --}}
        <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
          <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
            <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">Personalización: Logo de la aplicación</h4>
            <p class="text-sm text-slate-600 dark:text-neutral-400">Sube un logo para el encabezado y el sidebar.</p>
          </div>
          <div class="p-6">
            @livewire('profile.app-logo-form')
          </div>
        </div>

        {{-- Sesiones --}}
        <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
          <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
            <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">Sesiones del navegador</h4>
            <p class="text-sm text-slate-600 dark:text-neutral-400">Cierra otras sesiones activas.</p>
          </div>
          <div class="p-6">
            @livewire('profile.logout-other-browser-sessions-form')
          </div>
        </div>

        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
          <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors lg:col-span-2">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
              <h4 class="text-lg font-semibold text-rose-700 dark:text-rose-300">Eliminar cuenta</h4>
              <p class="text-sm text-slate-600 dark:text-neutral-400">Borrará de forma permanente tus datos.</p>
            </div>
            <div class="p-6">
              @livewire('profile.delete-user-form')
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>

  @push('head')
  <style>
    /* Ajustes dark-mode negro/gris con toque sutil (inputs/labels dentro de tarjetas) */
    .dark .profile-card input,
    .dark .profile-card select,
    .dark .profile-card textarea {
      background-color: #0a0a0a;   /* neutral-950-ish para campos */
      border-color: #404040;       /* neutral-700 */
      color: #e5e5e5;              /* neutral-200 */
    }
    .dark .profile-card input::placeholder,
    .dark .profile-card textarea::placeholder { color: #a3a3a3; } /* neutral-400 */
    .dark .profile-card label { color: #d4d4d4; }                 /* neutral-300 */
    .dark .profile-card .text-gray-600 { color: #a3a3a3; }        /* tune gray->neutral */
    .dark .profile-card .text-gray-700 { color: #e5e5e5; }
    .dark .profile-card .border-gray-300 { border-color: #404040; }

    /* Hover leve para tarjetas en dark */
    @media (hover:hover){
      .dark .profile-card:hover{ background-color:#0f0f0f; } /* neutral-900 */
    }

    /* Pequeño ajuste responsivo para paddings en móviles muy angostos */
    @media (max-width: 360px){
      .profile-card .px-6 { padding-left: 1rem; padding-right: 1rem; }
      .profile-card .p-6  { padding: 1rem; }
    }
  </style>
  @endpush
</x-app-layout>
