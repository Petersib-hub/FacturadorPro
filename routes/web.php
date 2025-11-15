<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PublicBudgetController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecurringInvoiceController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\PublicClientPortalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Portada pública
Route::get('/', fn() => view('welcome'));

// Rutas protegidas (requiere login + email verificado)
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, '__invoke'])->name('dashboard');

    // Ajustes del negocio
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Clientes
    Route::resource('clients', ClientController::class);

    // Productos
    Route::resource('products', ProductController::class);

    // Presupuestos
    Route::resource('budgets', BudgetController::class);
    Route::get('budgets/{budget}/pdf',       [BudgetController::class, 'pdf'])->name('budgets.pdf');
    Route::post('budgets/{budget}/email',    [BudgetController::class, 'email'])->name('budgets.email');
    Route::post('budgets/{budget}/status',   [BudgetController::class, 'updateStatus'])->name('budgets.status');
    Route::post('budgets/{budget}/convert',  [InvoiceController::class, 'convertFromBudget'])->name('budgets.convert');

    // Facturas
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/payments',  [InvoiceController::class, 'registerPayment'])->name('invoices.payments.store');
    Route::get('invoices/{invoice}/pdf',        [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/email',     [InvoiceController::class, 'email'])->name('invoices.email');
    Route::post('invoices/{invoice}/mark-sent', [InvoiceController::class, 'markSent'])->name('invoices.markSent');
    Route::post('invoices/{invoice}/rectify',   [InvoiceController::class, 'rectify'])->name('invoices.rectify');

    // Facturas recurrentes
    Route::resource('recurring-invoices', RecurringInvoiceController::class);
    Route::post('recurring-invoices/{recurring_invoice}/duplicate', [RecurringInvoiceController::class, 'duplicate'])->name('recurring-invoices.duplicate');
    Route::put('recurring-invoices/{recurring_invoice}/pause',      [RecurringInvoiceController::class, 'pause'])->name('recurring-invoices.pause');
    Route::put('recurring-invoices/{recurring_invoice}/resume',     [RecurringInvoiceController::class, 'resume'])->name('recurring-invoices.resume');
    Route::post('recurring-invoices/{recurring_invoice}/run-now',   [RecurringInvoiceController::class, 'runNow'])->name('recurring-invoices.run-now');

    // Impuestos
    Route::resource('tax-rates', TaxRateController::class);

    // Registrar "copiar enlace público"
    Route::post('invoices/{invoice}/link-copied', function (\App\Models\Invoice $invoice) {
        \App\Support\Audit::record('invoice.public_link_copied', 'invoice', $invoice->id, [
            'invoice_no' => $invoice->number,
        ]);
        return response()->json([
            'url' => route('public.invoices.show', $invoice->public_token)
        ]);
    })->name('invoices.linkCopied');
});

// Perfil (protegido por auth)
Route::middleware('auth')->group(function () {
    Route::get('/profile',            [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo',     [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/profile/password',  [ProfileController::class, 'updatePassword'])->name('profile.password'); // NUEVA
    Route::delete('/profile',         [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===== Portal público por token =====
Route::prefix('pub')->name('public.')->group(function () {

    // Portal del cliente
    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('{token}', [PublicClientPortalController::class, 'index'])
            ->where('token', '[A-Za-z0-9]{24,128}')
            ->name('client');
    });

    // Presupuestos públicos
    Route::get('budgets/{token}', [PublicBudgetController::class, 'show'])
        ->where('token', '[A-Za-z0-9]{24,128}')
        ->name('budgets.show');
    Route::post('budgets/{token}/accept', [PublicBudgetController::class, 'accept'])
        ->where('token', '[A-Za-z0-9]{24,128}')
        ->name('budgets.accept');
    Route::post('budgets/{token}/reject', [PublicBudgetController::class, 'reject'])
        ->where('token', '[A-Za-z0-9]{24,128}')
        ->name('budgets.reject');

    // Facturas públicas
    Route::get('invoices/{token}', [PublicInvoiceController::class, 'show'])
        ->where('token', '[A-Za-z0-9]{24,128}')
        ->name('invoices.show');
});

// Informes
Route::get('/reports/invoices.csv', [\App\Http\Controllers\ReportsController::class,'invoicesCsv'])->name('reports.invoices.csv');
Route::get('/reports/budgets.csv',  [\App\Http\Controllers\ReportsController::class,'budgetsCsv'])->name('reports.budgets.csv');
Route::get('/reports/compliance.csv', [\App\Http\Controllers\ReportsController::class,'complianceCsv'])->name('reports.compliance.csv');

// Auth
require __DIR__ . '/auth.php';
// verifactu UI (si lo usas)
require __DIR__ . '/ui_verifactu.php';