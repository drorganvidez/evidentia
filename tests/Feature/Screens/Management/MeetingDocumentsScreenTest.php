<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Committee;
use App\Models\DefaultList;
use App\Models\MeetingRequest;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;

/** The meeting calls, signature sheets and default lists of every committee, read only, for the lecturer and the president. */
class MeetingDocumentsScreenTest extends ScreenTestCase
{
    private const PREFIXES = ['profesor' => 'lecture', 'presidente' => 'president'];

    /** A second committee with its secretary, a newer call, its sheet and a list. */
    private function otherCommittee(): MeetingRequest
    {
        $committee = Committee::where('name', 'Comunicación')->sole();
        $user = User::factory()->create(['name' => 'Rosa', 'surname' => 'Vidal Soto']);
        $secretary = Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => $committee->id]);
        $request = MeetingRequest::forceCreate([
            'title' => 'Convocatoria de difusión', 'place' => 'Sala de juntas', 'datetime' => now()->addWeeks(2),
            'type' => '1', 'modality' => '1', 'committee_id' => $committee->id, 'secretary_id' => $secretary->id,
        ]);
        SignatureSheet::create(['title' => 'Firmas de difusión', 'random_identifier' => '5512', 'secretary_id' => $secretary->id])
            ->users()->attach($this->person('companero'));
        DefaultList::create(['name' => 'Equipo de redes', 'secretary_id' => $secretary->id])
            ->users()->attach($this->person('registro'));

        return $request;
    }

    public function test_the_meeting_calls_of_every_committee_link_their_detail_history_and_pdf(): void
    {
        $logistics = MeetingRequest::where('title', 'Convocatoria de logística')->sole();
        $other = $this->otherCommittee();

        foreach (self::PREFIXES as $who => $prefix) {
            $html = $this->screen($who, route($prefix.'.meeting.request.list'))
                ->assertSee('Convocatorias de todos los comités')
                ->assertSee('Convocatoria de logística')
                ->assertSee('Convocatoria de difusión')
                ->assertSee('Comunicación')
                ->assertSee('Ruiz Díaz, Marta')
                ->assertSee('Con hoja')
                ->assertSee('Sin hoja')
                ->assertSee('href="'.route('activity.request', ['id' => $logistics->id]).'"', false)
                ->assertSee('href="'.e(route('activity.request', ['id' => $logistics->id, 'tab' => 'historial'])).'"', false)
                ->assertSee('href="'.route('download.request', ['id' => $other->id]).'"', false)
                ->assertSee('aria-current="page"', false)
                ->assertDontSee('Nueva convocatoria')
                ->assertDontSee('/secretary/meeting/manage/request/edit', false)
                ->getContent();

            $this->assertLessThan(strpos($html, 'Convocatoria de logística'), strpos($html, 'Convocatoria de difusión'));
        }
    }

    public function test_the_signature_sheets_of_every_committee_show_who_signed(): void
    {
        $this->otherCommittee();

        foreach (self::PREFIXES as $who => $prefix) {
            $this->screen($who, route($prefix.'.meeting.signaturesheet.list'))
                ->assertSee('Hojas de firmas de todos los comités')
                ->assertSee('Hoja de firmas de logística')
                ->assertSee('Pérez Romero, Lucía')
                ->assertSee('Firmas de difusión')
                ->assertSee('García López, Juan')
                ->assertSee('Sin convocatoria')
                ->assertDontSee('Nueva hoja de firmas')
                ->assertDontSee('/sign/7391', false);
        }
    }

    public function test_the_default_lists_of_every_secretary_show_their_people(): void
    {
        $this->otherCommittee();

        foreach (self::PREFIXES as $who => $prefix) {
            $this->screen($who, route($prefix.'.meeting.defaultlist.list'))
                ->assertSee('Listas de todos los comités')
                ->assertSee('Equipo de montaje')
                ->assertSee('García López, Juan; Pérez Romero, Lucía')
                ->assertSee('Equipo de redes')
                ->assertSee('Vidal Soto, Rosa')
                ->assertDontSee('Nueva lista');
        }
    }

    public function test_the_meetings_link_the_other_sections(): void
    {
        $this->screen('presidente', route('president.meeting.list'))
            ->assertSee('Secciones de las reuniones')
            ->assertSee('href="'.route('president.meeting.request.list').'"', false)
            ->assertSee('href="'.route('president.meeting.signaturesheet.list').'"', false)
            ->assertSee('href="'.route('president.meeting.defaultlist.list').'"', false);
    }

    public function test_empty_sections_say_so(): void
    {
        SignatureSheet::query()->delete();
        DefaultList::query()->delete();
        MeetingRequest::query()->delete();

        $this->screen('profesor', route('lecture.meeting.request.list'))->assertSee('No hay convocatorias');
        $this->screen('profesor', route('lecture.meeting.signaturesheet.list'))->assertSee('No hay hojas de firmas');
        $this->screen('profesor', route('lecture.meeting.defaultlist.list'))->assertSee('No hay listas');
    }

    public function test_nobody_else_opens_them(): void
    {
        foreach (['meeting.request.list', 'meeting.signaturesheet.list', 'meeting.defaultlist.list'] as $page) {
            foreach (['estudiante', 'secretario', 'coordinador'] as $who) {
                $status = $this->actingAs($this->person($who))->get(route('president.'.$page))->getStatusCode();
                $this->assertContains($status, [302, 403, 404], "$who $page");
            }
        }
    }
}
