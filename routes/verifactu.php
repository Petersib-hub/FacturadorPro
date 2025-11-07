<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Verifactu\OnboardingController;
use App\Http\Controllers\Verifactu\InvoiceVerificationController;
use App\Http\Controllers\Verifactu\ExportController;

Route::middleware(['api', 'auth:sanctum'])
    ->prefix('v1/verifactu') // ← SIN "api/" aquí
    ->name('verifactu.')
    ->group(function () {
        Route::post('/onboarding', [OnboardingController::class, 'store'])
            ->name('onboarding.store');

        Route::put('/invoices/{invoice}', [InvoiceVerificationController::class, 'update'])
            ->name('invoices.verify');

        Route::post('/exports', [ExportController::class, 'store'])
            ->name('exports.store');
    });