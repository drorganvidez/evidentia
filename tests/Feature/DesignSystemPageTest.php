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
        $response->assertSee("\$store.theme.select('dark')", false);
    }

    public function test_it_lists_every_existing_section_in_the_table_of_contents(): void
    {
        $response = $this->get('/_ui')->assertOk();

        $response->assertSee('aria-label="Contenido"', false);

        foreach (['tokens', 'icons'] as $section) {
            $response->assertSee('href="#ui-'.$section.'"', false);
            $response->assertSee('id="ui-'.$section.'"', false);
        }
    }

    public function test_unknown_pages_are_not_found(): void
    {
        $this->get('/_ui/no-such-page')->assertNotFound();
    }

    public function test_the_demo_endpoints_serve_the_interactive_components(): void
    {
        $people = $this->getJson('/_ui/demo/people')->assertOk()->json();

        $this->assertCount(300, $people);
        $this->assertSame(['value', 'label', 'hint'], array_keys($people[0]));
        $this->assertSame($people, $this->getJson('/_ui/demo/people')->json(), 'The people must be deterministic');

        $this->getJson('/_ui/demo/list/3')->assertOk()->assertJson([3, 6, 9]);

        $id = $this->post('/_ui/demo/upload')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->getContent();
        $this->assertStringStartsWith('demo-', $id);

        $this->delete('/_ui/demo/upload', ['id' => $id])->assertNoContent();
        $this->get('/_ui/demo/remove/informe.pdf')->assertNoContent();
    }

    public function test_nothing_is_published_outside_local_and_testing(): void
    {
        $this->app['env'] = 'production';

        $this->get('/_ui')->assertNotFound();
        $this->getJson('/_ui/demo/people')->assertNotFound();
        $this->withSession(['_token' => 'token'])->post('/_ui/demo/upload', ['_token' => 'token'])->assertNotFound();
    }
}
