<?php

return [
    'generic' => [
        'productos',
        'servicios',
        'proyectos',
        'sucursales',
        'empleados',
        'clientes',
        'alquileres',
    ],
    'tienda' => [
        'productos',
        'servicios',
        'clientes',
    ],
    'club' => [
        'alquileres',
        'clientes',
        'empleados',
    ],
    'empresa' => [
        // vacío a propósito, se puede personalizar luego
    ],
    // Mantenido por compatibilidad con datos existentes
    'estacionamiento' => [
        'clientes',
    ],
];
