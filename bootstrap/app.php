<?php

use App\Http\Controllers\CspReportController;
use App\Http\Middleware\CheckBlock;
use App\Http\Middleware\ContentSecurityPolicy;
use App\Services\Errors\ErrorRecorder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
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
        // The app only ever sits behind nginx (container) and the university's reverse proxy:
        // the client IP and the scheme come from the X-Forwarded-* headers they set.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        $middleware->web(append: [CheckBlock::class]);

        // Global, so error pages and redirects carry the policy too.
        $middleware->append(ContentSecurityPolicy::class);

        // 'csp:enforce' / 'csp:report-only' per route or group while the views migrate.
        $middleware->alias([
            'csp' => ContentSecurityPolicy::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every reported error (the 500s; Laravel leaves 404, validation and authentication out) carries who and
        // where in the log, and is kept, grouped and announced to the lecturers by the error recorder
        $exceptions->context(fn () => app(ErrorRecorder::class)->context());
        $exceptions->report(function (Throwable $exception): void {
            app(ErrorRecorder::class)->record($exception);
        });
    })->create();
