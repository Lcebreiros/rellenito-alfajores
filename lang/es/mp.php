<?php

return [
    // OAuth flow
    'oauth_invalid_state'   => 'Estado de seguridad inválido. Intentá conectar de nuevo.',
    'oauth_denied'          => 'No autorizaste el acceso a tu cuenta de Mercado Pago.',
    'oauth_no_code'         => 'No se recibió el código de autorización de Mercado Pago.',
    'oauth_exchange_failed' => 'Error al obtener las credenciales de Mercado Pago',
    'oauth_connected'       => 'Tu cuenta de Mercado Pago fue conectada correctamente.',
    'oauth_disconnected'    => 'Cuenta de Mercado Pago desconectada.',

    // Errors
    'not_connected'         => 'No hay una cuenta de Mercado Pago conectada.',
    'no_device_selected'    => 'No hay ningún dispositivo Point seleccionado.',

    // UI - panel de conexión
    'panel_title'           => 'Cuenta de Mercado Pago',
    'panel_desc'            => 'Conectá tu cuenta para cobrar con Point y QR directamente desde el sistema.',
    'connect_btn'           => 'Conectar con Mercado Pago',
    'disconnect_btn'        => 'Desconectar cuenta',
    'disconnect_confirm'    => '¿Desconectar tu cuenta de Mercado Pago? Perderás la configuración del dispositivo.',
    'connected_as'          => 'Conectado como',
    'expires'               => 'Expira',
    'never_expires'         => 'Sin fecha de vencimiento',

    // UI - selector de dispositivos
    'device_title'          => 'Dispositivo Point',
    'device_desc'           => 'Seleccioná el Point que se usará para cobrar en este local.',
    'device_select_placeholder' => 'Seleccionar dispositivo…',
    'device_loading'        => 'Cargando dispositivos…',
    'device_error'          => 'Error al cargar dispositivos. Verificá la conexión.',
    'device_empty'          => 'No se encontraron dispositivos Point en tu cuenta.',
    'device_saved'          => 'Dispositivo seleccionado correctamente.',
    'device_save_btn'       => 'Guardar dispositivo',

    // UI - estado del point
    'device_status_active'  => 'Activo',
    'device_status_inactive'=> 'Inactivo',
];
