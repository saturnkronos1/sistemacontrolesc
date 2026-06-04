<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel Cloud Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para el despliegue en Laravel Cloud.
    | Ver: https://cloud.laravel.com/docs/deployments
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Build Commands
    |--------------------------------------------------------------------------
    |
    | Comandos que se ejecutan durante la fase de build del deployment.
    |
    */
    'build' => [
        'composer install --no-dev --optimize-autoloader',
        'npm ci',
        'npm run build',
        'php artisan storage:link',
    ],

    /*
    |--------------------------------------------------------------------------
    | Deploy Commands
    |--------------------------------------------------------------------------
    |
    | Comandos que se ejecutan durante la activación del deployment.
    |
    */
    'deploy' => [
        'php artisan migrate --force',
        'php artisan config:cache',
        'php artisan route:cache',
        'php artisan view:cache',
        'php artisan event:cache',
        'php artisan queue:restart',
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Variables
    |--------------------------------------------------------------------------
    |
    | Variables de entorno requeridas en producción.
    |
    */
    'env' => [
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'APP_URL' => '${APP_URL}',
        'LOG_LEVEL' => 'warning',
        'DB_CONNECTION' => 'mysql',
        'FILESYSTEM_DISK' => 'local',
    ],
];
