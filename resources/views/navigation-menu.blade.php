{{-- SIDEBAR DESKTOP --}}
<aside class="hidden md:flex md:flex-col w-64 bg-white border-r min-h-screen">
    {{-- Logo / título --}}
    <div class="h-16 flex items-center px-4 border-b">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2">
            <x-application-mark class="h-9 w-auto" />
            <span class="font-semibold text-gray-800">Panel</span>
        </a>
    </div>

    {{-- Links --}}
    @php
        $base = 'flex items-center gap-3 px-3 py-2 rounded-lg border transition';
        $active = 'bg-indigo-50 text-indigo-700 border-indigo-200';
        $inactive = 'text-gray-700 border-transparent hover:bg-gray-50';
    @endphp

    <div class="flex-1 overflow-y-auto p-3 space-y-1">
        <a href="{{ route('dashboard') }}"
           class="{{ request()->routeIs('dashboard') ? "$base $active" : "$base $inactive" }}">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                <path d="M3 12l9-9 9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 21V12h6v9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('orders.create') }}"
           class="{{ request()->routeIs('orders.create') ? "$base $active" : "$base $inactive" }}">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                <path d="M3 7h18M6 3h12M6 21h12M5 7l1 14h12l1-14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Crear pedido</span>
        </a>

        <a href="{{ route('products.index') }}"
           class="{{ request()->routeIs('products.*') ? "$base $active" : "$base $inactive" }}">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                <path d="M3.3 7.7 12 12l8.7-4.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span>Productos</span>
        </a>

        <a href="{{ route('expenses.index') }}"
           class="{{ request()->routeIs('expenses.*') ? "$base $active" : "$base $inactive" }}">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Gastos</span>
        </a>
    </div>

    {{-- Perfil / Logout --}}
    <div class="mt-auto border-t p-3">
        <div class="flex items-center gap-3">
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <img class="w-9 h-9 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
            @endif
            <div class="min-w-0">
                <div class="text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
            </div>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2">
            <a href="{{ route('profile.show') }}" class="text-center text-sm py-2 rounded-lg border hover:bg-gray-50">Perfil</a>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button class="w-full text-center text-sm py-2 rounded-lg border hover:bg-gray-50">Salir</button>
            </form>
        </div>
    </div>
</aside>