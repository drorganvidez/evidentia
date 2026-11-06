<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Configuration;
use App\Models\Evidence;

class CoordinatorReviewScreenTest extends ScreenTestCase
{
    public function test_every_tab_renders_on_the_new_layout(): void
    {
        $this->screen('coordinador', route('coordinator.evidence.list.all'))
            ->assertSee('Revisar evidencias')
            ->assertSee('Montaje del photocall')
            ->assertSee('Atención en el registro')
            ->assertDontSee('Borrador de la memoria');

        $this->screen('coordinador', route('coordinator.evidence.list.pending'))
            ->assertSee('Montaje del photocall')
            ->assertDontSee('Cartelería del vestíbulo');

        $this->screen('coordinador', route('coordinator.evidence.list.accepted'))
            ->assertSee('Cartelería del vestíbulo')
            ->assertDontSee('Montaje del photocall');

        $this->screen('coordinador', route('coordinator.evidence.list.rejected'))
            ->assertSee('Recogida de material')
            ->assertSee('Faltan las fotos del montaje terminado.');
    }

    public function test_the_actions_post_to_the_review_routes(): void
    {
        $pending = Evidence::where('title', 'Montaje del photocall')->sole();

        $this->screen('coordinador', route('coordinator.evidence.list.pending'))
            ->assertSee('action="'.route('coordinator.evidence.accept', ['id' => $pending->id]).'"', false)
            ->assertSee('action="'.route('coordinator.evidence.reject').'"', false)
            ->assertSee('name="reasonrejection"', false)
            ->assertSee('name="_id" value="'.$pending->id.'"', false);
    }

    public function test_the_coordinator_cannot_review_their_own_evidence(): void
    {
        $own = Evidence::create([
            'title' => 'Inventario del almacén', 'description' => '<p>Inventario.</p>', 'hours' => 1, 'status' => 'PENDING',
            'user_id' => $this->person('coordinador')->id, 'committee_id' => Evidence::first()->committee_id,
        ]);

        $this->screen('coordinador', route('coordinator.evidence.list.pending'))
            ->assertSee('Inventario del almacén')
            ->assertDontSee('action="'.route('coordinator.evidence.accept', ['id' => $own->id]).'"', false)
            ->assertDontSee('name="_id" value="'.$own->id.'"', false);
    }

    public function test_the_actions_disappear_once_the_validation_closes(): void
    {
        Configuration::query()->update(['validate_evidences_timestamp' => now()->subDay()]);

        $this->screen('coordinador', route('coordinator.evidence.list.all'))
            ->assertSee('Montaje del photocall')
            ->assertDontSee('name="reasonrejection"', false)
            ->assertDontSee('/coordinator/evidence/accept/', false);
    }

    public function test_an_empty_tab_says_so(): void
    {
        Evidence::where('status', 'REJECTED')->delete();

        $this->screen('coordinador', route('coordinator.evidence.list.rejected'))
            ->assertSee('No hay evidencias rechazadas');
    }
}
