<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The app always runs behind nginx and the university's reverse proxy; the client IP, the scheme and
 * the host must come from the X-Forwarded-* headers those proxies set.
 */
class TrustedProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_proxy/echo', fn () => response()->json([
            'ip' => request()->ip(),
            'secure' => request()->isSecure(),
            'url' => url('/evidence'),
        ]));
    }

    public function test_the_forwarded_client_ip_and_scheme_are_used(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '172.18.0.5'])
            ->withHeaders([
                'X-Forwarded-For' => '150.214.1.2',
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'evidentia.us.es',
                'X-Forwarded-Port' => '443',
            ])
            ->getJson('/_proxy/echo')
            ->assertOk()
            ->assertJson([
                'ip' => '150.214.1.2',
                'secure' => true,
                'url' => 'https://evidentia.us.es/evidence',
            ]);
    }

    public function test_a_direct_request_keeps_its_own_address_and_scheme(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.9'])
            ->getJson('/_proxy/echo')
            ->assertOk()
            ->assertJson(['ip' => '10.0.0.9', 'secure' => false]);
    }
}
