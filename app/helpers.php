<?php

use App\Helpers\ModuleHelper;

if (!function_exists('current_module')) {
    /**
     * Obtener el módulo actual
     *
     * @return string|null
     */
    function current_module(): ?string
    {
        return ModuleHelper::getCurrentModule();
    }
}

if (!function_exists('module_bg')) {
    /**
     * Obtener la clase de background del módulo actual
     *
     * @param string|null $module
     * @return string
     */
    function module_bg(?string $module = null): string
    {
        return ModuleHelper::getModuleBgClass($module ?? current_module());
    }
}

if (!function_exists('module_color')) {
    /**
     * Obtener el color del módulo
     *
     * @param string|null $module
     * @return string
     */
    function module_color(?string $module = null): string
    {
        return ModuleHelper::getModuleColor($module ?? current_module());
    }
}

if (!function_exists('module_name')) {
    /**
     * Obtener el nombre legible del módulo
     *
     * @param string|null $module
     * @return string
     */
    function module_name(?string $module = null): string
    {
        return ModuleHelper::getModuleName($module ?? current_module());
    }
}
