<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl text-slate-800 dark:text-neutral-100 leading-tight">
      {{ __('settings.profile_heading') }}
    </h2>
  </x-slot>

  <div class="bg-gray-50 dark:bg-neutral-950">
    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      {{-- Intro --}}
      <div class="mb-8 text-center">
        <h3 class="text-2xl font-semibold text-slate-900 dark:text-neutral-100">{{ __('settings.profile_page_title') }}</h3>
        <p class="mt-1 text-slate-600 dark:text-neutral-400">{{ __('settings.profile_page_subtitle') }}</p>
      </div>

      {{-- Responsive card grid --}}
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
          <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
              <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">{{ __('settings.profile_personal_title') }}</h4>
              <p class="text-sm text-slate-600 dark:text-neutral-400">{{ __('settings.profile_personal_sub') }}</p>
            </div>
            <div class="p-6">
              @livewire('profile.update-profile-information-form')
            </div>
          </div>
        @endif

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
          <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
              <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">{{ __('settings.profile_password_title') }}</h4>
              <p class="text-sm text-slate-600 dark:text-neutral-400">{{ __('settings.profile_password_sub') }}</p>
            </div>
            <div class="p-6">
              @livewire('profile.update-password-form')
            </div>
          </div>
        @endif

        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
          <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
              <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">{{ __('settings.profile_2fa_title') }}</h4>
              <p class="text-sm text-slate-600 dark:text-neutral-400">{{ __('settings.profile_2fa_sub') }}</p>
            </div>
            <div class="p-6">
              @livewire('profile.two-factor-authentication-form')
            </div>
          </div>
        @endif

        {{-- App logo --}}
        <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
          <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
            <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">{{ __('settings.profile_logo_title') }}</h4>
            <p class="text-sm text-slate-600 dark:text-neutral-400">{{ __('settings.profile_logo_sub') }}</p>
          </div>
          <div class="p-6">
            @livewire('profile.app-logo-form')
          </div>
        </div>

        {{-- Browser sessions --}}
        <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors">
          <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
            <h4 class="text-lg font-semibold text-slate-900 dark:text-neutral-100">{{ __('settings.profile_sessions_title') }}</h4>
            <p class="text-sm text-slate-600 dark:text-neutral-400">{{ __('settings.profile_sessions_sub') }}</p>
          </div>
          <div class="p-6">
            @livewire('profile.logout-other-browser-sessions-form')
          </div>
        </div>

        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
          <div class="profile-card bg-white dark:bg-neutral-900 rounded-2xl border border-slate-200 dark:border-neutral-700 shadow-sm dark:ring-1 dark:ring-indigo-500/10 transition-colors lg:col-span-2">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-neutral-700">
              <h4 class="text-lg font-semibold text-rose-700 dark:text-rose-300">{{ __('settings.profile_delete_title') }}</h4>
              <p class="text-sm text-slate-600 dark:text-neutral-400">{{ __('settings.profile_delete_sub') }}</p>
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
    .dark .profile-card input,
    .dark .profile-card select,
    .dark .profile-card textarea {
      background-color: #0a0a0a;
      border-color: #404040;
      color: #e5e5e5;
    }
    .dark .profile-card input::placeholder,
    .dark .profile-card textarea::placeholder { color: #a3a3a3; }
    .dark .profile-card label { color: #d4d4d4; }
    .dark .profile-card .text-gray-600 { color: #a3a3a3; }
    .dark .profile-card .text-gray-700 { color: #e5e5e5; }
    .dark .profile-card .border-gray-300 { border-color: #404040; }

    @media (hover:hover){
      .dark .profile-card:hover{ background-color:#0f0f0f; }
    }

    @media (max-width: 360px){
      .profile-card .px-6 { padding-left: 1rem; padding-right: 1rem; }
      .profile-card .p-6  { padding: 1rem; }
    }
  </style>
  @endpush
</x-app-layout>
