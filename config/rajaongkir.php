<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RajaOngkir API Key
    |--------------------------------------------------------------------------
    |
    | This key is used to authenticate requests to the RajaOngkir API.
    | Make sure to keep this key secure and do not expose it publicly.
    |
    */
    'api_key' => env('RAJAONGKIR_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | RajaOngkir Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the RajaOngkir API. You can change this if you are using
    | a different environment or a custom endpoint.
    |
    */
    'base_url' => env('RAJAONGKIR_BASE_URL', 'https://api.rajaongkir.com/starter'),
];
