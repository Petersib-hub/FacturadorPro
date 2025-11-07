<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Verifactu\ComplianceController;

Route::middleware(['web','auth'])
    ->prefix('verifactu')
    ->name('verifactu.web.')
    ->group(function () {
        Route::get('/', [ComplianceController::class, 'index'])->name('index');
        Route::post('/invoices/{invoice}/verify', [ComplianceController::class, 'verify'])->name('verify');
        Route::post('/exports', [ComplianceController::class, 'export'])->name('export');
    });
