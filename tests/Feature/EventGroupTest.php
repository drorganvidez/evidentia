<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventGroupTest extends TestCase
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
    }

    public function test_of_each_group_only_the_longest_attended_event_counts(): void
    {
        $morning = $this->attended('Charla IA mañana', 2, 'Charla IA');
        $this->attended('Charla IA tarde', 2, 'Charla IA');
        $this->attended('Taller A', 3, 'Talleres paralelos');
        $this->attended('Taller B', 1.5, 'Talleres paralelos');
        $this->attended('Clausura', 1);

        $this->assertSame(6.0, (float) $this->student->fresh()->attendees_hours());

        $morning->update(['event_group' => null]);
        $this->assertSame(8.0, (float) $this->student->fresh()->attendees_hours());
    }

    public function test_the_coordinator_groups_events_and_a_group_left_alone_is_undone(): void
    {
        [$a, $b, $c] = [Event::forceCreate(['name' => 'A', 'hours' => 1]), Event::forceCreate(['name' => 'B', 'hours' => 2]), Event::forceCreate(['name' => 'C', 'hours' => 1])];
        $this->actingAs($this->coordinator);

        $this->post(route('registercoordinator.event.group.save'), ['event_group' => 'Primero', 'events' => [$a->id, $b->id]])
            ->assertRedirect(route('registercoordinator.event.groups'))->assertSessionHas('success');
        $this->post(route('registercoordinator.event.group.save'), ['event_group' => ' Segundo ', 'events' => [$b->id, $c->id]]);

        $this->assertNull($a->fresh()->event_group);
        $this->assertSame('Segundo', $b->fresh()->event_group);
        $this->assertSame('Segundo', $c->fresh()->event_group);
        $log = ActivityLog::where('action', 'event.grouped')->latest('id')->first();
        $this->assertSame(['group' => 'Segundo', 'events' => ['B', 'C']], array_intersect_key($log->details, array_flip(['group', 'events'])));

        $this->get(route('registercoordinator.event.groups'))->assertOk()->assertSee('Segundo')->assertDontSee('Primero');
        $this->get(route('registercoordinator.event.list'))->assertOk()->assertSee('Grupo Segundo');
    }

    public function test_a_group_needs_two_events_and_can_be_undone(): void
    {
        $a = Event::forceCreate(['name' => 'A', 'hours' => 1, 'event_group' => 'Doble']);
        $b = Event::forceCreate(['name' => 'B', 'hours' => 1, 'event_group' => 'Doble']);
        $this->actingAs($this->coordinator);

        $this->post(route('registercoordinator.event.group.save'), ['event_group' => 'Solo', 'events' => [$a->id]])
            ->assertSessionHasErrors(['events' => 'Elige al menos dos eventos.']);

        $this->post(route('registercoordinator.event.group.remove'), ['event_group' => 'Doble'])->assertSessionHas('success');
        $this->assertSame(0, Event::whereNotNull('event_group')->count());
        $this->assertSame(1, ActivityLog::where('action', 'event.ungrouped')->count());

        $this->post(route('registercoordinator.event.group.remove'), ['event_group' => 'Doble'])->assertSessionHas('error', 'Ese grupo ya no existe.');
    }

    public function test_only_the_register_coordinator_groups_events(): void
    {
        $this->actingAs($this->student)->get(route('registercoordinator.event.groups'))->assertNotFound();
    }

    private function attended(string $name, float $hours, ?string $group = null): Event
    {
        $event = Event::forceCreate(['name' => $name, 'hours' => $hours, 'event_group' => $group]);
        Attendee::create(['event_id' => $event->id, 'user_id' => $this->student->id, 'status' => 'Checked In']);

        return $event;
    }
}
