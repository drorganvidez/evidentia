<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Client for the Single Sign-On (SSO) of the University of Seville (CAS 2.0 / 3.0 and SAML attribute release),
 * with a local development mock mode to simulate returning an authenticated UVUS without reaching the private IdP.
 */
class UsSsoService
{
    /**
     * Whether the University of Seville SSO integration is configured and available on the login screen.
     */
    public function isEnabled(): bool
    {
        if ($this->isMockEnabled()) {
            return true;
        }

        return (bool) config('services.us_sso.enabled', false)
            && filled(config('services.us_sso.base_url'));
    }

    /**
     * Whether the local mock SSO screen is enabled (never allowed in production).
     */
    public function isMockEnabled(): bool
    {
        if (app()->environment('production')) {
            return false;
        }

        return (bool) config('services.us_sso.mock_enabled', false);
    }

    /**
     * Builds the external CAS login URL with the callback service parameter.
     */
    public function loginUrl(string $serviceUrl): string
    {
        $baseUrl = rtrim((string) config('services.us_sso.base_url'), '/');
        $loginPath = '/'.ltrim((string) config('services.us_sso.login_path', '/login'), '/');

        return $baseUrl.$loginPath.'?'.http_build_query(['service' => $serviceUrl]);
    }

    /**
     * Validates a CAS service ticket against the University of Seville IdP and returns the normalized UVUS,
     * or null when the ticket is rejected or the IdP cannot be reached.
     */
    public function validateTicket(string $ticket, string $serviceUrl): ?string
    {
        $ticket = trim($ticket);
        if ($ticket === '' || ! filled(config('services.us_sso.base_url'))) {
            return null;
        }

        $baseUrl = rtrim((string) config('services.us_sso.base_url'), '/');
        $validatePath = '/'.ltrim((string) config('services.us_sso.validate_path', '/p3/serviceValidate'), '/');

        try {
            $response = Http::accept('application/xml, text/xml, application/json')
                ->timeout(10)
                ->get($baseUrl.$validatePath, [
                    'ticket' => $ticket,
                    'service' => $serviceUrl,
                ]);

            if (! $response->successful()) {
                return null;
            }

            return $this->extractUvusFromResponse((string) $response->body());
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Normalizes a UVUS identifier for database matching.
     */
    public function normalizeUvus(?string $uvus): ?string
    {
        $normalized = mb_strtolower(trim((string) $uvus));

        return $normalized === '' ? null : $normalized;
    }

    /**
     * Extracts the UVUS from a CAS 2.0/3.0 XML or JSON validation payload.
     */
    private function extractUvusFromResponse(string $body): ?string
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, '{')) {
            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                $user = data_get($decoded, 'serviceResponse.authenticationSuccess.attributes.uid.0')
                    ?? data_get($decoded, 'serviceResponse.authenticationSuccess.attributes.uid')
                    ?? data_get($decoded, 'serviceResponse.authenticationSuccess.user');

                return is_scalar($user) ? $this->normalizeUvus((string) $user) : null;
            }
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($trimmed);
            if ($xml === false) {
                return null;
            }

            $namespaces = $xml->getNamespaces(true);
            if (isset($namespaces['cas'])) {
                $cas = $xml->children($namespaces['cas']);
                if (isset($cas->authenticationSuccess)) {
                    $success = $cas->authenticationSuccess->children($namespaces['cas']);
                    $uid = (string) ($success->attributes->uid ?? '');
                    if ($uid !== '') {
                        return $this->normalizeUvus($uid);
                    }

                    return $this->normalizeUvus((string) ($success->user ?? ''));
                }

                return null;
            }

            if (isset($xml->authenticationSuccess)) {
                $uid = (string) ($xml->authenticationSuccess->attributes->uid ?? '');
                if ($uid !== '') {
                    return $this->normalizeUvus($uid);
                }

                return $this->normalizeUvus((string) ($xml->authenticationSuccess->user ?? ''));
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return null;
    }
}

