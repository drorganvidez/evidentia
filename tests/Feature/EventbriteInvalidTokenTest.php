<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * `new GuzzleHttp\Client` se sustituye por un mock "overload" de Mockery (proceso aislado):
 * no sale ninguna petición. Simula lo que hace Guzzle cuando Eventbrite responde 401.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class EventbriteInvalidTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $guzzle = Mockery::mock('overload:GuzzleHttp\Client');
        $guzzle->shouldReceive('request')->andReturnUsing(function ($method, $uri) {
            // Guzzle lanza ClientException en cualquier 4xx (http_errors = true por defecto)
            throw new ClientException('401 Unauthorized: INVALID_AUTH', new Request($method, $uri),
                new Response(401, [], '{"error":"INVALID_AUTH"}'));
        });

        parent::setUp();
        Storage::fake();
        Mail::fake();
        Http::preventStrayRequests();
        // Token caducado/revocado en Eventbrite
        DB::table('configuration')->insert(['id' => 1, 'attendee_timestamp' => now()->addMonth(), 'eventbrite_token' => 'revoked']);
    }

    public function test_loading_attendees_with_a_revoked_token_shows_the_token_error_instead_of_a_500(): void
    {
        $rc = User::factory()->create();
        $rc->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'Coordinador de registro']));
        Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);

        $this->actingAs($rc)
            ->from(route('registercoordinator.event.list'))
            ->get(route('registercoordinator.attendee.load', ['id' => '111']))
            ->assertRedirect(route('registercoordinator.event.list'))
            ->assertSessionHas('error');
    }
}
