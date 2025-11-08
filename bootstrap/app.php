<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/verifactu.php', // ← Carga SOLO aquí verifactu.php
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Ping de cumplimiento (art. 9.1.a) una vez al día al iniciar el sistema
        // Se añade al GRUPO "web" en Laravel 11 (no existe app/Http/Kernel.php)
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\ComplianceBootPing::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();