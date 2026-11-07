<?php

namespace Tests\Feature\Activity;

use App\Models\Attendee;
use App\Models\Bonus;
use App\Models\Certificate;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\DefaultList;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\MessageThread;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;
use App\Services\Activity\SubjectInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_title_and_the_committee_of_each_subject(): void
    {
        $committee = Committee::create(['name' => 'Logística']);
        $student = User::factory()->create(['name' => 'Juan', 'surname' => 'Pérez']);
        $secretary = Secretary::forceCreate(['user_id' => User::factory()->create()->id, 'committee_id' => $committee->id]);
        $evidence = Evidence::create(['title' => 'Mesas', 'hours' => 2, 'status' => 'PENDING', 'user_id' => $student->id, 'committee_id' => $committee->id]);
        $draft = Evidence::create(['title' => 'Montaje', 'hours' => 1, 'status' => 'DRAFT', 'user_id' => $student->id, 'committee_id' => $committee->id]);
        $request = MeetingRequest::forceCreate(['title' => 'Convocatoria', 'committee_id' => $committee->id, 'secretary_id' => $secretary->id]);
        $sheet = SignatureSheet::create(['title' => 'Hoja', 'random_identifier' => '7391', 'meeting_request_id' => $request->id, 'secretary_id' => $secretary->id]);
        $looseSheet = SignatureSheet::create(['title' => 'Hoja suelta', 'random_identifier' => '7392', 'secretary_id' => $secretary->id]);
        $meeting = Meeting::forceCreate(['title' => 'Reunión', 'datetime' => now(), 'hours' => 1, 'committee_id' => $committee->id]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $secretary->id]);
        $bonus = Bonus::create(['reason' => 'Montaje del escenario', 'hours' => 3]);
        $bonus->committee()->associate($committee)->save();
        $list = DefaultList::create(['name' => 'Equipo', 'secretary_id' => $secretary->id]);
        $event = Event::forceCreate(['name' => 'Charla IA', 'hours' => 2]);
        $attendee = Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $student->id, 'status' => 'Checked In']);
        $configuration = Configuration::forceCreate([]);
        $thread = MessageThread::create([
            'subject' => 'Duda', 'author_id' => $student->id, 'author_name' => 'Pérez, Juan', 'author_role' => 'STUDENT',
            'recipient_role' => 'COORDINATOR', 'committee_id' => $committee->id, 'last_message_at' => now(),
        ]);

        $certificate = Certificate::create(['type' => Certificate::DIPLOMA, 'recipient_name' => 'Ana Ruiz', 'title' => 'Ponente']);

        $expected = [
            [$evidence, 'Mesas', $committee->id],
            [$draft, 'Montaje', null],
            [$request, 'Convocatoria', $committee->id],
            [$sheet, 'Hoja', $committee->id],
            [$looseSheet, 'Hoja suelta', $committee->id],
            [$minutes, 'Reunión', $committee->id],
            [$meeting, 'Reunión', $committee->id],
            [$bonus, 'Montaje del escenario', $committee->id],
            [$list, 'Equipo', $committee->id],
            [$committee, 'Logística', $committee->id],
            [$student, 'Pérez, Juan', null],
            [$event, 'Charla IA', null],
            [$attendee, 'Charla IA · Pérez, Juan', null],
            [$configuration, 'Configuración del curso', null],
            [$thread, 'Duda', null],
            [$certificate, 'Diploma · Ana Ruiz', null],
        ];

        foreach ($expected as [$subject, $title, $committeeId]) {
            $this->assertSame($title, SubjectInfo::title($subject), $subject::class);
            $this->assertSame($committeeId, SubjectInfo::committeeId($subject), $subject::class);
            $this->assertSame($subject->getKey(), SubjectInfo::rootId($subject), $subject::class);
        }
        $this->assertCount(count(SubjectInfo::supported()), array_unique(array_map(fn (array $row): string => $row[0]::class, $expected)));
    }

    public function test_every_version_of_an_evidence_shares_the_root(): void
    {
        $student = User::factory()->create();
        $first = Evidence::create(['title' => 'v1', 'status' => 'REJECTED', 'user_id' => $student->id, 'last' => false]);
        $second = Evidence::create(['title' => 'v2', 'status' => 'REJECTED', 'user_id' => $student->id, 'points_to' => $first->id, 'last' => false]);
        $third = Evidence::create(['title' => 'v3', 'status' => 'PENDING', 'user_id' => $student->id, 'points_to' => $second->id]);

        $this->assertSame($first->id, SubjectInfo::rootId($third));
        $this->assertSame($first->id, SubjectInfo::rootId($second));
        $this->assertSame($first->id, SubjectInfo::rootId($first));
    }

    public function test_an_unsaved_subject_has_no_root(): void
    {
        $this->assertNull(SubjectInfo::rootId(new Attendee(['event_id' => 1, 'user_id' => 1])));
    }

    public function test_the_name_of_a_person(): void
    {
        $this->assertSame('Pérez López, Juan', SubjectInfo::personName(new User(['name' => ' Juan ', 'surname' => 'Pérez López'])));
        $this->assertSame('Juan', SubjectInfo::personName(new User(['name' => 'Juan', 'surname' => ''])));
        $this->assertSame('Juan', SubjectInfo::personName(new User(['name' => 'Juan'])));
    }

    public function test_the_details_lose_their_secrets_at_any_depth(): void
    {
        $clean = SubjectInfo::sanitize([
            'Password' => 'x', 'current_password' => 'x', 'credentials' => ['email' => 'a', 'password' => 'b'], 'secret' => 'x',
            'after' => ['token' => 'x', 'name' => 'Ana', 'list' => [['remember_token' => 'x', 'id' => 3]]],
            'reason' => str_repeat('é', 2500), 'hours' => 1.5, 'automatic' => true, 'users' => [1, 2],
        ]);

        $this->assertSame(['after', 'reason', 'hours', 'automatic', 'users'], array_keys($clean));
        $this->assertSame(['name' => 'Ana', 'list' => [['id' => 3]]], $clean['after']);
        $this->assertSame(2000, mb_strlen($clean['reason']));
        $this->assertSame(1.5, $clean['hours']);
        $this->assertSame([1, 2], $clean['users']);
    }
}
