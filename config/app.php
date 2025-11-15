<?php

use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    */
    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    */
    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    */
    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    */
    'timezone' => env('APP_TIMEZONE', 'Europe/Madrid'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    */
    'locale'          => env('APP_LOCALE', 'es'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'es'),
    'faker_locale'    => env('APP_FAKER_LOCALE', 'es_ES'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    */
    'cipher' => 'AES-256-CBC',
    'key'    => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    | Supported drivers: "file", "cache"
    */
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store'  => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | Usamos los proveedores por defecto de Laravel y añadimos los de la app.
    | IMPORTANTE: AuthServiceProvider registrado para que carguen Policies.
    |
    */
    'providers' => ServiceProvider::defaultProviders()->merge([
        // ----- Application Providers -----
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,

        // App\Providers\EventServiceProvider::class, // ← Descomenta si existe en tu proyecto
        // App\Providers\RouteServiceProvider::class,  // Laravel 11 ya no lo necesita
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    */
    'aliases' => [
        'App'     => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth'    => Illuminate\Support\Facades\Auth::class,
        'Blade'   => Illuminate\Support\Facades\Blade::class,
        'Cache'   => Illuminate\Support\Facades\Cache::class,
        'Config'  => Illuminate\Support\Facades\Config::class,
        'DB'      => Illuminate\Support\Facades\DB::class,
        'File'    => Illuminate\Support\Facades\File::class,
        'Log'     => Illuminate\Support\Facades\Log::class,
        'Mail'    => Illuminate\Support\Facades\Mail::class,
        'Route'   => Illuminate\Support\Facades\Route::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        // agrega aquí otros aliases que ya estuvieras usando…
    ],

];