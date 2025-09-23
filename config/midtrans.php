<?php 

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans API Key
    |--------------------------------------------------------------------------
    |
    | This key is used to authenticate requests to the Midtrans API.
    | Make sure to keep this key secure and do not expose it publicly.
    |
    */
    'api_key' => env('MIDTRANS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Client Key
    |--------------------------------------------------------------------------
    |
    | The client key for the Midtrans API. This is used for client-side operations.
    |
    */
    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Server Key
    |--------------------------------------------------------------------------
    |
    | The server key for the Midtrans API. This is used for server-side operations.
    |
    */
    'server_key' => env('MIDTRANS_SERVER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Environment
    |--------------------------------------------------------------------------
    |
    | Set to true for production or false for sandbox environment.
    |
    */
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    'is_3ds' => env('MIDTRANS_IS_3DS', true),
];