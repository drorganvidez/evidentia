<?php

namespace Tests\Feature\Screens\Management;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\MessageThread;
use App\Models\User;

/**
 * The record of a person for the president and the lecturers: the summary of their hours, their account, committees
 * and accesses, and one section at a time with every evidence, meeting, signature, attendance, bonus, annotation and
 * message, each one with its totals and its own empty state. Nobody else opens it.
 */
class StudentRecordScreenTest extends ScreenTestCase
{
    private function record(string $who, string $whose, array $query = []): string
    {
        $prefix = $who === 'profesor' ? 'lecture' : 'president';
        $html = $this->screen($who, route('profiles.view', ['id' => $this->person($whose)->id] + $query))->getContent();
        $this->assertStringContainsString(route("$prefix.user.list"), $html);

        return $html;
    }

    /** The text of the page with the tags out and the spaces collapsed. */
    private function text(string $html): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html))));
    }

    public function test_the_management_opens_the_header_and_the_evidences_of_a_student(): void
    {
        $student = $this->person('estudiante');
        $accepted = Evidence::where('title', 'Cartelería del vestíbulo')->sole();

        foreach (['presidente' => 'president', 'profesor' => 'lecture'] as $who => $prefix) {
            $html = $this->record($who, 'estudiante');
            $text = $this->text($html);

            $this->assertStringContainsString('Lucía Pérez Romero', $html);
            $this->assertStringContainsString('Horas computadas', $html);
            $this->assertStringContainsString('aria-label="Secciones del expediente"', $html);
            $this->assertStringContainsString('Usuario estudiante', $text);
            $this->assertStringContainsString('estudiante@e2e.test', $html);
            $this->assertStringContainsString('Comités Ninguno', $text);
            $this->assertStringContainsString('Equipo de montaje · Logística', $text);
            $this->assertStringContainsString('Último acceso Nunca', $text);
            $this->assertStringContainsString('href="'.route("$prefix.user.management", ['id' => $student->id]).'"', $html);

            // Every section with its count, the evidences open and marked as the current one
            foreach (['Evidencias 3', 'Reuniones 1', 'Firmas 1', 'Asistencias 1', 'Bonos 1', 'Mensajes 1'] as $tab) {
                $this->assertStringContainsString($tab, $text);
            }
            $this->assertMatchesRegularExpression('/href="[^"]*\/profiles\/view\/'.$student->id.'#secciones"[^>]*aria-current="page"/', $html);
            $this->assertStringContainsString('href="'.route('profiles.view', ['id' => $student->id, 'tab' => 'reuniones']).'#secciones"', $html);

            $this->assertStringContainsString('Evidencias enviadas', $html);
            $this->assertStringContainsString(route('profiles.view.evidence', ['id_user' => $student->id, 'id_evidence' => $accepted->id]), $html);
            $this->assertStringContainsString('href="'.route('activity.evidence', ['id' => $accepted->id]).'"', $html);
            $this->assertStringContainsString('Faltan las fotos del montaje terminado.', $html, 'The reason of the rejected one');
            $this->assertStringNotContainsString('Borrador de la memoria', $html);
            $this->assertStringContainsString('3 enviadas · 1 aceptada · 1 pendiente · 1 rechazada', $text);
            $this->assertStringContainsString('2,00 h aceptadas de 4,25 h', $text);
            $this->assertNoAutofocus($html);
        }
    }

    public function test_the_meetings_the_signatures_the_attendances_and_the_bonuses(): void
    {
        $minutes = MeetingMinutes::sole();
        $request = MeetingRequest::sole();

        $html = $this->record('profesor', 'estudiante', ['tab' => 'reuniones']);
        $this->assertStringContainsString('Reunión de logística', $html);
        $this->assertStringContainsString('href="'.route('activity.minutes', ['id' => $minutes->id]).'"', $html);
        $this->assertStringContainsString('href="'.route('lecture.committee.view', ['id' => Committee::where('name', 'Logística')->value('id')]).'"', $html);
        $this->assertStringContainsString('1 reunión 2,00 h computadas', $this->text($html));
        $this->assertStringNotContainsString('Evidencias enviadas', $html, 'One section at a time');

        $html = $this->record('presidente', 'estudiante', ['tab' => 'firmas']);
        $text = $this->text($html);
        $this->assertStringContainsString('Hojas firmadas', $html);
        $this->assertStringContainsString('Hoja de firmas de logística', $html);
        $this->assertStringContainsString('href="'.route('activity.request', ['id' => $request->id]).'"', $html);
        $this->assertStringContainsString('1 firma', $text);
        $this->assertStringContainsString('Convocatorias de sus comités', $html);
        $this->assertStringContainsString('Firmó', $html);
        $this->assertStringContainsString('1 convocatoria · 1 con constancia · 0 sin constancia', $text);

        $html = $this->record('presidente', 'estudiante', ['tab' => 'asistencias']);
        $this->assertStringContainsString('Taller de Git', $html);
        $this->assertStringContainsString('Asistido', $html);
        $this->assertStringContainsString('1 asistencia · 1 asistida 1,50 h computadas', $this->text($html));

        $html = $this->record('presidente', 'estudiante', ['tab' => 'bonos']);
        $this->assertStringContainsString('Montaje del escenario', $html);
        $this->assertStringContainsString('1 bono 3,00 h computadas', $this->text($html));
    }

    public function test_the_activity_and_the_messages(): void
    {
        $student = $this->person('estudiante');
        $thread = MessageThread::where('subject', 'Duda sobre el photocall')->sole();
        $evidence = Evidence::where('title', 'Montaje del photocall')->sole();

        $html = $this->record('profesor', 'estudiante', ['tab' => 'actividad']);
        $text = $this->text($html);
        $this->assertStringContainsString('aria-label="Ámbito de la actividad"', $html);
        $this->assertStringContainsString('aria-label="Actividad de Lucía Pérez Romero"', $html);
        $this->assertStringContainsString('Evidencia enviada', $html);
        $this->assertStringContainsString('Evidencia rechazada', $html);
        $this->assertStringContainsString('href="'.route('activity.evidence', ['id' => $evidence->id]).'"', $html, 'Each annotation names its subject');
        foreach (['Todo', 'Lo que ha hecho', 'Lo que le afecta', 'Accesos'] as $scope) {
            $this->assertStringContainsString($scope, $text);
        }

        // What the student did, without what others did on their things
        $html = $this->record('profesor', 'estudiante', ['tab' => 'actividad', 'ambito' => 'hecho']);
        $this->assertStringContainsString('Evidencia enviada', $html);
        $this->assertStringNotContainsString('Evidencia rechazada', $html);
        $html = $this->record('profesor', 'estudiante', ['tab' => 'actividad', 'ambito' => 'afecta']);
        $this->assertStringContainsString('Evidencia rechazada', $html);
        $this->assertStringContainsString('por Carlos Navarro Ruiz', $this->text($html));
        $this->assertStringNotContainsString('Evidencia enviada', $html);
        $html = $this->record('profesor', 'estudiante', ['tab' => 'actividad', 'ambito' => 'accesos']);
        $this->assertStringContainsString('Sin anotaciones', $html);

        $html = $this->record('presidente', 'estudiante', ['tab' => 'mensajes']);
        $this->assertStringContainsString('Mensajes que ha escrito', $html);
        $this->assertStringContainsString('href="'.route('messages.show', $thread).'"', $html);
        $this->assertStringContainsString('Coordinación de Logística', $html);
        $this->assertStringContainsString('1 hilo 1 mensaje escrito', $this->text($html));
        $this->assertStringNotContainsString('Cambio de turno en el registro', $html, 'Only the threads the person wrote in');
        $this->assertSame($student->id, $thread->author_id);
    }

    public function test_each_section_has_its_own_empty_state(): void
    {
        $empty = [
            'reuniones' => 'No consta en ninguna reunión',
            'firmas' => 'No ha firmado ninguna hoja',
            'asistencias' => 'Sin asistencias a eventos',
            'bonos' => 'Sin bonos',
        ];
        foreach ($empty as $tab => $title) {
            $this->assertStringContainsString($title, $this->record('presidente', 'companero', ['tab' => $tab]));
        }

        $html = $this->record('presidente', 'presidente');
        $this->assertStringContainsString('No ha enviado evidencias', $html);
        $this->assertStringContainsString('No ha escrito mensajes', $this->record('presidente', 'presidente', ['tab' => 'mensajes']));
        $this->assertStringContainsString('Sin convocatorias en sus comités', $this->record('presidente', 'presidente', ['tab' => 'firmas']));
    }

    public function test_the_requests_of_their_committees_tell_how_the_person_shows_in_each(): void
    {
        $logistics = Committee::where('name', 'Logística')->sole();
        $meeting = Meeting::sole();
        MeetingRequest::forceCreate([
            'title' => 'Reunión de arranque', 'place' => 'Aula A3.10', 'datetime' => now()->subDays(3), 'type' => '1', 'modality' => '1',
            'committee_id' => $logistics->id, 'meeting_id' => $meeting->id,
        ]);
        MeetingRequest::forceCreate([
            'title' => 'Reunión de cierre', 'place' => 'Aula A3.10', 'datetime' => now()->subDays(10), 'type' => '1', 'modality' => '1',
            'committee_id' => $logistics->id,
        ]);

        $text = $this->text($this->record('profesor', 'estudiante', ['tab' => 'firmas']));
        $this->assertMatchesRegularExpression('/Convocatoria de logística .*Firmó.*Reunión de arranque .*En el acta.*Reunión de cierre .*Sin constancia/', $text);
        $this->assertStringContainsString('3 convocatorias · 2 con constancia · 1 sin constancia', $text);

        // The classmate is in the list of the committee but signed nothing and is in no minutes
        $text = $this->text($this->record('profesor', 'companero', ['tab' => 'firmas']));
        $this->assertMatchesRegularExpression('/Convocatoria de logística .*Próxima.*Reunión de arranque .*Sin constancia/', $text);
    }

    public function test_the_committees_they_run_and_their_accesses(): void
    {
        $logistics = Committee::where('name', 'Logística')->sole();
        $coordinator = $this->person('coordinador');
        foreach ([now()->subDays(2), now()->subHour()] as $at) {
            ActivityLog::create(['created_at' => $at, 'action' => ActivityAction::Login->value, 'user_id' => $coordinator->id]);
        }

        $html = $this->record('presidente', 'coordinador');
        $text = $this->text($html);
        $this->assertStringContainsString('Comités Coordinación Logística', $text);
        $this->assertStringContainsString('href="'.route('president.committee.view', ['id' => $logistics->id]).'"', $html);
        $this->assertStringContainsString('Último acceso hace 1 hora', $text);
        $this->assertStringContainsString('Accesos 2', $text);

        $html = $this->record('presidente', 'coordinador', ['tab' => 'actividad', 'ambito' => 'accesos']);
        $this->assertStringContainsString('Inicio de sesión', $html);

        $this->assertStringContainsString('Comités Secretaría Logística', $this->text($this->record('profesor', 'secretario')));

        User::where('username', 'companero')->update(['block' => true]);
        $this->assertStringContainsString('Estado Bloqueada', $this->text($this->record('profesor', 'companero')));
    }

    public function test_the_activity_goes_a_page_at_a_time_and_keeps_its_section(): void
    {
        $student = $this->person('estudiante');
        foreach (range(1, 31) as $minute) {
            ActivityLog::create(['created_at' => now()->subMinutes($minute), 'action' => ActivityAction::Login->value, 'user_id' => $student->id]);
        }

        $html = $this->record('profesor', 'estudiante', ['tab' => 'actividad', 'ambito' => 'accesos']);
        $this->assertStringContainsString('31 anotaciones', $html);
        $this->assertStringContainsString('aria-label="Paginación de la actividad"', $html);
        $this->assertStringContainsString(e(route('profiles.view', ['id' => $student->id, 'tab' => 'actividad', 'ambito' => 'accesos', 'page' => 2]).'#secciones'), $html);
    }

    public function test_an_unknown_section_opens_the_evidences(): void
    {
        $id = $this->person('estudiante')->id;

        $this->screen('profesor', route('profiles.view', ['id' => $id, 'tab' => 'nada']))->assertSee('Evidencias enviadas');
        $this->screen('profesor', '/profiles/view/'.$id.'?tab[]=bonos&ambito[]=x')->assertSee('Evidencias enviadas');
        $this->screen('profesor', route('profiles.view', ['id' => $id, 'tab' => 'actividad', 'ambito' => 'nada']))->assertSee('Evidencia enviada');
    }

    public function test_only_the_management_opens_it(): void
    {
        $id = $this->person('estudiante')->id;
        foreach (['estudiante', 'companero', 'coordinador', 'secretario', 'registro'] as $who) {
            foreach (['', '?tab=actividad', '?tab=mensajes'] as $query) {
                $this->actingAs($this->person($who))->get(route('profiles.view', ['id' => $id]).$query)->assertNotFound();
            }
        }
        $this->actingAs($this->person('profesor'))->get(route('profiles.view', ['id' => 999]))->assertNotFound();
    }
}
