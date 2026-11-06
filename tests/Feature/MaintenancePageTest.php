<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Components\ComponentTestCase;

/** During php artisan down every page answers 503 with the maintenance page of the new layout. */
class MaintenancePageTest extends ComponentTestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $this->app->maintenanceMode()->deactivate();
        parent::tearDown();
    }

    public function test_the_maintenance_page_says_so_and_only_offers_to_try_again(): void
    {
        $this->app->maintenanceMode()->activate(['retry' => 60]);

        foreach (['/', '/login', '/evidence/list'] as $path) {
            $response = $this->get($path);
            $response->assertStatus(503);
            $html = $response->getContent();

            $this->assertMatchesRegularExpression('/<h1[^>]*>\s*En mantenimiento\s*<\/h1>/', $html);
            $this->assertStringContainsString('<title>En mantenimiento · Evidentia</title>', $html);
            $this->assertStringContainsString('Volvemos en unos minutos.', $html);
            $this->assertStringContainsString('Volver a intentarlo', $html);
            $this->assertStringContainsString('href="'.url($path === '/' ? '' : $path).'"', $html);
            $this->assertStringNotContainsString('Volver al inicio', $html);
            $this->assertStringNotContainsString('>503<', $html);
            $this->assertCspSafe($html);
            $this->assertUniqueIds($html);
        }
    }

    public function test_the_other_error_pages_keep_their_code_and_the_way_home(): void
    {
        $html = $this->get('/no-existe')->assertNotFound()->getContent();

        $this->assertStringContainsString('>404<', $html);
        $this->assertStringContainsString('Volver al inicio', $html);
    }
}
