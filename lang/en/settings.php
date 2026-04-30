<?php

return [
    // App info
    'app_description'           => 'Management and financial control tool',

    // Themes
    'themes_title'              => 'Themes',
    'themes_subtitle'           => 'Choose a visual theme to personalize your experience',
    'theme_mode_label'          => 'Theme mode',
    'theme_mode_hint'           => 'Choose between light or dark mode',
    'custom_color_label'        => 'Select your custom color',
    'save_color_btn'            => 'Save Color',
    'color_hint'                => 'Enter a valid hex code (example: #6366f1) or use the color picker.',
    'instant_apply'             => 'Changes apply instantly without reloading the page.',

    // Currency
    'currency_title'            => 'Currency',
    'currency_subtitle'         => 'Choose the currency to use for prices and amounts',
    'currency_preview'          => 'Preview:',
    'save_currency_btn'         => 'Save currency',

    // Timezone
    'timezone_title'            => 'Timezone',
    'timezone_select_label'     => 'Select your timezone',
    'timezone_select_hint'      => 'Configure your timezone to see correct dates and times throughout the app',
    'filter_by_country'         => 'Filter by country',
    'search_timezone'           => 'Search timezone',
    'timezone_placeholder'      => 'City or zone (e.g. Buenos Aires, London)',
    'use_argentina_btn'         => '🇦🇷 Use Argentina',
    'clear_btn'                 => 'Clear',
    'no_countries'              => 'No countries available.',
    'no_results'                => 'No results',
    'search_other_terms'        => 'Try other search terms',
    'tz_configured'             => 'Configured:',
    'tz_not_configured'         => 'Not configured',
    'save_config_btn'           => 'Save configuration',
    'tz_tip'                    => 'If you work in Argentina, select America/Argentina/Buenos_Aires to get the correct local time.',

    // Identity
    'identity_title'            => 'Identity',
    'site_title_label'          => 'Site title',
    'save_btn'                  => 'Save',

    // Stock Notifications
    'stock_notif_title'         => 'Stock Notifications',
    'stock_notif_subtitle'      => 'Configure automatic alerts',
    'low_stock_alert_title'     => 'Low Stock Alert',
    'low_stock_alert_desc'      => 'Receive a notification when stock falls below the configured threshold',
    'low_stock_threshold_label' => 'Low stock threshold',
    'units_suffix'              => 'units',
    'min_label'                 => 'Minimum: 1',
    'notif_when_below'          => 'We will notify you when a product has :n or fewer units in stock.',
    'out_of_stock_alert_title'  => 'Out of Stock Alert',
    'out_of_stock_alert_desc'   => 'Receive a notification when a product runs out of stock (0 units)',
    'notif_all_products'        => 'Notifications apply to all your products',

    // Rentals hours
    'rentals_title'             => 'Rentals / Courts',
    'rentals_subtitle'          => 'Operating hours for bookings',
    'rentals_hours_desc'        => 'Configure the opening and closing times used to show available slots in the courts view.',
    'open_time_label'           => 'Opening',
    'close_time_label'          => 'Closing',
    'save_hours_btn'            => 'Save hours',

    // Google Calendar
    'google_cal_subtitle'       => 'Automatic event synchronization',
    'google_connected'          => 'Account connected',
    'auto_sync_title'           => 'Automatic synchronization',
    'auto_sync_desc'            => 'Scheduled sales will be automatically saved to your Google Calendar',
    'syncs_auto_title'          => 'Syncs automatically:',
    'sync_item_orders'          => 'Scheduled sales (when you create or edit a sale with a date)',
    'sync_item_update'          => 'Automatic updates when dates or details change',
    'sync_item_delete'          => 'Automatic deletion when sales are cancelled or deleted',
    'sync_disabled_title'       => 'Synchronization disabled',
    'sync_disabled_desc'        => 'New sales will only be saved in Gestior. Enable synchronization to see your sales in Google Calendar.',
    'privacy_note'              => 'Privacy: We only access your calendar to create events for your sales. We do not share your information with third parties. You can disconnect your account at any time.',
    'disconnect_confirm'        => "Are you sure you want to disconnect your Google Calendar account?\n\nExisting events will remain in your calendar, but new sales will not be synced.",
    'disconnect_google_btn'     => 'Disconnect Google Calendar',
    'connect_benefits_title'    => 'When you connect your Google Calendar:',
    'benefit_1'                 => 'Your scheduled sales will automatically appear in your calendar',
    'benefit_2'                 => 'You will receive automatic reminders before each sale',
    'benefit_3'                 => 'You will see your sales on all your synced devices',
    'benefit_4'                 => 'Changes update in real time',
    'privacy_title'             => 'Your privacy matters:',
    'privacy_1'                 => 'We only access your calendar to create sales events',
    'privacy_2'                 => 'We do not read your other events or personal information',
    'privacy_3'                 => 'We do not share your data with third parties',
    'privacy_4'                 => 'You can disconnect at any time',
    'connect_google_btn'        => 'Connect with Google Calendar',
    'google_redirect_hint'      => 'You will be redirected to Google to authorize access securely',

    // Receipt logo
    'receipt_logo_title'        => 'Receipt Logo',
    'preview_label'             => 'Preview',
    'dropzone_label'            => 'Drag an image or click',
    'dropzone_hint'             => 'Accepts .png, .jpg, .jpeg, .webp (max 2 MB)',
    'remove_btn'                => 'Remove',
    'receipt_logo_tip'          => 'Tip: upload a horizontal version with a transparent background so it looks good on the ticket.',

    // Modules config
    'modules_title'             => 'Panel Modules',
    'modules_subtitle'          => 'Customize which modules you see in your sidebar',
    'modules_info'              => 'Select only the modules you use in your business. Fixed modules (Dashboard, Sales, Payment Methods, Stock, Expenses, Settings and Support) will always be available.',
    'module_visible'            => 'Visible',
    'module_hidden'             => 'Hidden',
    'modules_selected_suffix'   => 'modules selected',
    'of_label'                  => 'of',
    'save_changes_btn'          => 'Save changes',

    // Generic
    'saving'                    => 'Saving...',

    // App logo form
    'logo_no_logo'      => 'No logo',
    'logo_current'      => 'Current logo',
    'logo_hint'         => 'Recommended: PNG with transparent background, minimum 128×128.',
    'logo_uploading'    => 'Uploading…',
    'logo_save_btn'     => 'Save logo',
    'logo_remove_btn'   => 'Remove logo',

    // Profile page
    'profile_heading'          => 'My profile',
    'profile_page_title'       => 'Account settings',
    'profile_page_subtitle'    => 'Manage your personal information, security and app preferences.',
    'profile_personal_title'   => 'Personal information',
    'profile_personal_sub'     => 'Name, email and profile photo.',
    'profile_password_title'   => 'Security: Password',
    'profile_password_sub'     => 'Update your password regularly.',
    'profile_2fa_title'        => 'Two-factor authentication (2FA)',
    'profile_2fa_sub'          => 'Protect your account with a second verification step.',
    'profile_logo_title'       => 'Customization: App logo',
    'profile_logo_sub'         => 'Upload a logo for the header and sidebar.',
    'profile_sessions_title'   => 'Browser sessions',
    'profile_sessions_sub'     => 'Log out other active sessions.',
    'profile_delete_title'     => 'Delete account',
    'profile_delete_sub'       => 'Permanently deletes your data.',

    // Payment method selector
    'pm_select_title'   => 'Payment Methods',
    'pm_select_aria'    => 'Select :name',
    'pm_no_methods'     => 'No payment methods configured',
    'pm_no_methods_hint'=> 'Go to <a href=":url" class="underline hover:text-amber-900 dark:hover:text-amber-100 font-semibold">Payment Methods</a> to add some.',
];
