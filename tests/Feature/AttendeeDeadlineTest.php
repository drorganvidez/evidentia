<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendeeDeadlineTest extends TestCase
{
    use RefreshDatabase;

    private User $registerCoordinator;

    private User $student;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Mail::fake();

        // El plazo de "Importación de eventos y asistencias" cerró ayer
        DB::table('configuration')->insert(['id' => 1, 'attendee_timestamp' => now()->subDay()]);

        $this->registerCoordinator = User::factory()->create();
        $this->registerCoordinator->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'Coordinador de registro']));
        $this->student = User::factory()->create();
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'Estudiante']));
        $this->event = Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);

        $this->actingAs($this->registerCoordinator);
    }

    public function test_the_closed_deadline_blocks_loading_attendees_from_eventbrite(): void
    {
        // Comportamiento de referencia: la importación ya respeta el plazo
        $this->get(route('registercoordinator.attendee.load', ['id' => '111']))->assertNotFound();
    }

    public function test_the_closed_deadline_also_blocks_creating_attendances_by_hand(): void
    {
        $this->post(route('attendee.store'), [
            'event_id' => $this->event->id,
            'user_id' => $this->student->id,
            'status' => 'Checked In',
        ]);

        $this->assertSame(0, Attendee::count(), 'Attendance hours credited after the deadline');
        $this->assertSame(0, $this->student->fresh()->events_hours());
    }

    public function test_the_closed_deadline_also_blocks_editing_and_deleting_attendances(): void
    {
        $attendee = Attendee::forceCreate(['event_id' => $this->event->id, 'user_id' => $this->student->id, 'status' => 'Attending']);

        $this->post(route('attendee.update', ['id' => $attendee->id]), [
            'event_id' => $this->event->id,
            'user_id' => $this->student->id,
            'status' => 'Checked In',
        ]);
        $this->assertSame('Attending', $attendee->fresh()->status, 'Attendance edited after the deadline');

        $this->delete(route('attendee.destroy', ['id' => $attendee->id]));
        $this->assertNotNull($attendee->fresh(), 'Attendance deleted after the deadline');
    }
}
