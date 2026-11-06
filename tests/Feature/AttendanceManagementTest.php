<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['attendee_timestamp' => now()->addMonth()]);
        $coordinator = User::factory()->create();
        $coordinator->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'register_coordinator']));
        $this->student = User::factory()->create();
        $this->event = Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);
        $this->actingAs($coordinator);
    }

    public function test_attendances_are_added_changed_and_removed_by_hand(): void
    {
        $this->post(route('attendee.store'), ['event_id' => $this->event->id, 'user_id' => $this->student->id, 'status' => 'Attending'])
            ->assertRedirect(route('registercoordinator.attendee.list'));
        $attendee = Attendee::sole();
        $this->assertEquals(0, $this->student->fresh()->events_hours());

        $this->post(route('attendee.update', ['id' => $attendee->id]), ['event_id' => $this->event->id, 'user_id' => $this->student->id, 'status' => 'Checked In'])
            ->assertRedirect(route('registercoordinator.attendee.list'));
        $this->assertEquals(2, $this->student->fresh()->events_hours());

        $this->delete(route('attendee.destroy', ['id' => $attendee->id]))->assertRedirect(route('registercoordinator.attendee.list'));
        $this->assertModelMissing($attendee);
    }

    public function test_a_student_has_one_attendance_per_event(): void
    {
        Attendee::forceCreate(['event_id' => $this->event->id, 'user_id' => $this->student->id, 'status' => 'Checked In']);

        $this->post(route('attendee.store'), ['event_id' => $this->event->id, 'user_id' => $this->student->id, 'status' => 'Checked In'])
            ->assertSessionHas('error');

        $this->assertSame(1, Attendee::count());
    }

    public function test_events_are_hidden_and_shown_again(): void
    {
        $this->from(route('registercoordinator.event.list'))->post(route('registercoordinator.event.hide', ['id' => $this->event->id]));
        $this->assertTrue((bool) $this->event->fresh()->hidden);

        $this->from(route('registercoordinator.event.list'))->post(route('registercoordinator.event.unhide', ['id' => $this->event->id]));
        $this->assertFalse((bool) $this->event->fresh()->hidden);
    }
}
