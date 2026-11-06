<?php

namespace Tests\Feature\Screens\Management;

use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Components\ComponentTestCase;

/**
 * Base of the render tests of the management, review and register screens: the seed of the end-to-end tests, a
 * person of it signed in, and the checks every screen of the new layout must pass.
 */
abstract class ScreenTestCase extends ComponentTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
    }

    protected function person(string $who): User
    {
        return User::where('username', $who)->sole();
    }

    /** GET the screen as a person of the seed and check that it renders on the new layout under the policy. */
    protected function screen(string $who, string $url): TestResponse
    {
        $response = $this->actingAs($this->person($who))->get($url);

        $response->assertOk();
        $html = $response->getContent();
        $this->assertStringContainsString('id="main"', $html, 'New layout');
        $this->assertStringNotContainsString('content-wrapper', $html, 'Old layout');
        $this->assertSame(1, substr_count($html, '<h1'), 'One h1');
        $this->assertCspSafe($html);
        $this->assertUniqueIds($html);

        return $response;
    }
}
