<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\Role;
use App\Models\User;
use App\Services\Messaging\Audience;
use App\Services\Messaging\QuickContact;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Each list of a student leads to who answers for it: the coordination for the evidences, the secretary for the
 * meetings and the register coordination for the attendances, already chosen in the message form.
 */
class QuickContactTest extends TestCase
{
    use RefreshDatabase;

    private Committee $logistics;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
        $this->logistics = Committee::where('name', 'Logística')->sole();
    }

    private function person(string $username): User
    {
        return User::where('username', $username)->sole();
    }

    private function queryOf(string $href): array
    {
        parse_str((string) parse_url($href, PHP_URL_QUERY), $query);

        return $query;
    }

    public function test_the_evidences_lead_to_the_coordination_of_the_latest_accepted_one(): void
    {
        $communication = Committee::where('name', 'Comunicación')->sole();
        $student = $this->person('estudiante');

        $this->assertSame(['to' => 'COORDINATOR:'.$this->logistics->id, 'subject' => 'Sobre mis evidencias'], $this->queryOf(QuickContact::coordination($student)));

        Evidence::create([
            'title' => 'Notas de prensa', 'description' => '<p>Notas.</p>', 'hours' => 1, 'status' => 'ACCEPTED',
            'user_id' => $student->id, 'committee_id' => $communication->id,
        ]);
        $this->assertSame('COORDINATOR:'.$communication->id, $this->queryOf(QuickContact::coordination($student))['to']);

        // the pending one counts when nothing was accepted
        $this->assertSame('COORDINATOR:'.$this->logistics->id, $this->queryOf(QuickContact::coordination($this->person('companero')))['to']);
        // nobody to suggest: the form opens without an addressee
        $this->assertSame(['subject' => 'Sobre mis evidencias'], $this->queryOf(QuickContact::coordination(User::factory()->create())));
    }

    public function test_the_meetings_lead_to_the_secretary_of_the_latest_one_attended_or_signed(): void
    {
        $this->assertSame(['to' => 'SECRETARY:'.$this->logistics->id, 'subject' => 'Sobre mis reuniones'], $this->queryOf(QuickContact::secretary($this->person('estudiante'))));
        // no meeting nor sheet: the committee of their evidences
        $this->assertSame('SECRETARY:'.$this->logistics->id, $this->queryOf(QuickContact::secretary($this->person('companero')))['to']);
        $this->assertArrayNotHasKey('to', $this->queryOf(QuickContact::secretary($this->person('profesor'))));
    }

    public function test_the_attendances_lead_to_the_register_coordination(): void
    {
        $this->assertSame(['to' => 'REGISTER_COORDINATOR', 'subject' => 'Sobre mis asistencias'], $this->queryOf(QuickContact::register()));
    }

    public function test_the_addressees_put_the_committees_of_accepted_evidences_first(): void
    {
        $communication = Committee::where('name', 'Comunicación')->sole();
        $classmate = $this->person('companero');
        $coordinator = User::factory()->create();
        $coordinator->roles()->attach(Role::where('rol', 'COORDINATOR')->sole());
        Coordinator::forceCreate(['user_id' => $coordinator->id, 'committee_id' => $communication->id]);

        // pending in Logística: Logística first
        $this->assertSame('COORDINATOR:'.$this->logistics->id, Audience::optionsFor($classmate, null)->first()['value']);

        Evidence::create([
            'title' => 'Notas de prensa', 'description' => '<p>Notas.</p>', 'hours' => 1, 'status' => 'ACCEPTED',
            'user_id' => $classmate->id, 'committee_id' => $communication->id,
        ]);
        $this->assertSame('COORDINATOR:'.$communication->id, Audience::optionsFor($classmate, null)->first()['value']);
    }

    public function test_each_list_of_the_student_carries_its_contact(): void
    {
        $student = $this->person('estudiante');
        $minutes = MeetingMinutes::sole();
        $this->actingAs($student);

        $see = function (string $route, array $texts): string {
            $html = preg_replace('/\s+/', ' ', $this->get(route($route))->assertOk()->getContent());
            foreach ($texts as $text) {
                $this->assertStringContainsString($text, $html);
            }

            return $html;
        };

        $see('evidence.list', ['Escribir a la coordinación', 'href="'.e(QuickContact::coordination($student)).'"']);
        $see('meeting.list', [
            'Escribir a la secretaría',
            'aria-label="Escribir a la secretaría sobre Reunión de logística"',
            'href="'.route('messages.create', ['minutes' => $minutes->id]).'"',
        ]);
        $see('attendee.list', [
            'Escribir a la coordinación de registro',
            'aria-label="Escribir a la coordinación de registro sobre Taller de Git"',
        ]);

        // the minutes preselect their secretary
        $this->get(route('messages.create', ['minutes' => $minutes->id]))->assertOk()
            ->assertSee('value="SECRETARY:'.$this->logistics->id.'" selected', false);
        $this->get(QuickContact::register())->assertOk()
            ->assertSee('value="REGISTER_COORDINATOR" selected', false)
            ->assertSee('value="Sobre mis asistencias"', false);
    }

    public function test_what_the_presidency_reviews_asks_the_presidency(): void
    {
        // Inés coordinates Logística and sent an evidence to it; Marta sent one to the committee of the presidency
        foreach (['coordinadora', 'secretario'] as $who) {
            $author = $this->person($who);
            $this->assertSame(['to' => 'PRESIDENT', 'subject' => 'Sobre mis evidencias'], $this->queryOf(QuickContact::coordination($author)), $who);
            $this->assertSame('Escribir a presidencia', QuickContact::coordinationLabel($author), $who);

            $evidence = Evidence::where('user_id', $author->id)->where('status', 'PENDING')->sole();
            $this->assertSame('PRESIDENT', Audience::optionsFor($author, $evidence)->first()['value'], $who);
        }

        // what the coordination reviews still asks the coordination
        $student = $this->person('estudiante');
        $this->assertSame('Escribir a la coordinación', QuickContact::coordinationLabel($student));
        $this->assertSame('COORDINATOR:'.$this->logistics->id, Audience::optionsFor($student, Evidence::where('title', 'Montaje del photocall')->sole())->first()['value']);
    }

    public function test_the_evidence_pages_name_who_to_write_to(): void
    {
        $this->actingAs($this->person('coordinadora'));
        $mine = Evidence::where('title', 'Inventario del almacén')->sole();

        $this->get(route('activity.evidence', ['id' => $mine->id]))->assertOk()->assertSee('Escribir a presidencia')->assertDontSee('Escribir al coordinador');
        $this->get(route('evidence.list'))->assertOk()->assertSee('Escribir a presidencia');

        $this->actingAs($this->person('estudiante'));
        $this->get(route('activity.evidence', ['id' => Evidence::where('title', 'Recogida de material')->sole()->id]))->assertOk()->assertSee('Escribir al coordinador');
    }
}
