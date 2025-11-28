{{-- MOBILE: barra superior (componente parcial) --}}
  @php
    $levelLabel = null;
    if (Auth::check()) {
        $roles = Auth::user()->getRoleNames()->toArray();
        $firstRole = $roles[0] ?? null;
        if ($firstRole) {
            $roleMap = [
                'company' => 'Empresa',
                'admin'   => 'Sucursal',
                'user'    => 'Usuario',
                'master'  => 'Master',
            ];
            $levelLabel = $roleMap[$firstRole] ?? Str::title(str_replace(['-', '_'], ' ', $firstRole));
        } else {
            switch (Auth::user()->hierarchy_level) {
                case \App\Models\User::HIERARCHY_MASTER:  $levelLabel = 'Master'; break;
                case \App\Models\User::HIERARCHY_COMPANY: $levelLabel = 'Empresa'; break;
                case \App\Models\User::HIERARCHY_ADMIN:   $levelLabel = 'Sucursal'; break;
                case \App\Models\User::HIERARCHY_USER:    $levelLabel = 'Usuario'; break;
                default: $levelLabel = null; break;
            }
        }
    }
  @endphp
<div class="md:hidden w-full bg-white dark:bg-neutral-900 border-b border-neutral-200 dark:border-neutral-800 no-print mobile-header-glass"
     x-data="{ moreOpen: false }">
    <div class="h-14 flex items-center justify-between px-4">
        {{-- Logo y título --}}
        <a href="{{ route('inicio') }}" class="inline-flex items-center gap-2">
            <x-application-mark class="h-8 w-auto" />
            <span class="font-semibold text-neutral-900 dark:text-neutral-100">Panel</span>
            @if($levelLabel)
              <span class="ml-1 text-sm font-semibold text-neutral-500 dark:text-neutral-400">{{ $levelLabel }}</span>
            @endif
        </a>

        {{-- Botón "Más" con dropdown --}}
        <div class="relative">
            <button type="button"
                    @click.stop="moreOpen = !moreOpen"
                    @keydown.escape.window="moreOpen = false"
                    @click.outside="moreOpen = false"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-full
                           bg-neutral-100 dark:bg-neutral-800 
                           hover:bg-neutral-200 dark:hover:bg-neutral-700
                           active:bg-neutral-300 dark:active:bg-neutral-600
                           transition-all duration-200 ring-1 ring-neutral-200 dark:ring-neutral-700"
                    aria-haspopup="menu"
                    :aria-expanded="moreOpen">
                @auth
                    <img class="w-7 h-7 rounded-full object-cover ring-2 ring-white dark:ring-neutral-700"
                         src="{{ Auth::user()->profile_photo_url }}" 
                         alt="{{ Auth::user()->name }}">
                @else
                    <div class="w-7 h-7 rounded-full bg-neutral-300 dark:bg-neutral-600"></div>
                @endauth
            </button>

            {{-- Dropdown menu --}}
            <div x-cloak x-show="moreOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                 @click.stop
                 class="absolute right-0 top-[calc(100%+8px)] min-w-[16rem] max-w-xs rounded-2xl
                        border border-neutral-200/60 dark:border-neutral-700/60
                        bg-white/95 dark:bg-neutral-900/95 backdrop-blur-xl
                        shadow-xl shadow-neutral-900/10 dark:shadow-black/25
                        overflow-hidden ring-1 ring-black/5 dark:ring-white/10 z-50">
                
                {{-- Header del dropdown --}}
                <div class="px-4 py-3 border-b border-neutral-200/50 dark:border-neutral-800/50 bg-neutral-50/50 dark:bg-neutral-800/30">
                    <div class="flex items-center gap-3">
                        @auth
                            <img class="w-10 h-10 rounded-full object-cover ring-2 ring-neutral-300 dark:ring-neutral-600"
                                 src="{{ Auth::user()->profile_photo_url }}" 
                                 alt="{{ Auth::user()->name }}">
                        @else
                            <div class="w-10 h-10 rounded-full bg-neutral-200 dark:bg-neutral-700"></div>
                        @endauth
                        <div class="flex-1 min-w-0">
                            @auth
                                <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                    {{ Auth::user()->name }}
                                </p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                                    {{ Auth::user()->email }}
                                </p>
                            @else
                                <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Invitado</p>
                            @endauth
                        </div>
                    </div>
                </div>

                {{-- Menu items --}}
                <div class="py-2">
                    {{-- Configuración --}}
                    <a href="{{ Route::has('settings') ? route('settings') : (Route::has('settings.index') ? route('settings.index') : (Route::has('profile.show') ? route('profile.show') : '#')) }}" 
                       wire:navigate data-turbo="false"
                       @click="moreOpen = false"
                       class="flex items-center gap-3 px-4 py-3 text-sm group 
                              hover:bg-neutral-50 dark:hover:bg-neutral-800/50 
                              active:bg-neutral-100 dark:active:bg-neutral-700/50 
                              transition-all duration-200 touch-manipulation">
                        <div class="w-9 h-9 rounded-xl bg-neutral-100 dark:bg-neutral-800 
                                    flex items-center justify-center 
                                    group-hover:bg-neutral-200 dark:group-hover:bg-neutral-700 
                                    transition-colors">
                            <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-400" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="font-medium text-neutral-700 dark:text-neutral-300 
                                     group-hover:text-neutral-900 dark:group-hover:text-neutral-100">
                            Configuración
                        </span>
                    </a>

                    {{-- Perfil --}}
                    <a href="{{ Route::has('profile.show') ? route('profile.show') : '#' }}" 
                       wire:navigate data-turbo="false"
                       @click="moreOpen = false"
                       class="flex items-center gap-3 px-4 py-3 text-sm group 
                              hover:bg-neutral-50 dark:hover:bg-neutral-800/50 
                              active:bg-neutral-100 dark:active:bg-neutral-700/50 
                              transition-all duration-200 touch-manipulation">
                        <div class="w-9 h-9 rounded-xl bg-neutral-100 dark:bg-neutral-800 
                                    flex items-center justify-center 
                                    group-hover:bg-neutral-200 dark:group-hover:bg-neutral-700 
                                    transition-colors">
                            @auth
                                <img class="w-6 h-6 rounded-lg object-cover" 
                                     src="{{ Auth::user()->profile_photo_url }}" 
                                     alt="{{ Auth::user()->name }}">
                            @else
                                <svg class="w-5 h-5 text-neutral-600 dark:text-neutral-400" 
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            @endif
                        </div>
                        <span class="font-medium text-neutral-700 dark:text-neutral-300 
                                     group-hover:text-neutral-900 dark:group-hover:text-neutral-100">
                            Perfil
                        </span>
                    </a>
                </div>

                {{-- Separador --}}
                <div class="mx-4 border-t border-neutral-200/60 dark:border-neutral-800/60"></div>

                {{-- Logout --}}
                <div class="py-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                @click="moreOpen = false"
                                class="w-full flex items-center gap-3 px-4 py-3 text-left text-sm group
                                       hover:bg-red-50 dark:hover:bg-red-950/30
                                       active:bg-red-100 dark:active:bg-red-900/30 
                                       transition-all duration-200 touch-manipulation">
                            <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/50 
                                        flex items-center justify-center 
                                        group-hover:bg-red-100 dark:group-hover:bg-red-900/50 
                                        transition-colors">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 013 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="font-medium text-red-600 dark:text-red-400 
                                         group-hover:text-red-700 dark:group-hover:text-red-300">
                                Cerrar sesión
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
