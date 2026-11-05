<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Receives the CSP violation reports sent by the browser and writes them to the log.
 *
 * Two formats arrive here:
 *  - report-uri (legacy): Content-Type application/csp-report, body {"csp-report": {...}}
 *  - report-to (Reporting API): Content-Type application/reports+json, body [{"type": "csp-violation", "body": {...}}, ...]
 *
 * The legacy content type is not recognised by Request::isJson(), so both are decoded by hand.
 */
final class CspReportController extends Controller
{
    private const MAX_BODY_BYTES = 65536;

    public function __invoke(Request $request): Response
    {
        $raw = $request->getContent();

        if (strlen($raw) > self::MAX_BODY_BYTES) {
            return response()->noContent(Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        $payload = json_decode($raw, true);

        if (! is_array($payload)) {
            return response()->noContent(Response::HTTP_BAD_REQUEST);
        }

        $logger = Log::channel(config('csp.log_channel'));

        foreach ($this->normalize($payload) as $violation) {
            $logger->notice('CSP violation', $violation + [
                'ip' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 200),
            ]);
        }

        return response()->noContent();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalize(array $payload): array
    {
        if (isset($payload['csp-report']) && is_array($payload['csp-report'])) {
            $r = $payload['csp-report'];

            return [$this->clean([
                'format' => 'report-uri',
                'document' => $r['document-uri'] ?? null,
                'directive' => $r['effective-directive'] ?? $r['violated-directive'] ?? null,
                'blocked' => $r['blocked-uri'] ?? null,
                'source' => $r['source-file'] ?? null,
                'line' => $r['line-number'] ?? null,
                'column' => $r['column-number'] ?? null,
                'sample' => $r['script-sample'] ?? null,
                'disposition' => $r['disposition'] ?? null,
            ])];
        }

        $reports = array_is_list($payload) ? $payload : [$payload];

        $out = [];

        foreach ($reports as $report) {
            if (! is_array($report) || ($report['type'] ?? null) !== 'csp-violation' || ! is_array($report['body'] ?? null)) {
                continue;
            }

            $b = $report['body'];

            $out[] = $this->clean([
                'format' => 'report-to',
                'document' => $b['documentURL'] ?? null,
                'directive' => $b['effectiveDirective'] ?? null,
                'blocked' => $b['blockedURL'] ?? null,
                'source' => $b['sourceFile'] ?? null,
                'line' => $b['lineNumber'] ?? null,
                'column' => $b['columnNumber'] ?? null,
                'sample' => $b['sample'] ?? null,
                'disposition' => $b['disposition'] ?? null,
            ]);
        }

        return $out;
    }

    /**
     * Trims strings so a hostile report cannot bloat the log.
     *
     * @param  array<string, mixed>  $violation
     * @return array<string, mixed>
     */
    private function clean(array $violation): array
    {
        return array_map(
            fn (mixed $value): mixed => is_string($value) ? Str::limit($value, 300) : (is_scalar($value) ? $value : null),
            $violation
        );
    }
}
