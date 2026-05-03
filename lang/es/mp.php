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
    'device_saving'         => 'Guardando…',

    // UI - estado del point
    'device_status_active'  => 'Activo',
    'device_status_inactive'=> 'Inactivo',

    // UI - activar modo PDV
    'device_mode_pdv'        => 'Modo PDV',
    'device_mode_standalone' => 'Modo autónomo',
    'device_activate_pdv'    => 'Activar modo PDV',
    'device_activating'      => 'Activando…',
    'device_activated'       => 'Modo PDV activado. Reiniciá el dispositivo para que tome efecto.',
    'device_activate_error'  => 'Error al activar el modo PDV.',
    'device_restart_required'=> 'El dispositivo debe reiniciarse para que el cambio tome efecto.',

    // Overlay de espera de pago en terminal
    'payment_awaiting'           => 'Esperando pago en el terminal…',
    'payment_terminal_hint'      => 'Presentá la tarjeta o efectuá el pago en el dispositivo Point.',
    'payment_rejected'           => 'El pago fue rechazado o hubo un error en el terminal.',
    'payment_cancelled'          => 'El pago fue cancelado en el terminal.',
    'payment_cancelled_operator' => 'Cobro cancelado por el operador.',
    'payment_timeout'            => 'Tiempo de espera agotado. El cobro fue cancelado.',
    'payment_cancel_btn'         => 'Cancelar cobro',
    'payment_error_close'        => 'Cerrar',
];
