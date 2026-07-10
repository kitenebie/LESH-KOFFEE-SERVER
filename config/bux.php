<?php

return [

    /*
    |--------------------------------------------------------------------------
    | BUX.ph Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */

    'api_url' => env('BUX_API_URL', 'https://api.bux.ph/v1/api/sandbox/open/checkout'),

    'api_key' => env('BUX_API_KEY'),

    'auth' => env('BUX_AUTH'),

    'client_id' => env('BUX_CLIENT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret (for signature verification)
    |--------------------------------------------------------------------------
    | Used to verify HMAC-SHA256 signature on incoming webhooks.
    | Set to null to skip verification (development only).
    */
    'webhook_secret' => env('BUX_WEBHOOK_SECRET'),

];
