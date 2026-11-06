<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Strict, nonce-based Content-Security-Policy.
 *
 * - A fresh nonce per request through Vite::useCspNonce(): @vite and @fonts add it to every
 *   <script>, <link rel="stylesheet">, <link rel="modulepreload"> and <style> they print.
 * - No 'unsafe-inline' and no 'unsafe-eval'. Alpine runs from @alpinejs/csp for that reason.
 * - Registered globally, so error pages and redirects carry the policy too.
 * - While the Vite dev server is running (public/hot exists) its origin is allowed over http
 *   and ws so modules and HMR work. Production never has a hot file.
 * - Optional route parameter: 'csp:enforce' or 'csp:report-only'. Without it the mode comes
 *   from config('csp.mode'). A policy already set by an inner layer (a route group with
 *   'csp:enforce' inside the report-only default) is respected.
 * - X-Frame-Options DENY always, because frame-ancestors does nothing in report-only mode.
 */
final class ContentSecurityPolicy
{
    public const ENFORCE = 'enforce';

    public const REPORT_ONLY = 'report-only';

    /** Endpoint name used by the Reporting-Endpoints header and the report-to directive. */
    public const REPORTING_ENDPOINT = 'csp';

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, ?string $mode = null): Response
    {
        if (! config('csp.enabled', true)) {
            return $next($request);
        }

        // Before the view renders, so @vite already knows the nonce.
        Vite::useCspNonce();

        $response = $next($request);

        if ($this->alreadyHasPolicy($response)) {
            return $response;
        }

        $mode ??= (string) config('csp.mode', self::REPORT_ONLY);

        $header = $mode === self::ENFORCE
            ? 'Content-Security-Policy'
            : 'Content-Security-Policy-Report-Only';

        // headers->set() exists on every Symfony response, including the StreamedResponse of
        // Storage::download() and the BinaryFileResponse of Excel::download(); withHeaders() does not.
        $response->headers->set($header, $this->policy($request));
        $response->headers->set('X-Frame-Options', 'DENY');

        if ($this->reportingEnabled()) {
            $response->headers->set(
                'Reporting-Endpoints',
                sprintf('%s="%s"', self::REPORTING_ENDPOINT, route('csp.report'))
            );
        }

        return $response;
    }

    /**
     * Build the policy string from the nonce issued for this request.
     */
    public function policy(Request $request): string
    {
        $nonce = sprintf("'nonce-%s'", Vite::cspNonce());

        [$devHttp, $devWs] = $this->devServerOrigins();

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", $nonce, ...$devHttp],
            'style-src' => ["'self'", $nonce, ...$devHttp],
            // data: images are still needed by the AdminLTE stylesheet of the views not yet migrated
            'img-src' => ["'self'", 'data:', ...$devHttp],
            'font-src' => ["'self'", ...$devHttp],
            'connect-src' => ["'self'", ...$devHttp, ...$devWs],
            'frame-ancestors' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'object-src' => ["'none'"],
        ];

        if ($this->reportingEnabled()) {
            // report-uri is the legacy directive every browser understands; browsers that
            // support report-to ignore it.
            $directives['report-uri'] = [route('csp.report')];
            $directives['report-to'] = [self::REPORTING_ENDPOINT];
        }

        // Only meaningful behind TLS; the scheme is known thanks to the trusted proxies.
        if (config('csp.upgrade_insecure_requests') && $request->isSecure()) {
            $directives['upgrade-insecure-requests'] = [];
        }

        return collect($directives)
            ->map(fn (array $values, string $name): string => trim($name.' '.implode(' ', $values)))
            ->implode('; ');
    }

    /**
     * Origins of the Vite dev server, only while public/hot exists.
     *
     * @return array{0: list<string>, 1: list<string>} [http(s) origins, ws(s) origins]
     */
    private function devServerOrigins(): array
    {
        $url = Vite::devServerUrl();

        if ($url === null || $url === '') {
            return [[], []];
        }

        $parts = parse_url($url);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return [[], []];
        }

        $hostPort = $parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
        $httpScheme = $parts['scheme'] === 'https' ? 'https' : 'http';
        $wsScheme = $httpScheme === 'https' ? 'wss' : 'ws';

        return [
            [$httpScheme.'://'.$hostPort],
            [$wsScheme.'://'.$hostPort],
        ];
    }

    private function alreadyHasPolicy(Response $response): bool
    {
        return $response->headers->has('Content-Security-Policy')
            || $response->headers->has('Content-Security-Policy-Report-Only');
    }

    private function reportingEnabled(): bool
    {
        return (bool) config('csp.report', false) && app('router')->has('csp.report');
    }
}
