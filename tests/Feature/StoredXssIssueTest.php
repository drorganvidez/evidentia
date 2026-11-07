<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// el ataque de la issue #156: un script guardado en borrador, editado de nuevo y publicado no llega a ejecutarse
class StoredXssIssueTest extends TestCase
{
    use RefreshDatabase;

    private const PAYLOAD = '<script>var l = window.location.href.split("/"); window.location="/coordinator/evidence/accept/"+l[l.length-1];</script>';

    public function test_a_script_saved_in_a_draft_edited_and_published_never_runs(): void
    {
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
        ]);
        $committee = Committee::create(['name' => 'Programa']);
        $student = User::factory()->create();
        $student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $coordinator = User::factory()->create();
        $coordinator->roles()->attach(Role::forceCreate(['rol' => 'COORDINATOR', 'slug' => 'coordinator']));
        Coordinator::forceCreate(['user_id' => $coordinator->id, 'committee_id' => $committee->id]);
        $form = ['title' => 'Evidencia malvada', 'hours' => 1, 'committee' => $committee->id, 'description' => 'Texto de prueba '.self::PAYLOAD];

        $this->actingAs($student)->post(route('evidence.draft'), $form)->assertRedirect();
        $draft = Evidence::where('last', true)->latest('id')->firstOrFail();

        $this->get(route('evidence.edit', ['id' => $draft->id]))->assertOk()->assertDontSee(self::PAYLOAD, false);
        $this->post(route('evidence.draft.edit'), $form + ['_id' => $draft->id])->assertRedirect();
        $draft = Evidence::where('last', true)->latest('id')->firstOrFail();
        $this->post(route('evidence.publish.edit'), $form + ['_id' => $draft->id])->assertRedirect();
        $published = Evidence::where('last', true)->latest('id')->firstOrFail();

        $this->assertSame('PENDING', $published->status);
        foreach ([[$student, 'evidence.view'], [$coordinator, 'coordinator.evidence.view']] as [$user, $route]) {
            $this->actingAs($user)->get(route($route, ['id' => $published->id]))
                ->assertOk()
                ->assertDontSee('<script>var l', false)
                ->assertSee('Texto de prueba');
        }
        $this->assertSame('PENDING', $published->fresh()->status);
    }
}
