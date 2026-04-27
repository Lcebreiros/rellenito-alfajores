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

if (!function_exists('currency_symbol')) {
    /**
     * Símbolo de la moneda configurada por el usuario actual.
     * Ej: "$", "R$", "€"
     */
    function currency_symbol(?string $code = null): string
    {
        return \App\Services\CurrencyService::symbol($code);
    }
}

if (!function_exists('format_price')) {
    /**
     * Formatea un monto con símbolo y separadores de la moneda del usuario.
     * Ej: format_price(1234.5) → "$ 1.234,50"
     */
    function format_price(float|int|string $amount, ?string $code = null): string
    {
        return \App\Services\CurrencyService::format($amount, $code);
    }
}

if (!function_exists('currency_locale')) {
    /**
     * Locale BCP-47 de la moneda actual, para pasar a toLocaleString() en JS.
     * Ej: "es-AR", "pt-BR"
     */
    function currency_locale(?string $code = null): string
    {
        return \App\Services\CurrencyService::locale($code);
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
