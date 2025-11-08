<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\UserSetting;
use App\Observers\UserSettingObserver;
use App\Contracts\Fiskaly\FiskalyClientInterface;
use App\Services\Fiskaly\FiskalyClient;

use App\Models\Invoice;
use App\Models\Budget;
use App\Models\InvoicePayment;
use App\Observers\InvoiceObserver;
use App\Observers\BudgetObserver;
use App\Observers\InvoicePaymentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bindings / singletons / configs condicionales
        $this->app->bind(FiskalyClientInterface::class, FiskalyClient::class);
    }

    public function boot(): void
    {
        // Que las paginaciones usen Bootstrap 5 (coincide con tu UI)
        Paginator::useBootstrapFive();

        UserSetting::observe(UserSettingObserver::class);

        // Si quisieras forzar un locale o zona horaria, podrías hacerlo aquí:
        // app()->setLocale('es');
        // date_default_timezone_set(config('app.timezone', 'Europe/Madrid'));
        Invoice::observe(InvoiceObserver::class);
        Budget::observe(BudgetObserver::class);
        InvoicePayment::observe(InvoicePaymentObserver::class);

    }
}
