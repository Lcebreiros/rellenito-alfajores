<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Impresora Térmica - Configuración
    |--------------------------------------------------------------------------
    |
    | Dos métodos disponibles:
    |
    | 1. DIRECTO (Recomendado para simplicidad):
    |    PHP se comunica directamente con la impresora USB.
    |    No requiere servidor Node.js.
    |
    | 2. VIA SERVIDOR (Recomendado si usas Gate + Gestior):
    |    Usa servidor Node.js como intermediario.
    |    Permite que múltiples programas usen la misma impresora.
    |
    */

    // ========== MÉTODO DIRECTO (Sin servidor) ==========
    'direct' => [
        'enabled' => env('THERMAL_PRINTER_DIRECT_ENABLED', false),

        // En Linux: /dev/usb/lp0 o /dev/lp0
        // En Windows: nombre de la impresora (ej: "ThermalPrinter" o "POS-80")
        // Si se deja null, intentará detectar automáticamente
        'printer_path' => env('THERMAL_PRINTER_DIRECT_PATH', null),
    ],

    // ========== MÉTODO VIA SERVIDOR (Con Node.js) ==========
    // Habilitar/deshabilitar impresión automática
    'enabled' => env('THERMAL_PRINTER_ENABLED', true),

    // URL del servidor de impresión (generalmente localhost)
    'server_url' => env('THERMAL_PRINTER_SERVER_URL', 'http://localhost:9876'),

    // Timeout para las peticiones HTTP (segundos)
    'timeout' => env('THERMAL_PRINTER_TIMEOUT', 3),

    // Imprimir automáticamente al ingreso (al egreso se escanea el ticket)
    'auto_print' => [
        'parking_entry' => env('THERMAL_PRINTER_AUTO_ENTRY', true),
    ],
];
