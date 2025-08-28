{{-- Drawer mobile como componente separado --}}
<div x-data="{ open: false }" 
     @toggle-mobile-menu.window="open = !open" 
     class="md:hidden">
    
    {{-- Overlay + Drawer --}}
    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-50">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        
        {{-- Drawer panel --}}
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="absolute right-0 top-0 h-full w-80 max-w-full bg-white shadow-xl flex flex-col">
            
            {{-- Header del drawer --}}
            <div class="flex items-center justify-between p-4 border-b">
                <div class="inline-flex items-center gap-2">
                    <x-application-mark class="h-8 w-auto" />
                    <span class="font-semibold">Menú</span>
                </div>
                <button @click="open = false" class="p-2 rounded-md hover:bg-gray-100" aria-label="Cerrar menú">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none">
                        <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            {{-- Links --}}
            <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                @php
                    $base = 'flex items-center gap-3 px-3 py-3 rounded-lg border transition w-full';
                    $active = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                    $inactive = 'text-gray-700 border-transparent hover:bg-gray-50';
                @endphp

                <a href="{{ route('dashboard') }}"
                   @click="open = false"
                   class="{{ request()->routeIs('dashboard') ? "$base $active" : "$base $inactive" }}">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <path d="M3 12l9-9 9 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 21V12h6v9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('orders.create') }}"
                   @click="open = false"
                   class="{{ request()->routeIs('orders.create') ? "$base $active" : "$base $inactive" }}">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <path d="M3 7h18M6 3h12M6 21h12M5 7l1 14h12l1-14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Crear pedido</span>
                </a>

                <a href="{{ route('products.index') }}"
                   @click="open = false"
                   class="{{ request()->routeIs('products.*') ? "$base $active" : "$base $inactive" }}">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        <path d="M3.3 7.7 12 12l8.7-4.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    <span>Productos</span>
                </a>
            </nav>

            {{-- Footer del drawer --}}
            <div class="border-t p-4">
                <div class="flex items-center gap-3 mb-4">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <img class="w-9 h-9 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}">
                    @endif
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="space-y-2">
                    <a href="{{ route('profile.show') }}" 
                       @click="open = false"
                       class="block text-center text-sm py-2 rounded-lg border hover:bg-gray-50 w-full">
                        Perfil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full text-center text-sm py-2 rounded-lg border hover:bg-gray-50">
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>