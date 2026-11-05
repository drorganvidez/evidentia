<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * EventbriteController creates its own GuzzleHttp\Client, so the class is replaced with a Mockery
 * "overload" mock in a separate process. Eventbrite accepts only the token "valid".
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class EventbriteTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        Mockery::mock('overload:GuzzleHttp\Client')
            ->shouldReceive('request')
            ->andReturnUsing(function ($method, $uri, $options) {
                if ($options['query']['token'] === 'valid') {
                    return new Response(200, [], '{"id":"1"}');
                }
                throw new ClientException('401 Unauthorized', new Request($method, $uri), new Response(401));
            });

        parent::setUp();
        Configuration::forceCreate(['attendee_timestamp' => now()->addMonth()]);
        $coordinator = User::factory()->create();
        $coordinator->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'register_coordinator']));
        $this->actingAs($coordinator);
    }

    public function test_a_valid_token_is_saved(): void
    {
        $this->post(route('registercoordinator.token.save'), ['token' => 'valid'])
            ->assertRedirect(route('registercoordinator.token'))
            ->assertSessionHas('success');

        $this->assertSame('valid', Configuration::find(1)->eventbrite_token);
    }

    public function test_an_invalid_token_is_not_saved_and_says_so(): void
    {
        $this->from(route('registercoordinator.token'))
            ->post(route('registercoordinator.token.save'), ['token' => 'revoked'])
            ->assertRedirect(route('registercoordinator.token'))
            ->assertSessionHas('error');

        $this->assertNull(Configuration::find(1)->eventbrite_token);
    }
}
