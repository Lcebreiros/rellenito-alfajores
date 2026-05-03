<?php

return [
    'oauth_invalid_state'   => 'Invalid security state. Please try connecting again.',
    'oauth_denied'          => 'You did not authorize access to your Mercado Pago account.',
    'oauth_no_code'         => 'No authorization code was received from Mercado Pago.',
    'oauth_exchange_failed' => 'Error obtaining Mercado Pago credentials',
    'oauth_connected'       => 'Your Mercado Pago account was connected successfully.',
    'oauth_disconnected'    => 'Mercado Pago account disconnected.',

    'not_connected'         => 'No Mercado Pago account is connected.',
    'no_device_selected'    => 'No Point device is selected.',

    'panel_title'           => 'Mercado Pago Account',
    'panel_desc'            => 'Connect your account to charge with Point and QR directly from the system.',
    'connect_btn'           => 'Connect with Mercado Pago',
    'disconnect_btn'        => 'Disconnect account',
    'disconnect_confirm'    => 'Disconnect your Mercado Pago account? Device configuration will be lost.',
    'connected_as'          => 'Connected as',
    'expires'               => 'Expires',
    'never_expires'         => 'No expiration date',

    'device_title'          => 'Point Device',
    'device_desc'           => 'Select the Point device that will be used to charge at this location.',
    'device_select_placeholder' => 'Select device…',
    'device_loading'        => 'Loading devices…',
    'device_error'          => 'Error loading devices. Check the connection.',
    'device_empty'          => 'No Point devices found in your account.',
    'device_saved'          => 'Device selected successfully.',
    'device_save_btn'       => 'Save device',
    'device_saving'         => 'Saving…',

    'device_status_active'  => 'Active',
    'device_status_inactive'=> 'Inactive',

    'device_mode_pdv'        => 'PDV Mode',
    'device_mode_standalone' => 'Standalone mode',
    'device_activate_pdv'    => 'Activate PDV mode',
    'device_activating'      => 'Activating…',
    'device_activated'       => 'PDV mode activated. Restart the device for the change to take effect.',
    'device_activate_error'  => 'Error activating PDV mode.',
    'device_restart_required'=> 'The device must be restarted for the change to take effect.',

    'payment_awaiting'           => 'Waiting for payment on terminal…',
    'payment_terminal_hint'      => 'Present the card or complete the payment on the Point device.',
    'payment_rejected'           => 'The payment was rejected or an error occurred on the terminal.',
    'payment_cancelled'          => 'The payment was cancelled on the terminal.',
    'payment_cancelled_operator' => 'Payment cancelled by operator.',
    'payment_timeout'            => 'Timeout reached. The payment was cancelled.',
    'payment_cancel_btn'         => 'Cancel payment',
    'payment_error_close'        => 'Close',
];
