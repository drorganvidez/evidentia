<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * EventbriteController creates its own GuzzleHttp\Client, so the class is replaced with a Mockery "overload" mock in a
 * separate process. No request leaves the machine and the token never reaches the log.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class EventbriteSyncActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    protected function setUp(): void
    {
        Mockery::mock('overload:GuzzleHttp\Client')
            ->shouldReceive('request')
            ->andReturnUsing(fn ($method, $uri) => match ($uri) {
                'users/me/' => new Response(200, [], '{"id":"1"}'),
                'users/me/organizations' => new Response(200, [], json_encode(['organizations' => [['id' => '9']]])),
                'organizations/9/events' => new Response(200, [], json_encode([
                    'events' => [$this->event('111', 'Charla IA'), $this->event('222', 'Taller de Git')],
                    'pagination' => ['has_more_items' => false],
                ])),
                'events/111/attendees' => new Response(200, [], json_encode([
                    'attendees' => [
                        $this->attendee('Ana', 'García', 'ana@alum.us.es', 'Checked In'),
                        $this->attendee('Pedro', 'López', 'pedro@alum.us.es', 'Checked In'),
                        $this->attendee('Carla', 'Ruiz', 'carla@alum.us.es', 'Attending'),
                        $this->attendee('Nadie', 'Conocido', 'nadie@gmail.com', 'Attending'),
                    ],
                    'pagination' => ['has_more_items' => false],
                ])),
            });

        parent::setUp();
        Configuration::forceCreate(['attendee_timestamp' => now()->addMonth(), 'eventbrite_token' => 'secret-token-123']);
        $this->coordinator = User::factory()->create(['email' => 'carla@alum.us.es']);
        $this->coordinator->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'register_coordinator']));
        $this->actingAs($this->coordinator);
    }

    public function test_saving_the_token_is_recorded_without_the_token(): void
    {
        $this->post(route('registercoordinator.token.save'), ['token' => 'new-secret-token'])
            ->assertRedirect(route('registercoordinator.token'));

        $log = ActivityLog::sole();
        $this->assertSame('eventbrite.token_saved', $log->action);
        $this->assertSame('REGISTER_COORDINATOR', $log->acting_role);
        $this->assertSame('configuration', $log->subject_type);
        $this->assertSame('Token de Eventbrite', $log->subject_title);
        $this->assertNull($log->details);
        $this->assertStringNotContainsString('new-secret-token', json_encode($log->getAttributes()));
    }

    public function test_loading_the_events_records_how_many_are_new_and_how_many_changed(): void
    {
        Event::forceCreate(['name' => 'Charla', 'id_eventbrite' => '111', 'hours' => 1]);

        $this->get(route('registercoordinator.event.load'))->assertRedirect(route('registercoordinator.event.list'));

        $log = ActivityLog::sole();
        $this->assertSame('eventbrite.events_synced', $log->action);
        $this->assertSame('REGISTER_COORDINATOR', $log->acting_role);
        $this->assertSame(['created' => 1, 'updated' => 1], $log->details);
        $this->assertStringNotContainsString('secret-token-123', json_encode($log->getAttributes()));
    }

    public function test_loading_the_attendances_records_the_matched_people_and_the_coordinator_among_them(): void
    {
        $event = Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);
        $ana = User::factory()->create(['email' => 'ana@alum.us.es']);
        $pedro = User::factory()->create(['email' => 'pedro@alum.us.es']);
        Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $pedro->id, 'status' => 'Attending']);

        $this->get(route('registercoordinator.attendee.load', ['id' => '111']))->assertRedirect(route('registercoordinator.attendee.list'));

        $log = ActivityLog::sole();
        $this->assertSame('eventbrite.attendees_synced', $log->action);
        $this->assertSame('event', $log->subject_type);
        $this->assertSame($event->id, $log->subject_id);
        $this->assertSame(['created' => 2, 'updated' => 1, 'unmatched' => 1, 'self_included' => true], $log->details);
        $this->assertEqualsCanonicalizing(
            [$ana->id, $pedro->id, $this->coordinator->id],
            DB::table('activity_log_targets')->where('activity_log_id', $log->id)->pluck('user_id')->map(fn ($id) => (int) $id)->all(),
        );
        $this->assertStringNotContainsString('secret-token-123', json_encode($log->getAttributes()));
    }

    private function event(string $id, string $name): array
    {
        return [
            'id' => $id, 'name' => ['html' => $name], 'description' => ['html' => '<p>'.$name.'</p>'],
            'start' => ['local' => '2026-10-16T16:00:00'], 'end' => ['local' => '2026-10-16T18:00:00'],
            'capacity' => 50, 'status' => 'completed', 'url' => 'https://www.eventbrite.es/e/'.$id,
        ];
    }

    private function attendee(string $firstName, string $lastName, string $email, string $status): array
    {
        return ['profile' => ['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email], 'status' => $status];
    }
}
