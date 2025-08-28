<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-gray': {
                            50: '#fafafa',
                            100: '#f5f5f5',
                            200: '#e5e5e5',
                            300: '#d4d4d4',
                            400: '#a3a3a3',
                            500: '#737373',
                            600: '#525252',
                            700: '#404040',
                            800: '#262626',
                            900: '#171717',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.015rem;
        }
        .input-focus-effect:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
        }
        .btn-transition {
            transition: all 0.2s ease;
        }
        .btn-transition:hover {
            transform: translateY(-1px);
        }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-custom-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <!-- Logo y título -->
            <div class="text-center mb-10">
                <div class="flex justify-center mb-5">
                    <div class="w-14 h-14 bg-custom-gray-900 rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-light text-custom-gray-900 tracking-tight">Acceder al sistema</h1>
                <p class="mt-2 text-sm text-custom-gray-500">Ingrese sus credenciales para continuar</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-lg shadow-sm border border-custom-gray-100 p-8">
                <!-- Validation Errors (simulado) -->
                <!-- <div class="mb-6 p-3 bg-red-50 text-red-700 text-sm rounded-md border border-red-100">
                    <p>Estas credenciales no coinciden con nuestros registros.</p>
                </div> -->

                <!-- Success Message (simulado) -->
                <!-- <div class="mb-6 p-3 bg-emerald-50 text-emerald-700 text-sm rounded-md border border-emerald-100">
                    <p>Se ha enviado un enlace de verificación a su correo electrónico.</p>
                </div> -->

                <form class="space-y-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-custom-gray-700 mb-1.5">Correo electrónico</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-custom-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <input id="email" type="email" class="w-full pl-10 pr-3 py-2.5 border border-custom-gray-200 rounded-md focus:outline-none input-focus-effect focus:border-blue-500 text-custom-gray-700 placeholder-custom-gray-400" placeholder="nombre@empresa.com">
                        </div>
                    </div>

                    <!-- Password -->
                    <div x-data="{ show: false }">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-medium text-custom-gray-700">Contraseña</label>
                            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500">¿Olvidó su contraseña?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-custom-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input id="password" :type="show ? 'text' : 'password'" class="w-full pl-10 pr-10 py-2.5 border border-custom-gray-200 rounded-md focus:outline-none input-focus-effect focus:border-blue-500 text-custom-gray-700 placeholder-custom-gray-400" placeholder="••••••••">
                            
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <svg x-show="!show" class="h-5 w-5 text-custom-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="show" x-cloak class="h-5 w-5 text-custom-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L17 17m-7.122-7.122L3 3"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" class="h-4 w-4 text-blue-600 border-custom-gray-300 rounded focus:ring-blue-500">
                        <label for="remember_me" class="ml-2 block text-sm text-custom-gray-700">Recordar sesión</label>
                    </div>

                    <!-- Submit button -->
                    <div>
                        <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm btn-transition text-sm font-medium text-white bg-custom-gray-800 hover:bg-custom-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Iniciar sesión
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-custom-gray-100">
                    <p class="text-sm text-custom-gray-500 text-center">
                        ¿No tiene una cuenta? 
                        <a href="#" class="font-medium text-blue-600 hover:text-blue-500">Regístrese aquí</a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-xs text-custom-gray-400">
                    &copy; 2023 Sistema. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</body>
</html>