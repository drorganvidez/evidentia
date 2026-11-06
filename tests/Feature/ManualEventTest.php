<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sessions held before the edition used Eventbrite have no event to import, so the register coordinator
 * creates them by hand and registers their attendances like any other.
 */
class ManualEventTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['attendee_timestamp' => now()->addMonth()]);
        $this->coordinator = User::factory()->create();
        $this->coordinator->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'register_coordinator']));
        $this->student = User::factory()->create();
        $this->actingAs($this->coordinator);
    }

    private function workshop(): array
    {
        return [
            'name' => 'Taller de Git, turno de las 8:30',
            'start_datetime' => '2026-10-28T08:30',
            'end_datetime' => '2026-10-28T10:00',
            'capacity' => 30,
            'description' => 'Primera sesión del taller',
        ];
    }

    public function test_an_event_is_created_by_hand_with_its_length_as_hours(): void
    {
        $this->get(route('registercoordinator.event.create'))->assertOk()->assertSee('Crear evento');

        $this->post(route('registercoordinator.event.store'), $this->workshop())
            ->assertRedirect(route('registercoordinator.event.list'))
            ->assertSessionHas('success');

        $event = Event::sole();
        $this->assertNull($event->id_eventbrite);
        $this->assertSame('Taller de Git, turno de las 8:30', $event->name);
        $this->assertEquals(1.5, $event->hours);
        $this->assertSame('2026-10-28 08:30:00', (string) $event->start_datetime);
        $this->assertSame('completed', $event->status);
    }

    public function test_attendances_of_a_hand_made_event_count_as_hours(): void
    {
        $this->post(route('registercoordinator.event.store'), $this->workshop());
        $event = Event::sole();

        $this->get(route('attendee.create', ['event' => $event->id]))
            ->assertOk()
            ->assertSee('Taller de Git, turno de las 8:30')
            ->assertSee('28/10/2026 08:30');

        $this->post(route('attendee.store'), ['event_id' => $event->id, 'user_id' => $this->student->id, 'status' => 'Checked In'])
            ->assertRedirect(route('registercoordinator.attendee.list'));

        $this->assertSame(1, Attendee::where('event_id', $event->id)->count());
        $this->assertEquals(1.5, $this->student->fresh()->events_hours());
    }

    public function test_the_event_list_shows_hand_made_and_eventbrite_events_side_by_side(): void
    {
        Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2, 'url' => 'https://eventbrite.example/111']);
        $this->post(route('registercoordinator.event.store'), $this->workshop());
        $manual = Event::whereNull('id_eventbrite')->sole();

        $this->get(route('registercoordinator.event.list'))
            ->assertOk()
            ->assertSee('Charla IA')
            ->assertSee('Taller de Git, turno de las 8:30')
            ->assertSee('Creado a mano')
            ->assertSee(route('registercoordinator.attendee.load', ['id' => '111']), false)
            ->assertSee(route('registercoordinator.event.edit', $manual->id), false)
            ->assertSee(route('registercoordinator.event.hide', ['id' => $manual->id]), false);
    }

    public function test_a_hand_made_event_is_edited_and_its_hours_follow(): void
    {
        $this->post(route('registercoordinator.event.store'), $this->workshop());
        $event = Event::sole();

        $this->get(route('registercoordinator.event.edit', $event->id))
            ->assertOk()
            ->assertSee('value="2026-10-28T08:30"', false);

        $this->post(route('registercoordinator.event.update', $event->id), ['end_datetime' => '2026-10-28T11:00'] + $this->workshop())
            ->assertRedirect(route('registercoordinator.event.list'));

        $this->assertEquals(2.5, $event->fresh()->hours);
    }

    public function test_the_end_must_come_after_the_start(): void
    {
        $this->from(route('registercoordinator.event.create'))
            ->post(route('registercoordinator.event.store'), ['end_datetime' => '2026-10-28T08:00'] + $this->workshop())
            ->assertRedirect(route('registercoordinator.event.create'))
            ->assertSessionHasErrors('end_datetime');

        $this->assertSame(0, Event::count());
    }

    public function test_a_hand_made_event_is_deleted_only_without_attendances(): void
    {
        $this->post(route('registercoordinator.event.store'), $this->workshop());
        $event = Event::sole();
        $attendee = Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $this->student->id, 'status' => 'Checked In']);

        $this->delete(route('registercoordinator.event.destroy', $event->id))->assertSessionHas('error');
        $this->assertModelExists($event);

        $attendee->delete();
        $this->delete(route('registercoordinator.event.destroy', $event->id))->assertSessionHas('success');
        $this->assertModelMissing($event);
    }

    public function test_eventbrite_events_are_not_edited_nor_deleted_by_hand(): void
    {
        $event = Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);

        $this->get(route('registercoordinator.event.edit', $event->id))->assertNotFound();
        $this->post(route('registercoordinator.event.update', $event->id), $this->workshop())->assertNotFound();
        $this->delete(route('registercoordinator.event.destroy', $event->id))->assertNotFound();

        $this->assertSame('Charla IA', $event->fresh()->name);
    }

    public function test_only_the_register_coordinator_creates_events_and_only_within_the_deadline(): void
    {
        $this->actingAs($this->student)
            ->post(route('registercoordinator.event.store'), $this->workshop())
            ->assertNotFound();

        Configuration::query()->update(['attendee_timestamp' => now()->subDay()]);

        $this->actingAs($this->coordinator)
            ->post(route('registercoordinator.event.store'), $this->workshop())
            ->assertNotFound();

        $this->assertSame(0, Event::count());
    }
}
