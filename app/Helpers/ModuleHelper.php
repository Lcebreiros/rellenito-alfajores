<?php

namespace App\Helpers;

class ModuleHelper
{
    /**
     * Detectar el módulo actual basado en la ruta
     *
     * @return string|null
     */
    public static function getCurrentModule(): ?string
    {
        $routeName = request()->route()?->getName() ?? '';

        // Mapeo de rutas a módulos
        $modules = [
            // Inicio - usar violeta (orders)
            'inicio' => 'orders',
            'home'   => 'orders',

            // Pedidos/Órdenes
            'orders' => 'orders',
            'order' => 'orders',

            // Productos
            'products' => 'products',
            'product' => 'products',

            // Clientes
            'clients' => 'clients',
            'client' => 'clients',

            // Dashboard
            'dashboard' => 'dashboard',

            // Gastos/Finanzas
            'expenses' => 'expenses',
            'expense' => 'expenses',
            'costs' => 'expenses',

            // Empresa/Sucursales/Configuración
            'company' => 'company',
            'settings' => 'company',
            'configuration' => 'company',

            // Empleados
            'employees' => 'employees',
            'employee' => 'employees',

            // Servicios
            'services' => 'services',
            'service' => 'services',

            // Stock
            'stock' => 'stock',

            // Estacionamiento (usar paleta de stock/cyan)
            'parking' => 'stock',

            // Métodos de Pago
            'payment-methods' => 'payment',
            'payment' => 'payment',
        ];

        // Buscar coincidencia en el nombre de la ruta
        foreach ($modules as $routePattern => $module) {
            if (str_contains($routeName, $routePattern)) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Obtener el color principal del módulo (Tailwind class)
     *
     * @param string|null $module
     * @return string
     */
    public static function getModuleColor(?string $module): string
    {
        return match($module) {
            'orders' => 'violet',
            'products' => 'sky',
            'clients' => 'emerald',
            'dashboard' => 'indigo',
            'expenses' => 'orange',
            'company' => 'slate',
            'employees' => 'teal',
            'services' => 'pink',
            'stock' => 'cyan',
            'payment' => 'yellow',
            default => 'neutral',
        };
    }

    /**
     * Obtener la clase CSS del background del módulo
     *
     * @param string|null $module
     * @return string
     */
    public static function getModuleBgClass(?string $module): string
    {
        $target = $module ?: 'neutral';
        return "module-bg-{$target}";
    }

    /**
     * Obtener el nombre legible del módulo
     *
     * @param string|null $module
     * @return string
     */
    public static function getModuleName(?string $module): string
    {
        return match($module) {
            'orders' => 'Pedidos',
            'products' => 'Productos',
            'clients' => 'Clientes',
            'dashboard' => 'Dashboard',
            'expenses' => 'Gastos',
            'company' => 'Empresa',
            'employees' => 'Personal',
            'services' => 'Servicios',
            'stock' => 'Stock',
            'payment' => 'Pagos',
            default => 'General',
        };
    }
}
