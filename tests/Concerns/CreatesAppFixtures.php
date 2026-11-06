<?php

namespace Tests\Concerns;

use App\Models\Agreement;
use App\Models\Attendee;
use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\DefaultList;
use App\Models\Diary;
use App\Models\DiaryPoint;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Point;
use App\Models\Proof;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\Token;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * One person per role and one row of every kind (evidences, requests, sheets, minutes, bonuses, lists, events,
 * attendances, tokens), all inside open deadlines. The same data as the page access test, plus a second committee
 * with its own coordinator.
 */
trait CreatesAppFixtures
{
    /** @var array<string, User> */
    protected array $users = [];

    /** @var array<string, int|string> */
    protected array $ids = [];

    /** @var array<string, User> */
    protected array $extra = [];

    protected function createAppFixtures(): void
    {
        Storage::fake();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addMonth(),
            'validate_evidences_timestamp' => now()->addMonth(),
            'meetings_timestamp' => now()->addMonth(),
            'bonus_timestamp' => now()->addMonth(),
            'attendee_timestamp' => now()->addMonth(),
            'secret' => 'salt',
        ]);
        $committee = Committee::create(['name' => 'Logística']);
        $roles = [];
        foreach (['STUDENT', 'COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'] as $role) {
            $roles[$role] = Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]);
            $this->users[$role] = User::factory()->create();
            $this->users[$role]->roles()->attach($roles[$role]);
        }
        Coordinator::forceCreate(['user_id' => $this->users['COORDINATOR']->id, 'committee_id' => $committee->id]);
        $secretary = Secretary::forceCreate(['user_id' => $this->users['SECRETARY']->id, 'committee_id' => $committee->id]);
        $student = $this->users['STUDENT'];

        $programa = Committee::create(['name' => 'Programa']);
        $this->extra['coordinatorPrograma'] = User::factory()->create();
        $this->extra['coordinatorPrograma']->roles()->attach($roles['COORDINATOR']);
        Coordinator::forceCreate(['user_id' => $this->extra['coordinatorPrograma']->id, 'committee_id' => $programa->id]);

        $evidence = Evidence::create([
            'title' => 'Organización de mesas', 'description' => '<p>Mesas del congreso.</p>', 'hours' => 2,
            'status' => 'PENDING', 'user_id' => $student->id, 'committee_id' => $committee->id,
        ]);
        $draft = Evidence::create([
            'title' => 'Montaje', 'description' => '<p>Montaje del escenario.</p>', 'hours' => 1,
            'status' => 'DRAFT', 'user_id' => $student->id, 'committee_id' => $committee->id,
        ]);
        Storage::put('proofs/certificado.pdf', '%PDF-1.4');
        $file = \Stamp::compute_file(File::create(['name' => 'certificado', 'type' => 'pdf', 'route' => 'proofs/certificado.pdf', 'size' => 8]));
        $file->save();
        $proof = Proof::create(['evidence_id' => $evidence->id, 'file_id' => $file->id]);

        $request = MeetingRequest::forceCreate([
            'title' => 'Convocatoria de logística', 'place' => 'Aula A3.10', 'datetime' => now()->addWeek(),
            'type' => '1', 'modality' => '1', 'committee_id' => $committee->id, 'secretary_id' => $secretary->id,
        ]);
        DiaryPoint::create(['diary_id' => Diary::create(['meeting_request_id' => $request->id])->id, 'point' => 'Reparto de tareas']);
        Storage::put('/meeting_requests/meeting_request_'.$request->id.'.pdf', '%PDF-1.4');
        $sheet = SignatureSheet::create([
            'title' => 'Hoja de firmas', 'random_identifier' => '7391', 'meeting_request_id' => $request->id, 'secretary_id' => $secretary->id,
        ]);
        $sheet->users()->attach($student);

        $meeting = Meeting::forceCreate([
            'title' => 'Reunión de logística', 'datetime' => now(), 'place' => 'Aula A3.10',
            'type' => '1', 'modality' => '1', 'hours' => 2, 'committee_id' => $committee->id,
        ]);
        $meeting->users()->attach([$student->id, $secretary->user_id]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $secretary->id]);
        $point = Point::create(['meeting_minutes_id' => $minutes->id, 'title' => 'Reparto', 'duration' => 10, 'description' => 'Equipos']);
        Agreement::create(['point_id' => $point->id, 'description' => 'Cada equipo nombra un responsable.', 'identificator' => 'ISD-1']);
        Storage::put('/meeting_minutes/meeting_minutes_'.$minutes->id.'.pdf', '%PDF-1.4');

        $bonus = Bonus::create(['reason' => 'Montaje del escenario', 'hours' => 3]);
        $bonus->committee()->associate($committee)->save();
        $bonus->users()->attach($student);
        $list = DefaultList::create(['name' => 'Equipo de montaje', 'secretary_id' => $secretary->id]);
        $list->users()->attach($student);

        $event = Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);
        $attendee = Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $student->id, 'status' => 'Checked In']);
        $manualEvent = Event::forceCreate(['name' => 'Taller de Git', 'hours' => 1.5, 'start_datetime' => now()->subWeek(), 'end_datetime' => now()->subWeek()->addMinutes(90)]);

        $token = Token::create(['token' => str_repeat('a', 60), 'used' => false, 'valid_until_timestamp' => now()->addDay(), 'user_id' => $student->id]);

        $this->ids = [
            'evidence' => $evidence->id, 'draft' => $draft->id, 'proof' => $proof->id, 'student' => $student->id,
            'request' => $request->id, 'sheet' => $sheet->id, 'minutes' => $minutes->id, 'bonus' => $bonus->id,
            'list' => $list->id, 'event' => $event->id, 'attendee' => $attendee->id, 'manualEvent' => $manualEvent->id,
            'token' => $token->token, 'logistica' => $committee->id, 'programa' => $programa->id,
        ];
    }
}
