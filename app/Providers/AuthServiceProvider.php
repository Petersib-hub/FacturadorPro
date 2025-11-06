<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// Models
use App\Models\Client;
use App\Models\Product;
use App\Models\Budget;
use App\Models\Invoice;
use App\Models\TaxRate;
use App\Models\RecurringInvoice;

// Policies
use App\Policies\ClientPolicy;
use App\Policies\ProductPolicy;
use App\Policies\BudgetPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\TaxRatePolicy;
use App\Policies\RecurringInvoicePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Client::class           => ClientPolicy::class,
        Product::class          => ProductPolicy::class,
        Budget::class           => BudgetPolicy::class,
        Invoice::class          => InvoicePolicy::class,
        TaxRate::class          => TaxRatePolicy::class,
        RecurringInvoice::class => RecurringInvoicePolicy::class,
        \App\Models\Product::class => \App\Policies\ProductPolicy::class,
        \App\Models\Invoice::class => \App\Policies\InvoicePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
