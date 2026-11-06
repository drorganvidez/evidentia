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

    public function test_the_evidences_of_the_coordinators_wait_in_the_presidency_not_in_the_queue(): void
    {
        $own = Evidence::create([
            'title' => 'Recuento de sillas', 'description' => '<p>Recuento.</p>', 'hours' => 1, 'status' => 'PENDING',
            'user_id' => $this->person('coordinador')->id, 'committee_id' => Evidence::first()->committee_id,
        ]);

        foreach (['all', 'pending'] as $tab) {
            $this->screen('coordinador', route('coordinator.evidence.list.'.$tab))
                ->assertSee('Montaje del photocall')
                ->assertDontSee('Recuento de sillas')
                // the one of Inés, the second coordinator of Logística
                ->assertDontSee('Inventario del almacén')
                ->assertDontSee('name="_id" value="'.$own->id.'"', false);
        }
    }

    public function test_the_tabs_count_the_queue(): void
    {
        $this->screen('coordinador', route('coordinator.evidence.list.all'))
            ->assertSeeInOrder(['Todas', '4', 'Pendientes', '2', 'Aceptadas', '1', 'Rechazadas', '1']);
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
