<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí defines las rutas y orígenes que pueden hacer peticiones cross-origin.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'docs', 'api/documentation'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:4200', 'http://localhost:8001'], // Cambia por tus dominios en producción

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
