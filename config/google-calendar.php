<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Calendar Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Google Calendar integration.
    | You need to create credentials in Google Cloud Console:
    | https://console.cloud.google.com/
    |
    */

    'client_id' => env('GOOGLE_CLIENT_ID'),

    'client_secret' => env('GOOGLE_CLIENT_SECRET'),

    'redirect_uri' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/google/callback'),

    'scopes' => [
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/calendar.events',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Settings
    |--------------------------------------------------------------------------
    |
    | Configure how events are created in Google Calendar
    |
    */

    'event_settings' => [
        // Send email notifications when creating events
        'send_notifications' => true,

        // Default reminder minutes before event
        'default_reminder_minutes' => 60,

        // Color IDs for different event types
        // See: https://developers.google.com/calendar/api/v3/reference/colors
        'colors' => [
            'order' => '9', // Blue
            'payment' => '11', // Red
            'purchase' => '10', // Green
        ],
    ],
];
