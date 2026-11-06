<?php

namespace Tests\Feature\Screens\PublicAndStudent;

use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Components\ComponentTestCase;

/**
 * Base of the render tests of the screens moved to the new UI: the seed of the end-to-end tests (one person per
 * role), signing in as one of them and the checks every screen honours.
 */
abstract class ScreenTestCase extends ComponentTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(E2eSeeder::class);
    }

    /** estudiante, companero, coordinador, secretario, registro, presidente or profesor. */
    protected function person(string $who): User
    {
        return User::where('username', $who)->sole();
    }

    protected function signedAs(string $who): static
    {
        return $this->actingAs($this->person($who));
    }

    /** A page of the app shell, safe under the policy, without repeated ids and with one h1. */
    protected function assertAppScreen(TestResponse $response): string
    {
        return $this->assertScreen($response, 'aria-label="Barra lateral"');
    }

    /** A public page with its skip link to the form, safe under the policy and without repeated ids. */
    protected function assertPublicScreen(TestResponse $response): string
    {
        return $this->assertScreen($response, 'Saltar al formulario');
    }

    private function assertScreen(TestResponse $response, string $marker): string
    {
        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString($marker, $html, 'The screen uses the new layout');
        $this->assertStringNotContainsString('adminlte', $html, 'Nothing of the old layout');
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html), 'One h1');
        $this->assertCspSafe($html);
        $this->assertUniqueIds($html);

        return $html;
    }
}
