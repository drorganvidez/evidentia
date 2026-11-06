<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * /_ui shows the tokens and components of the new UI to developers. It is the first page rendered with
 * the new layout, so it runs under the enforced CSP and its inline theme script must carry the nonce.
 */
class DesignSystemPageTest extends TestCase
{
    public function test_it_opens_in_local_and_testing_under_the_enforced_csp(): void
    {
        $response = $this->get('/_ui')->assertOk();

        $response->assertHeader('Content-Security-Policy');
        $response->assertHeaderMissing('Content-Security-Policy-Report-Only');

        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([A-Za-z0-9]{40})'/", $csp);
        preg_match("/'nonce-([A-Za-z0-9]{40})'/", $csp, $m);

        $response->assertSee('<meta property="csp-nonce" nonce="'.$m[1].'">', false);
        $response->assertSee('<script nonce="'.$m[1].'">', false);
        $response->assertSee("var KEY = 'evidentia.theme';", false);
        $response->assertSee('<title>Sistema de diseño', false);
        $response->assertSee('x-data="theme"', false);
    }

    public function test_it_is_not_published_outside_local_and_testing(): void
    {
        $this->app['env'] = 'production';

        $this->get('/_ui')->assertNotFound();
    }
}
