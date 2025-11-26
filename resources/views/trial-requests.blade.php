<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-slate-100 leading-tight transition-colors">
            {{ __('Solicitudes de Prueba') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @livewire('trial-requests')
        </div>
    </div>
</x-app-layout>
