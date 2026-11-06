<?php

namespace Tests\Feature\Screens\Meetings;

use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Components\ComponentTestCase;

/**
 * Base of the render tests of the meeting, bonus and list screens: the end-to-end seed, a person per role and the checks every
 * screen of the new layout must pass (CSP-safe markup, unique ids, the app shell instead of AdminLTE).
 */
abstract class MeetingScreenTestCase extends ComponentTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        $this->seed(E2eSeeder::class);
    }

    /** Signs in the seeded person of a role: estudiante, secretario, coordinador... */
    protected function as(string $who): static
    {
        return $this->actingAs(User::where('email', "$who@e2e.test")->sole());
    }

    /** The response is a page of the new layout that the strict CSP accepts, and returns its HTML. */
    protected function assertScreen(TestResponse $response): string
    {
        $html = $response->assertOk()->getContent();

        $this->assertStringContainsString('<main id="main"', $html, 'The screen does not use x-layout.app');
        $this->assertStringNotContainsString('class="wrapper"', $html, 'The screen still renders the AdminLTE layout');
        $this->assertSame(1, substr_count($html, '<h1'), 'A screen has exactly one h1');
        $this->assertCspSafe($html);
        $this->assertUniqueIds($html);

        return $html;
    }
}
