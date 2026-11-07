<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Committee;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\Point;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgreementSearchTest extends TestCase
{
    use RefreshDatabase;

    private MeetingMinutes $logistics;

    private MeetingMinutes $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logistics = $this->minutes('Reunión de logística', '2026-10-20 17:00', 'Logística', [
            'Presupuesto' => ['ACU-01' => 'Aprobar el presupuesto de cartelería', 'ACU-02' => 'Pedir tres presupuestos de catering'],
        ]);
        $this->program = $this->minutes('Reunión de programa', '2026-10-27 17:00', 'Programa', [
            'Ponentes' => ['PRG-07' => 'Invitar a la ponente de inteligencia artificial'],
        ]);
    }

    public function test_any_signed_in_person_finds_agreements_by_words(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('meeting.search', ['q' => 'presupuesto']))
            ->assertOk()
            ->assertSee('ACU-01')
            ->assertSee('ACU-02')
            ->assertDontSee('PRG-07')
            ->assertSee(route('activity.minutes', ['id' => $this->logistics->id]));
    }

    public function test_finds_an_agreement_by_its_identifier_first(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('meeting.search', ['q' => 'prg-07']))
            ->assertOk()
            ->assertSee('Invitar a la ponente')
            ->assertDontSee('ACU-01');
    }

    public function test_lists_every_agreement_of_one_meeting(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('meeting.search', ['acta' => $this->program->id]))
            ->assertOk()
            ->assertSee('Acuerdos de')
            ->assertSee('PRG-07')
            ->assertDontSee('ACU-01');
    }

    public function test_finds_meetings_by_words_of_their_points(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('meeting.search', ['tab' => 'reuniones', 'q' => 'ponentes']))
            ->assertOk()
            ->assertSee('Reunión de programa')
            ->assertDontSee('Reunión de logística')
            ->assertSee(route('meeting.search', ['acta' => $this->program->id]), false);
    }

    public function test_guests_are_sent_to_the_login(): void
    {
        $this->get(route('meeting.search'))->assertRedirect(route('login'));
    }

    private function minutes(string $title, string $datetime, string $committee, array $points): MeetingMinutes
    {
        $committee = Committee::create(['name' => $committee]);
        $secretary = Secretary::forceCreate(['user_id' => User::factory()->create()->id, 'committee_id' => $committee->id]);
        $meeting = Meeting::forceCreate(['title' => $title, 'datetime' => $datetime, 'committee_id' => $committee->id]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $secretary->id]);

        foreach ($points as $pointTitle => $agreements) {
            $point = Point::create(['meeting_minutes_id' => $minutes->id, 'title' => $pointTitle, 'duration' => 10]);
            foreach ($agreements as $identificator => $description) {
                Agreement::create(['point_id' => $point->id, 'identificator' => $identificator, 'description' => $description]);
            }
        }

        return $minutes;
    }
}
