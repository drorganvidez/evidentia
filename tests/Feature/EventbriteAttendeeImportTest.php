<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tests\TestCase;

/**
 * EventbriteController creates its own GuzzleHttp\Client, so the class is replaced with a Mockery
 * "overload" mock in a separate process. No request leaves the machine.
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class EventbriteAttendeeImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        Mockery::mock('overload:GuzzleHttp\Client')
            ->shouldReceive('request')
            ->andReturnUsing(fn ($method, $uri) => match ($uri) {
                'users/me/' => new Response(200, [], '{"id":"1"}'),
                'events/111/attendees' => new Response(200, [], json_encode([
                    'attendees' => [
                        $this->attendee('Ana', 'García', 'ana.garcia@alum.us.es'),
                        $this->attendee('Pedro', 'López', 'pedro.personal@gmail.com'),
                        $this->attendee('José', 'Pérez', 'jose.personal@gmail.com'),
                    ],
                    'pagination' => ['has_more_items' => false],
                ])),
                'events/222/attendees' => new Response(200, [], json_encode([
                    'attendees' => [
                        $this->attendee('Ana', 'García', 'ana.garcia@alum.us.es'),
                        $this->attendee('Pedro', 'López', 'pedro.personal@gmail.com'),
                    ],
                    'pagination' => ['has_more_items' => false],
                ])),
            });

        parent::setUp();
        Configuration::forceCreate(['attendee_timestamp' => now()->addMonth(), 'eventbrite_token' => 'token']);
    }

    public function test_attendances_go_to_the_right_students(): void
    {
        $juana = $this->student('Juana', 'GARCIA', 'juana@alum.us.es');
        $ana = $this->student('Ana', 'GARCIA', 'ana.garcia@alum.us.es');
        $pedro = $this->student('Pedro', 'LOPEZ', 'pedro@alum.us.es');
        $this->student('Pedro', 'LOPEZMARTIN', 'pedro.lopez.martin@alum.us.es');
        $this->student('José', 'PEREZ', 'jose1@alum.us.es');
        $this->student('José', 'PEREZ', 'jose2@alum.us.es');
        $event = Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);
        $coordinator = User::factory()->create();
        $coordinator->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'register_coordinator']));

        $this->actingAs($coordinator)
            ->get(route('registercoordinator.attendee.load', ['id' => '111']))
            ->assertRedirect(route('registercoordinator.attendee.list'));

        $this->assertEqualsCanonicalizing(
            [$ana->id, $pedro->id],
            Attendee::where('event_id', $event->id)->pluck('user_id')->all()
        );
        $this->assertSame(0, $juana->fresh()->attendees()->count());
    }

    public function test_loads_only_the_attendances_of_one_student_across_events(): void
    {
        $ana = $this->student('Ana', 'GARCIA', 'ana.garcia@alum.us.es');
        $pedro = $this->student('Pedro', 'LOPEZ', 'pedro@alum.us.es');
        $first = Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);
        $second = Event::forceCreate(['name' => 'Taller Git', 'id_eventbrite' => '222', 'hours' => 1, 'hidden' => true]);
        Event::forceCreate(['name' => 'Sesión a mano', 'hours' => 1]);
        $coordinator = $this->coordinator();

        $this->actingAs($coordinator)->get(route('registercoordinator.attendee.load.student'))->assertOk()->assertSee('Cargar asistencias');

        $this->actingAs($coordinator)
            ->post(route('registercoordinator.attendee.load.student.save'), ['user_id' => $ana->id])
            ->assertRedirect(route('registercoordinator.attendee.list'))
            ->assertSessionHas('success');

        $this->assertEqualsCanonicalizing([$first->id, $second->id], $ana->fresh()->attendees()->pluck('event_id')->all());
        $this->assertSame(0, $pedro->fresh()->attendees()->count());
    }

    public function test_a_coordinator_cannot_load_their_own_attendances(): void
    {
        Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);
        $coordinator = $this->coordinator();

        $this->actingAs($coordinator)
            ->post(route('registercoordinator.attendee.load.student.save'), ['user_id' => $coordinator->id])
            ->assertSessionHas('error');

        $this->assertSame(0, Attendee::count());
    }

    private function coordinator(): User
    {
        $coordinator = User::factory()->create();
        $coordinator->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'register_coordinator']));

        return $coordinator;
    }

    private function student(string $name, string $cleanSurname, string $email): User
    {
        return User::factory()->create([
            'name' => $name,
            'clean_name' => \StringUtilites::clean($name),
            'clean_surname' => $cleanSurname,
            'email' => $email,
        ]);
    }

    private function attendee(string $firstName, string $lastName, string $email): array
    {
        return [
            'profile' => ['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email],
            'status' => 'Checked In',
        ];
    }
}
