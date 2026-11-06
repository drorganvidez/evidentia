<?php

namespace Tests\Feature\Screens\Management;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Notifications\Events\EvidenceAccepted;
use App\Notifications\Events\EvidenceRejected;
use Illuminate\Support\Facades\Notification;

/**
 * The queue of the presidency over the end-to-end seed: what Inés, the second coordinator of Logística, sends to it and
 * what Marta sends to the committee of the presidency. The presidents review it as coordinators, the lecturer too, and
 * nobody else; the coordinators of Logística never review what Inés sends.
 */
class PresidencyReviewScreenTest extends ScreenTestCase
{
    private function evidence(string $title): Evidence
    {
        return Evidence::where('title', $title)->sole();
    }

    public function test_every_tab_renders_with_the_evidences_of_the_presidency(): void
    {
        $this->screen('presidente', route('president.review.all'))
            ->assertSee('Revisar evidencias')
            ->assertSee('Evidencias de Presidencia')
            ->assertSee('Inventario del almacén')
            ->assertSee('Acreditación de ponentes')
            ->assertSeeInOrder(['Comité', 'Inventario del almacén', 'Logística'])
            ->assertSeeInOrder(['Todas', '2', 'Pendientes', '2', 'Aceptadas', '0', 'Rechazadas', '0'])
            ->assertDontSee('Montaje del photocall')
            ->assertDontSee('Atención en el registro');

        $this->screen('presidente', route('president.review.accepted'))
            ->assertSee('No hay evidencias aceptadas');
        $this->screen('profesor', route('president.review.pending'))
            ->assertSee('Inventario del almacén');
    }

    public function test_the_actions_post_to_the_routes_of_the_presidency(): void
    {
        $inventory = $this->evidence('Inventario del almacén');

        $this->screen('presidente', route('president.review.pending'))
            ->assertSee('href="'.route('president.review.view', ['id' => $inventory->id]).'"', false)
            ->assertSee('action="'.route('president.review.accept', ['id' => $inventory->id]).'"', false)
            ->assertSee('action="'.route('president.review.reject').'"', false)
            ->assertSee('name="_id" value="'.$inventory->id.'"', false)
            ->assertDontSee('/coordinator/evidence/accept', false);
    }

    public function test_the_detail_reviews_for_the_presidency(): void
    {
        $inventory = $this->evidence('Inventario del almacén');

        $this->screen('presidente', route('president.review.view', ['id' => $inventory->id]))
            ->assertSee('Inventario del almacén')
            ->assertSee('action="'.route('president.review.accept', ['id' => $inventory->id]).'"', false)
            ->assertSee('action="'.route('president.review.reject').'"', false)
            ->assertSee('href="'.route('president.review.all').'"', false)
            ->assertDontSee('Escribir al coordinador');

        // an evidence the coordination reviews is not in the queue
        $this->actingAs($this->person('presidente'))
            ->get(route('president.review.view', ['id' => $this->evidence('Montaje del photocall')->id]))
            ->assertNotFound();
    }

    public function test_the_president_accepts_and_rejects_with_a_reason(): void
    {
        Notification::fake();
        $inventory = $this->evidence('Inventario del almacén');
        $accreditation = $this->evidence('Acreditación de ponentes');
        $this->actingAs($this->person('presidente'));

        $this->post(route('president.review.accept', ['id' => $inventory->id]))
            ->assertRedirect(route('president.review.accepted'))
            ->assertSessionHas('success', 'Evidencia aceptada con éxito.');
        $this->post(route('president.review.reject'), ['_id' => $accreditation->id, 'reasonrejection' => ''])
            ->assertSessionHas('error', 'Escribe el motivo del rechazo.');
        $this->post(route('president.review.reject'), ['_id' => $accreditation->id, 'reasonrejection' => 'Falta la lista de ponentes.'])
            ->assertRedirect(route('president.review.rejected'));

        $this->assertSame('ACCEPTED', $inventory->fresh()->status);
        $this->assertSame('REJECTED', $accreditation->fresh()->status);
        $this->assertSame('Falta la lista de ponentes.', $accreditation->fresh()->reasonRejection->reason);
        $accepted = ActivityLog::where('action', 'evidence.accepted')->latest('id')->first();
        $this->assertSame(['PRESIDENT', $this->person('presidente')->id, $inventory->id], [$accepted->acting_role, (int) $accepted->user_id, (int) $accepted->subject_id]);
        Notification::assertSentTo($this->person('coordinadora'), EvidenceAccepted::class);
        Notification::assertSentTo($this->person('secretario'), EvidenceRejected::class);
    }

    public function test_the_coordinators_of_logistics_never_review_what_ines_sends(): void
    {
        $inventory = $this->evidence('Inventario del almacén');
        $this->actingAs($this->person('coordinador'));

        // he may read it, as anything sent to his committee, but without the actions
        $this->get(route('coordinator.evidence.view', ['id' => $inventory->id]))
            ->assertOk()
            ->assertDontSee('action="'.route('coordinator.evidence.accept', ['id' => $inventory->id]).'"', false)
            ->assertDontSee('name="reasonrejection"', false);
        $this->post(route('coordinator.evidence.accept', ['id' => $inventory->id]))->assertNotFound();
        $this->post(route('coordinator.evidence.reject'), ['_id' => $inventory->id, 'reasonrejection' => 'No me toca.'])->assertNotFound();
        $this->post(route('coordinator.evidence.move'), ['_id' => $inventory->id, 'committee' => Committee::where('name', 'Comunicación')->value('id')])->assertNotFound();
        $this->get(route('president.review.all'))->assertNotFound();

        $this->assertSame('PENDING', $inventory->fresh()->status);
    }

    public function test_nobody_reviews_their_own_evidence_for_the_presidency(): void
    {
        $own = Evidence::create([
            'title' => 'Memoria de la presidencia', 'description' => '<p>Memoria.</p>', 'hours' => 1, 'status' => 'PENDING',
            'user_id' => $this->person('presidente')->id, 'committee_id' => Committee::presidency()->id,
        ]);
        $this->actingAs($this->person('presidente'));
        $blocked = ActivityLog::where('action', 'self_review.blocked')->count();

        $this->get(route('president.review.all'))->assertDontSee('Memoria de la presidencia');
        $this->get(route('president.review.view', ['id' => $own->id]))->assertNotFound();
        $this->post(route('president.review.accept', ['id' => $own->id]))->assertSessionHas('error');

        $this->assertSame('PENDING', $own->fresh()->status);
        $this->assertSame($blocked + 1, ActivityLog::where('action', 'self_review.blocked')->count());
    }

    public function test_the_actions_disappear_once_the_validation_closes(): void
    {
        Configuration::query()->update(['validate_evidences_timestamp' => now()->subDay()]);
        $inventory = $this->evidence('Inventario del almacén');

        $this->screen('presidente', route('president.review.all'))
            ->assertSee('Inventario del almacén')
            ->assertDontSee('name="reasonrejection"', false)
            ->assertDontSee('/president/review/accept/', false);
        $this->post(route('president.review.accept', ['id' => $inventory->id]))->assertNotFound();
    }
}
