<?php

return [
    'client_id'     => env('MP_CLIENT_ID'),
    'client_secret' => env('MP_CLIENT_SECRET'),
    'redirect_uri'  => env('MP_REDIRECT_URI', env('APP_URL', 'http://localhost') . '/mercadopago/callback'),

    'auth_url' => 'https://auth.mercadopago.com/authorization',
    'api_url'  => 'https://api.mercadopago.com',

    // Segundos antes de expiración para hacer refresh proactivo
    'refresh_buffer_seconds' => 86400, // 1 día
];
