<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\UserSetting;
use App\Observers\UserSettingObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings / singletons / configs condicionales
    }

    public function boot(): void
    {
        // Que las paginaciones usen Bootstrap 5 (coincide con tu UI)
        Paginator::useBootstrapFive();

        UserSetting::observe(UserSettingObserver::class);

        // Si quisieras forzar un locale o zona horaria, podrías hacerlo aquí:
        // app()->setLocale('es');
        // date_default_timezone_set(config('app.timezone', 'Europe/Madrid'));
    }
}
