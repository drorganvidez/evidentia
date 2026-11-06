<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * /_ui shows the tokens and components of the new UI to developers. It is rendered with the real app layout
 * under the enforced CSP, so it is also the first consumer of the shell; its pages compose whole screens.
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

    public function test_it_is_rendered_with_the_app_layout(): void
    {
        $html = $this->get('/_ui')->assertOk()->getContent();

        $this->assertStringContainsString('<a href="#main"', $html, 'Skip link');
        $this->assertStringContainsString('aria-label="Barra lateral"', $html);
        $this->assertMatchesRegularExpression('/<main id="main" tabindex="-1"\s+aria-labelledby="page-title"/', $html);
        $this->assertStringContainsString('id="page-title"', $html);
        $this->assertStringContainsString('aria-label="Navegación principal (móvil)"', $html, 'Bottom bar');
        $this->assertStringContainsString('id="drawer"', $html);
        $this->assertStringContainsString('type="radio" name="theme-topbar"', $html);
        $this->assertStringContainsString('id="field-q-topbar"', $html);
        $this->assertStringContainsString('aria-label="Enlaces del pie"', $html);
        $this->assertSame(1, substr_count($html, '<main '), 'One main');
        $this->assertSame(1, substr_count($html, 'x-data="notices"'), 'One stack of notices');
        $this->assertUniqueIds($html);
    }

    public function test_the_table_of_contents_links_every_section_file_and_nothing_else(): void
    {
        $html = $this->get('/_ui')->assertOk()->getContent();

        $this->assertSame(1, preg_match('/<nav aria-label="Contenido".*?<\/nav>/s', $html, $nav));
        preg_match_all('/href="#ui-([a-z-]+)"/', $nav[0], $m);
        $linked = $m[1];
        sort($linked);

        $files = collect(File::files(resource_path('views/ui/sections')))
            ->map(fn ($file): string => Str::before($file->getFilename(), '.blade.php'))
            ->sort()
            ->values()
            ->all();

        $this->assertSame($files, $linked, 'The closed list and the section files must agree');
        $this->assertCount(49, $files);

        foreach ($linked as $section) {
            $this->assertStringContainsString('id="ui-'.$section.'"', $html);
            $this->assertStringContainsString('id="ui-'.$section.'-title"', $html);
        }
    }

    public function test_the_pages_render_whole_screens_under_the_enforced_csp(): void
    {
        $pages = [
            'shell' => '<title>Inicio · ',
            'public' => '<title>Acceso · ',
            'detail' => '<title>Montaje del photocall · ',
        ];

        foreach ($pages as $page => $title) {
            $response = $this->get('/_ui/'.$page)->assertOk()->assertHeader('Content-Security-Policy');

            $response->assertSee($title, false);
            $this->assertUniqueIds($response->getContent());
            $this->assertSame(1, substr_count($response->getContent(), '<main '), 'One main in '.$page);
        }

        $this->get('/_ui/pdf')->assertOk()->assertSee('<html lang="es"', false);
    }

    public function test_the_public_page_uses_the_public_layout(): void
    {
        $html = $this->get('/_ui/public')->assertOk()->getContent();

        $this->assertStringContainsString('>Saltar al formulario</a>', $html);
        $this->assertSame(3, substr_count($html, 'type="radio" name="theme-public"'));
        $this->assertStringNotContainsString('aria-label="Barra lateral"', $html, 'No sidebar on a public page');
        $this->assertStringNotContainsString('id="drawer"', $html);
        $this->assertSame(1, substr_count($html, '<h1'), 'The claim is the only h1');
        $this->assertStringContainsString('x-data="countUp"', $html);
        $this->assertStringContainsString('x-data="passwordField"', $html);
        $this->assertStringContainsString('x-data="busyForm"', $html);
        $this->assertStringContainsString('<h2', $html);
    }

    public function test_unknown_pages_are_not_found(): void
    {
        $this->get('/_ui/no-such-page')->assertNotFound();
        $this->get('/_ui/Shell')->assertNotFound();
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
        $this->get('/_ui/shell')->assertNotFound();
        $this->get('/_ui/public')->assertNotFound();
        $this->get('/_ui/detail')->assertNotFound();
        $this->get('/_ui/pdf')->assertNotFound();
        $this->getJson('/_ui/demo/people')->assertNotFound();
        $this->withSession(['_token' => 'token'])->post('/_ui/demo/upload', ['_token' => 'token'])->assertNotFound();
    }

    private function assertUniqueIds(string $html): void
    {
        preg_match_all('/\sid="([^"]+)"/', $html, $matches);

        $repeated = array_keys(array_filter(array_count_values($matches[1]), fn (int $count): bool => $count > 1));

        $this->assertSame([], $repeated, 'Repeated ids');
    }
}
