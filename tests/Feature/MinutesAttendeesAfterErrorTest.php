<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MinutesAttendeesAfterErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendees_added_while_editing_survive_a_validation_error(): void
    {
        Storage::fake();
        DB::table('configuration')->insert(['id' => 1, 'meetings_timestamp' => now()->addMonth()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $committee = Committee::create(['name' => 'Logística']);
        Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
        $this->actingAs($user);
        [$ana, $pedro] = User::factory()->count(2)->create();
        $form = [
            'title' => 'Reunión inicial de Logística', 'type' => 1, 'modality' => 1, 'hours' => 1,
            'place' => 'Aula A3.10', 'date' => '2026-10-16', 'time' => '16:30', 'points_json' => '[]',
        ];
        $this->post(route('secretary.meeting.manage.minutes.create.step3_p'), $form + ['users' => [$ana->id]]);
        $minutes = Meeting::sole()->meetingMinutes;
        $editUrl = route('secretary.meeting.manage.minutes.edit', $minutes->id);

        // Añade a Pedro, pero borra sin querer las horas: vuelve al formulario con el error
        $html = $this->from($editUrl)->followingRedirects()->post(route('secretary.meeting.manage.minutes.save'),
            ['hours' => null, 'users' => [$ana->id, $pedro->id]] + $form + ['_id' => $minutes->id])
            ->assertOk()->assertSee('Hay errores en el formulario')->getContent();

        $this->assertEqualsCanonicalizing([(string) $ana->id, (string) $pedro->id], $this->selectedAttendees($html),
            'El formulario vuelve sin el asistente que se acababa de añadir');
    }

    private function selectedAttendees(string $html): array
    {
        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $ids = [];
        foreach ((new DOMXPath($dom))->query('//select[@name="users[]"]/option[@selected]') as $option) {
            $ids[] = $option->getAttribute('value');
        }

        return $ids;
    }
}
