<?php
/*
 | Back-end configuration for authenticus:
 | https://github.com/authenticus/auth-backend-laravel/
 | Does this config file need to be merged with config/auth.php?
 | As of now, we're keeping it separate.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | auth token name
    |--------------------------------------------------------------------------
    */
    'authenticus_token_name' => env('AUTHENTICUS_TOKEN_NAME', 'authenticus'),

    /*
    |--------------------------------------------------------------------------
    | When the auth token will expire (specified as an integer, in days)
    |--------------------------------------------------------------------------
    */
    'authenticus_token_expiration_days' => env('AUTHENTICUS_TOKEN_EXPIRATION_DAYS', 7),

];
