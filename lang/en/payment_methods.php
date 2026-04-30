<?php

return [
    // Index (company view)
    'title'               => 'Payment Methods',
    'info_title'          => 'Enable the payment methods you want to offer your customers',
    'info_body'           => 'Just activate or deactivate with one click. The technical setup is already done.',
    'badge_automatic'     => 'Automatic',
    'status_active_text'  => 'Active and available to your customers',
    'empty_title'         => 'No payment methods available',
    'empty_body'          => 'Contact the administrator to enable payment methods',

    // Master index
    'master_title'        => 'Payment Methods (Global)',
    'new_btn'             => 'New method',
    'master_subtitle'     => 'Manage the global methods available to all users.',
    'status_active'       => 'Active',
    'status_inactive'     => 'Inactive',
    'btn_deactivate'      => 'Deactivate',
    'btn_activate'        => 'Activate',
    'btn_edit'            => 'Edit',
    'empty_master'        => 'No global methods yet.',
    'empty_master_hint'   => 'You can create them with the "New method" button.',
    'current_config'      => 'Current configuration:',

    // Create / Edit form
    'create_title'           => 'New payment method',
    'edit_title'             => 'Edit payment method',
    'field_name'             => 'Name *',
    'field_slug'             => 'Slug *',
    'field_slug_hint'        => '(unique identifier)',
    'field_desc'             => 'Description',
    'field_icon'             => 'Icon',
    'field_icon_hint'        => '(heroicon)',
    'field_order'            => 'Order',
    'gateway_section'        => 'Gateway integration (optional)',
    'requires_gateway_label' => 'Requires gateway API integration',
    'gateway_provider_label' => 'Gateway provider',
    'gateway_select_ph'      => 'Select...',
    'gateway_info_create'    => 'API key configuration can be added after creating the method.',
    'gateway_info_edit'      => 'To configure API keys contact the system administrator.',
    'active_label'           => 'Activate payment method',
    'create_btn'             => 'Create payment method',
    'save_btn'               => 'Save changes',
    'cancel_btn'             => 'Cancel',
];
