<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">Bienvenido 👋</h3>
                <p class="text-gray-600 mb-6">Usa el menú de la izquierda para crear pedidos y administrar productos.</p>

                <div class="grid gap-4 sm:grid-cols-2">
                    <a href="{{ route('orders.create') }}"
                       class="block p-4 rounded-lg border hover:shadow transition">
                        <div class="font-medium">Crear pedido</div>
                        <p class="text-sm text-gray-500">Agrega productos con un click y finaliza.</p>
                    </a>
                    <a href="{{ route('products.index') }}"
                       class="block p-4 rounded-lg border hover:shadow transition">
                        <div class="font-medium">Gestionar productos</div>
                        <p class="text-sm text-gray-500">Precios, stock y estado.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>