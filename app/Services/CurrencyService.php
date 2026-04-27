<?php

namespace App\Services;

use App\Models\Setting;

class CurrencyService
{
    /**
     * Catálogo de monedas soportadas.
     * Fuente de verdad única para símbolo, locale y separadores.
     */
    public const CURRENCIES = [
        'ARS' => [
            'name'      => 'Peso Argentino',
            'flag'      => '🇦🇷',
            'symbol'    => '$',
            'locale'    => 'es-AR',
            'decimal'   => ',',
            'thousands' => '.',
        ],
        'UYU' => [
            'name'      => 'Peso Uruguayo',
            'flag'      => '🇺🇾',
            'symbol'    => '$U',
            'locale'    => 'es-UY',
            'decimal'   => ',',
            'thousands' => '.',
        ],
        'USD' => [
            'name'      => 'Dólar Americano',
            'flag'      => '🇺🇸',
            'symbol'    => 'US$',
            'locale'    => 'en-US',
            'decimal'   => '.',
            'thousands' => ',',
        ],
        'EUR' => [
            'name'      => 'Euro',
            'flag'      => '🇪🇺',
            'symbol'    => '€',
            'locale'    => 'es-ES',
            'decimal'   => ',',
            'thousands' => '.',
        ],
        'BRL' => [
            'name'      => 'Real',
            'flag'      => '🇧🇷',
            'symbol'    => 'R$',
            'locale'    => 'pt-BR',
            'decimal'   => ',',
            'thousands' => '.',
        ],
    ];

    /** Código de la moneda actualmente configurada para el usuario. */
    public static function current(): string
    {
        $code = Setting::get('currency', 'ARS');

        return array_key_exists($code, self::CURRENCIES) ? $code : 'ARS';
    }

    /** Símbolo de la moneda (ej: "$", "R$", "€"). */
    public static function symbol(?string $code = null): string
    {
        return self::config($code)['symbol'];
    }

    /** Locale BCP-47 para Intl / toLocaleString en JS (ej: "es-AR"). */
    public static function locale(?string $code = null): string
    {
        return self::config($code)['locale'];
    }

    /**
     * Formatea un monto con el símbolo y los separadores correctos.
     * Ej: format(1234.5) → "$ 1.234,50" (ARS)
     */
    public static function format(float|int|string $amount, ?string $code = null): string
    {
        $cfg = self::config($code);

        return $cfg['symbol'] . ' ' . number_format((float) $amount, 2, $cfg['decimal'], $cfg['thousands']);
    }

    /** Devuelve el array completo de una moneda (o la actual si $code es null). */
    public static function config(?string $code = null): array
    {
        $code ??= self::current();

        return self::CURRENCIES[$code] ?? self::CURRENCIES['ARS'];
    }

    /** Devuelve todos los códigos válidos. */
    public static function codes(): array
    {
        return array_keys(self::CURRENCIES);
    }
}
