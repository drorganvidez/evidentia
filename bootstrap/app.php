<?php

use App\Http\Controllers\CspReportController;
use App\Http\Middleware\CheckBlock;
use App\Http\Middleware\ContentSecurityPolicy;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Outside the "web" group: the browser posts here without a session or a CSRF token.
            Route::post('/csp-report', CspReportController::class)
                ->middleware('throttle:60,1')
                ->name('csp.report');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            CheckBlock::class,
            ContentSecurityPolicy::class,
        ]);

        // 'csp:enforce' / 'csp:report-only' per route or group while the views migrate.
        $middleware->alias([
            'csp' => ContentSecurityPolicy::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
