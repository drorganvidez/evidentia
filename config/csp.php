<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content-Security-Policy
    |--------------------------------------------------------------------------
    |
    | App\Http\Middleware\ContentSecurityPolicy builds the policy. These
    | switches only decide whether it is sent, in which mode and whether the
    | browser is asked to report violations.
    |
    */

    // Switch it off only to debug, never in production.
    'enabled' => (bool) env('CSP_ENABLED', true),

    // 'enforce' blocks; 'report-only' just reports. The views that still use the
    // AdminLTE layout need report-only; routes rendered with the new layout opt
    // into 'enforce' with the 'csp:enforce' middleware alias.
    'mode' => env('CSP_MODE', 'report-only'),

    // Adds report-uri, report-to and Reporting-Endpoints pointing at POST /csp-report
    // and writes every violation to the log.
    'report' => (bool) env('CSP_REPORT', false),

    // Log channel for the violations (null = default channel).
    'log_channel' => env('CSP_LOG_CHANNEL'),

    // upgrade-insecure-requests only makes sense behind TLS, so production only.
    'upgrade_insecure_requests' => (bool) env('CSP_UPGRADE_INSECURE_REQUESTS', env('APP_ENV') === 'production'),

];
