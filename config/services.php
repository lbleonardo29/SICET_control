<?php

return [

    /* |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Integración con la base corporativa "tickets".
    // write_through: escribir de vuelta empleados al crear/editar en SICET.
    // sync_enabled: permitir que corra el comando sicet:sync-empleados.
    'tickets' => [
        'write_through' => (bool) env('TICKETS_WRITE_THROUGH', false),
        'sync_enabled' => (bool) env('TICKETS_SYNC_ENABLED', false),
    ],

];
