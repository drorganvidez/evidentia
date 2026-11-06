<?php

namespace Tests\Feature;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Attendee;
use App\Models\Committee;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;
use App\Services\SelfReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\HttpResponseException;
use Tests\TestCase;

class SelfReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    private User $student;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->committee = Committee::create(['name' => 'Logística']);
        $this->coordinator = User::factory()->create();
        $this->coordinator->roles()->attach(Role::forceCreate(['rol' => 'COORDINATOR', 'slug' => 'coordinator']));
        $this->student = User::factory()->create();
    }

    public function test_it_spots_the_own_evidence_attendance_person_and_sheet(): void
    {
        $own = Evidence::create(['title' => 'Mía', 'status' => 'PENDING', 'user_id' => $this->coordinator->id, 'committee_id' => $this->committee->id]);
        $other = Evidence::create(['title' => 'Ajena', 'status' => 'PENDING', 'user_id' => $this->student->id, 'committee_id' => $this->committee->id]);
        $event = Event::forceCreate(['name' => 'Charla', 'hours' => 1]);
        $ownAttendance = Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $this->coordinator->id, 'status' => 'Checked In']);
        $secretary = Secretary::forceCreate(['user_id' => $this->coordinator->id, 'committee_id' => $this->committee->id]);
        $ownSheet = SignatureSheet::create(['title' => 'Hoja', 'random_identifier' => '1111', 'secretary_id' => $secretary->id]);
        $otherSheet = SignatureSheet::create(['title' => 'Otra', 'random_identifier' => '2222', 'secretary_id' => Secretary::forceCreate(['user_id' => $this->student->id])->id]);

        $this->assertTrue(SelfReview::violates($this->coordinator, $own));
        $this->assertFalse(SelfReview::violates($this->coordinator, $other));
        $this->assertTrue(SelfReview::violates($this->coordinator, $ownAttendance));
        $this->assertTrue(SelfReview::violates($this->coordinator, new Attendee(['event_id' => $event->id, 'user_id' => $this->coordinator->id])));
        $this->assertFalse(SelfReview::violates($this->student, $ownAttendance));
        $this->assertTrue(SelfReview::violates($this->coordinator, $this->coordinator->id));
        $this->assertFalse(SelfReview::violates($this->coordinator, $this->student->id));
        $this->assertFalse(SelfReview::violates($this->coordinator, null));
        $this->assertTrue(SelfReview::violates($this->coordinator->fresh(), $ownSheet));
        $this->assertFalse(SelfReview::violates($this->coordinator->fresh(), $otherSheet));
        $this->assertFalse(SelfReview::violates(User::factory()->create(), $ownSheet));
    }

    public function test_denying_annotates_the_attempt_and_goes_back_with_the_error(): void
    {
        $own = Evidence::create(['title' => 'Mía', 'status' => 'PENDING', 'user_id' => $this->coordinator->id, 'committee_id' => $this->committee->id]);
        $this->actingAs($this->coordinator);

        try {
            SelfReview::deny($own, ActivityAction::EvidenceAccepted, $this->coordinator, 'No puedes aceptar ni rechazar una evidencia tuya. El intento queda anotado.');
            $this->fail('deny() went on.');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $this->assertTrue($response->isRedirect());
            $this->assertSame('No puedes aceptar ni rechazar una evidencia tuya. El intento queda anotado.', session('error'));
        }

        $log = ActivityLog::sole();
        $this->assertSame('self_review.blocked', $log->action);
        $this->assertSame(['self_review'], $log->flags);
        $this->assertTrue($log->flagged);
        $this->assertSame(['attempted' => 'evidence.accepted'], $log->details);
        $this->assertSame($own->id, $log->subject_id);
        $this->assertSame($this->coordinator->id, $log->user_id);
        $this->assertSame($this->coordinator->id, $log->target_user_id);
        $this->assertSame($this->committee->id, $log->committee_id);
    }

    public function test_denying_an_unsaved_attendance_keeps_the_title(): void
    {
        $event = Event::forceCreate(['name' => 'Charla IA', 'hours' => 1]);
        $this->actingAs($this->coordinator);

        try {
            SelfReview::deny(new Attendee(['event_id' => $event->id, 'user_id' => $this->coordinator->id]), ActivityAction::AttendeeCreated, $this->coordinator, 'No puedes registrar ni editar tu propia asistencia. El intento queda anotado.');
        } catch (HttpResponseException) {
        }

        $log = ActivityLog::sole();
        $this->assertSame('attendee', $log->subject_type);
        $this->assertNull($log->subject_id);
        $this->assertStringStartsWith('Charla IA · ', $log->subject_title);
        $this->assertNull($log->committee_id);
    }
}
