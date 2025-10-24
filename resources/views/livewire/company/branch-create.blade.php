<div class="max-w-2xl mx-auto p-6 text-neutral-900 dark:text-neutral-100">
    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow border border-gray-200 dark:border-neutral-800 p-6">
        <h2 class="text-xl font-semibold mb-6 text-neutral-900 dark:text-neutral-100">Crear Nueva Sucursal</h2>

        {{-- Mensajes de error global --}}
        @error('general')
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
                {{ $message }}
            </div>
        @enderror

        {{-- Mensaje de éxito --}}
        @if (session()->has('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form wire:submit.prevent="createBranch">
            {{-- Información de la Sucursal --}}
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-neutral-100 mb-4">Información de la Sucursal</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">
                            Nombre de la Sucursal <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               wire:model.defer="name" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="Ej: Sucursal Centro">
                        @error('name') 
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">Dirección</label>
                        <textarea wire:model.defer="address" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror"
                                  placeholder="Dirección completa de la sucursal"></textarea>
                        @error('address') 
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">Teléfono</label>
                            <input type="tel" 
                                   wire:model.defer="phone" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                                   placeholder="+54 11 1234-5678">
                            @error('phone') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">Email de Contacto</label>
                            <input type="email" 
                                   wire:model.defer="contact_email" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('contact_email') border-red-500 @enderror"
                                   placeholder="info@sucursal.com">
                            @error('contact_email') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">Para contacto público (diferente al de acceso)</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Credenciales de Acceso --}}
            <div class="mb-6 border-t border-gray-200 dark:border-neutral-800 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-neutral-100 mb-4">Credenciales de Acceso</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">
                            Email de Acceso <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               wire:model.defer="email" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="admin@sucursal.com">
                        @error('email') 
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Este email se usará para iniciar sesión</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">
                            Contraseña <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               wire:model.defer="password" 
                               class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                               placeholder="Mínimo 8 caracteres">
                        @error('password') 
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Inventario / Productos --}}
            <div class="mb-6 border-t border-gray-200 dark:border-neutral-800 pt-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-neutral-100 mb-4">Inventario</h3>
                <div class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800/70">
                    <div class="mr-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-neutral-100">Usar inventario de la empresa</div>
                        <div class="text-xs text-gray-600 dark:text-neutral-400">Comparte catálogo y stock con la empresa. Las ventas se diferencian por sucursal.</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="use_company_inventory" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer dark:bg-neutral-700 peer-checked:bg-blue-600 transition-all"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transform peer-checked:translate-x-5 transition-transform"></div>
                    </label>
                </div>
                @error('use_company_inventory')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Configuración --}}
            <div class="mb-6 border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Límite de Usuarios</label>
                        <input type="number" 
                               min="0"
                               wire:model.defer="user_limit" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('user_limit') border-red-500 @enderror"
                               placeholder="Dejar vacío = ilimitado">
                        @error('user_limit') 
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Máximo de usuarios que puede crear</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">Estado Inicial</label>
                        <select wire:model.defer="is_active" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="1">Activa</option>
                            <option value="0">Suspendida</option>
                        </select>
                        @error('is_active') 
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-neutral-800">
                <a href="{{ route('company.branches.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-neutral-800 text-gray-700 dark:text-neutral-200 rounded-md hover:bg-gray-50 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Volver
                </a>
                
                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors disabled:opacity-50"
                        wire:loading.attr="disabled">
                    
                    <div wire:loading.remove wire:target="createBranch">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Crear Sucursal
                    </div>
                    
                    <div wire:loading wire:target="createBranch">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creando...
                    </div>
                </button>
            </div>
        </form>
    </div>

    {{-- Información importante --}}
    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-medium mb-2">Información:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Se creará un registro de sucursal con los datos de negocio</li>
                    <li>Se creará un usuario administrador que podrá iniciar sesión</li>
                    <li>La sucursal podrá gestionar usuarios según su límite</li>
                    <li>Las credenciales se pueden usar inmediatamente para acceder</li>
                </ul>
            </div>
        </div>
    </div>
</div>
