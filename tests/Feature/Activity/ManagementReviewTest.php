<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The management accepts or rejects an evidence outside the flow of the coordinator, also late. */
class ManagementReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
    }

    private function lecturer(): User
    {
        return User::where('username', 'profesor')->sole();
    }

    private function evidence(string $title): Evidence
    {
        return Evidence::where('title', $title)->sole();
    }

    public function test_the_management_accepts_a_pending_evidence(): void
    {
        $evidence = $this->evidence('Montaje del photocall');

        $this->actingAs($this->lecturer())->post(route('management.evidence.review'), ['_id' => $evidence->id, 'decision' => 'accept'])
            ->assertRedirect(route('activity.evidence', ['id' => $evidence->id]))
            ->assertSessionHas('success', 'Evidencia aceptada.');

        $this->assertSame('ACCEPTED', $evidence->fresh()->status);
        $log = ActivityLog::where('action', 'evidence.accepted')->latest('id')->first();
        $this->assertSame('LECTURE', $log->acting_role);
        $this->assertSame('PENDING', $log->details['before']);
        $this->assertFalse($log->flagged);
    }

    public function test_a_rejection_needs_its_reason(): void
    {
        $evidence = $this->evidence('Atención en el registro');

        $this->actingAs($this->lecturer())->post(route('management.evidence.review'), ['_id' => $evidence->id, 'decision' => 'reject'])
            ->assertSessionHasErrors(['reasonrejection' => 'El campo motivo del rechazo es obligatorio si rechazas.']);
        $this->actingAs($this->lecturer())->post(route('management.evidence.review'), ['_id' => $evidence->id, 'decision' => 'quizá'])
            ->assertSessionHasErrors('decision');
        $this->assertSame('PENDING', $evidence->fresh()->status);

        $this->actingAs($this->lecturer())->post(route('management.evidence.review'), [
            '_id' => $evidence->id, 'decision' => 'reject', 'reasonrejection' => 'Falta el registro firmado del turno.',
        ])->assertSessionHas('success', 'Evidencia rechazada.');

        $this->assertSame('REJECTED', $evidence->fresh()->status);
        $this->assertSame('Falta el registro firmado del turno.', $evidence->fresh()->reasonRejection->reason);
    }

    public function test_a_late_review_is_flagged(): void
    {
        Configuration::query()->update(['validate_evidences_timestamp' => now()->subDay()]);
        $evidence = $this->evidence('Montaje del photocall');

        $this->actingAs(User::where('username', 'presidente')->sole())
            ->post(route('management.evidence.review'), ['_id' => $evidence->id, 'decision' => 'accept'])
            ->assertSessionHas('warning', 'Revisión fuera de plazo. Queda señalada en el registro.');

        $log = ActivityLog::where('action', 'evidence.accepted')->latest('id')->first();
        $this->assertSame('PRESIDENT', $log->acting_role);
        $this->assertSame(['after_deadline'], $log->flags);
    }

    public function test_nobody_reviews_their_own_evidence(): void
    {
        $president = User::where('username', 'presidente')->sole();
        $own = Evidence::create([
            'title' => 'Discurso de clausura', 'description' => '<p>Discurso.</p>', 'hours' => 1, 'status' => 'PENDING',
            'user_id' => $president->id, 'committee_id' => $this->evidence('Montaje del photocall')->committee_id,
        ]);

        $this->actingAs($president)->from(route('home'))
            ->post(route('management.evidence.review'), ['_id' => $own->id, 'decision' => 'accept'])
            ->assertRedirect(route('home'))
            ->assertSessionHas('error', 'No puedes revisar una evidencia tuya. El intento queda anotado.');

        $this->assertSame('PENDING', $own->fresh()->status);
        $this->assertSame('evidence.accepted', ActivityLog::where('action', 'self_review.blocked')->where('subject_id', $own->id)->sole()->details['attempted']);
    }

    public function test_drafts_and_other_roles_are_turned_away(): void
    {
        $this->actingAs($this->lecturer())
            ->post(route('management.evidence.review'), ['_id' => $this->evidence('Borrador de la memoria')->id, 'decision' => 'accept'])
            ->assertNotFound();
        $this->actingAs(User::where('username', 'coordinador')->sole())
            ->post(route('management.evidence.review'), ['_id' => $this->evidence('Montaje del photocall')->id, 'decision' => 'accept'])
            ->assertNotFound();
    }
}
