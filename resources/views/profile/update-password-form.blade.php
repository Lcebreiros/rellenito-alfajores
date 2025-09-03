<x-form-section submit="updatePassword">
    <x-slot name="title">
        <h3 class="text-slate-900 dark:text-neutral-100">
            {{ __('Update Password') }}
        </h3>
    </x-slot>

    <x-slot name="description">
        <p class="text-slate-600 dark:text-neutral-300">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="current_password" value="{{ __('Current Password') }}" class="dark:text-neutral-300" />
            <x-input
                id="current_password"
                type="password"
                class="mt-1 block w-full dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:placeholder-neutral-400 dark:focus:ring-indigo-400/80"
                wire:model="state.current_password"
                autocomplete="current-password"
            />
            <x-input-error for="current_password" class="mt-2 dark:text-rose-300" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password" value="{{ __('New Password') }}" class="dark:text-neutral-300" />
            <x-input
                id="password"
                type="password"
                class="mt-1 block w-full dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:placeholder-neutral-400 dark:focus:ring-indigo-400/80"
                wire:model="state.password"
                autocomplete="new-password"
            />
            <x-input-error for="password" class="mt-2 dark:text-rose-300" />
        </div>

        <div class="col-span-6 sm:col-span-4">
            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" class="dark:text-neutral-300" />
            <x-input
                id="password_confirmation"
                type="password"
                class="mt-1 block w-full dark:bg-neutral-900 dark:text-neutral-100 dark:border-neutral-700 dark:placeholder-neutral-400 dark:focus:ring-indigo-400/80"
                wire:model="state.password_confirmation"
                autocomplete="new-password"
            />
            <x-input-error for="password_confirmation" class="mt-2 dark:text-rose-300" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3 dark:text-neutral-200" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        {{-- Mantiene colores de marca del botón --}}
        <x-button>
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
