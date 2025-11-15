<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\Invoice;
use App\Models\Budget;
use App\Models\Client;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\RecurringInvoice;

use App\Policies\InvoicePolicy;
use App\Policies\BudgetPolicy;
use App\Policies\ClientPolicy;
use App\Policies\ProductPolicy;
use App\Policies\TaxRatePolicy;
use App\Policies\RecurringInvoicePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Invoice::class          => InvoicePolicy::class,
        Budget::class           => BudgetPolicy::class,
        Client::class           => ClientPolicy::class,
        Product::class          => ProductPolicy::class,
        TaxRate::class          => TaxRatePolicy::class,
        RecurringInvoice::class => RecurringInvoicePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // (Opcional para desarrollo) — descomenta si quieres bypass total para el usuario autenticado
        // \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
        //     return true; // ¡solo para pruebas puntuales!
        // });
    }
}