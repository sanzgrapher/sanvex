<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Key Encryption Key (KEK)
    |--------------------------------------------------------------------------
    |
    | Used by the Sanvex EncryptionService to encrypt Data Encryption Keys (DEKs).
    | Should be base64 encoded and 32 bytes long (e.g. from `php artisan key:generate`).
    |
    */
    'kek' => env('SANVEX_KEK', env('APP_KEY')),

    /*
    |--------------------------------------------------------------------------
    | Registered Drivers
    |--------------------------------------------------------------------------
    |
    | Define the list of driver classes that should be auto-registered with
    | Sanvex. Custom drivers can be added to this array.
    |
    */
    'drivers' => [
        // \Sanvex\Core\Drivers\ExampleDriver::class
    ],
];
