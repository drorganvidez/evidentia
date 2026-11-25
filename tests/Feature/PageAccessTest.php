<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Attendee;
use App\Models\Bonus;
use App\Models\Certificate;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\DefaultList;
use App\Models\Diary;
use App\Models\DiaryPoint;
use App\Models\ErrorReport;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Point;
use App\Models\Proof;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Opens every page of the app as each role and as a guest. The roles listed for a page must get it;
 * everybody else must be turned away (redirect or not found), and nobody may ever get a server error.
 */
class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['STUDENT', 'COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'];

    private const EVERYONE = self::ROLES;

    private const GUEST = 'GUEST';

    private const MANAGEMENT = ['PRESIDENT', 'LECTURE'];

    private const WITH_EVIDENCES = ['STUDENT', 'COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR', 'PRESIDENT'];

    /** Pages that call external services (GitHub, Eventbrite); the Eventbrite ones are tested with a fake client. */
    private const NETWORK = ['updates.list', 'registercoordinator.event.load', 'registercoordinator.attendee.load'];

    /** @var array<string, User> */
    private array $users = [];

    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();
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
        foreach (self::ROLES as $role) {
            $this->users[$role] = User::factory()->create();
            $this->users[$role]->roles()->attach(Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }
        Coordinator::forceCreate(['user_id' => $this->users['COORDINATOR']->id, 'committee_id' => $committee->id]);
        $secretary = Secretary::forceCreate(['user_id' => $this->users['SECRETARY']->id, 'committee_id' => $committee->id]);
        $student = $this->users['STUDENT'];

        $evidence = Evidence::create([
            'title' => 'Organización de mesas', 'description' => '<p>Mesas del congreso.</p>', 'hours' => 2,
            'status' => 'PENDING', 'user_id' => $student->id, 'committee_id' => $committee->id,
        ]);
        $draft = Evidence::create([
            'title' => 'Montaje', 'description' => '<p>Montaje del escenario.</p>', 'hours' => 1,
            'status' => 'DRAFT', 'user_id' => $student->id, 'committee_id' => $committee->id,
        ]);
        // the presidency reviews what a coordinator sends to their own committee
        $delegated = Evidence::create([
            'title' => 'Inventario', 'description' => '<p>Inventario del almacén.</p>', 'hours' => 1,
            'status' => 'PENDING', 'user_id' => $this->users['COORDINATOR']->id, 'committee_id' => $committee->id,
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
        $errorReport = ErrorReport::create([
            'fingerprint' => sha1('demo'), 'exception' => \RuntimeException::class, 'message' => 'Fallo de prueba',
            'occurrences' => 1, 'first_seen_at' => now(), 'last_seen_at' => now(),
        ]);
        $manualEvent = Event::forceCreate(['name' => 'Taller de Git', 'hours' => 1.5, 'start_datetime' => now()->subWeek(), 'end_datetime' => now()->subWeek()->addMinutes(90)]);

        $diploma = Certificate::forceCreate([
            'type' => Certificate::DIPLOMA, 'user_id' => $student->id, 'recipient_name' => 'Ana Ruiz', 'title' => 'impartir una charla',
            'issued_at' => now(),
        ]);

        $token = Token::create(['token' => str_repeat('a', 60), 'used' => false, 'valid_until_timestamp' => now()->addDay(), 'user_id' => $student->id]);

        $thread = MessageThread::create([
            'subject' => 'Duda sobre las mesas', 'author_id' => $student->id, 'author_name' => $student->name, 'author_role' => 'STUDENT',
            'recipient_role' => 'COORDINATOR', 'committee_id' => $committee->id, 'state' => 'open', 'last_message_at' => now(),
            'last_message_by' => $student->id,
        ]);
        Message::create(['message_thread_id' => $thread->id, 'user_id' => $student->id, 'user_name' => $student->name, 'role' => 'STUDENT', 'body' => '¿Cuento el montaje?']);

        $this->ids = [
            'evidence' => $evidence->id, 'draft' => $draft->id, 'delegated' => $delegated->id, 'proof' => $proof->id, 'student' => $student->id,
            'request' => $request->id, 'committee' => $committee->id, 'sheet' => $sheet->id, 'minutes' => $minutes->id, 'bonus' => $bonus->id,
            'list' => $list->id, 'attendee' => $attendee->id, 'manualEvent' => $manualEvent->id, 'errorReport' => $errorReport->id, 'token' => $token->token,
            'thread' => $thread->id, 'event' => $event->id, 'diploma' => $diploma->code,
        ];
    }

    /** route name => [parameters (resolved in the test), roles that open it] */
    public static function pages(): array
    {
        return [
            'home' => [[], self::EVERYONE],
            'login' => [[], [self::GUEST]],
            'sso.redirect' => [[], []],
            'sso.callback' => [[], []],
            'sso.mock' => [[], []],
            'block' => [[], []],
            'profile.view' => [[], self::EVERYONE],
            'password.custom_reset' => [[], [self::GUEST]],
            'password.custom_update' => [['token' => 'token'], [self::GUEST]],
            'sign' => [['random_identifier' => '7391'], [self::GUEST, ...self::EVERYONE]],
            'sign.finish' => [[], []],
            'meeting.list' => [[], self::WITH_EVIDENCES],
            'attendee.list' => [[], ['STUDENT']],
            'download.request' => [['id' => 'request'], self::EVERYONE],
            'download.minutes' => [['id' => 'minutes'], self::EVERYONE],
            'notifications.index' => [[], self::EVERYONE],
            'notifications.preferences' => [[], self::EVERYONE],
            // nobody owns this notice: everybody gets a 404
            'notifications.open' => [['id' => '9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d'], []],

            'evidence.list' => [[], self::WITH_EVIDENCES],
            'evidence.archive' => [[], self::WITH_EVIDENCES],
            'evidence.create' => [[], self::WITH_EVIDENCES],
            'evidence.view' => [['id' => 'evidence'], ['STUDENT']],
            'evidence.edit' => [['id' => 'draft'], ['STUDENT']],
            'proof.download' => [['id' => 'proof'], ['STUDENT', 'COORDINATOR', 'PRESIDENT', 'LECTURE']],
            'proof.view' => [['id' => 'proof'], ['STUDENT', 'COORDINATOR', 'PRESIDENT', 'LECTURE']],
            'upload.load' => [['file_name' => 'missing.pdf'], []],
            'upload.remove' => [['file_name' => 'missing.pdf'], self::EVERYONE],
            'xls.upload.remove' => [['file_name' => 'missing.xls'], self::EVERYONE],

            'coordinator.committee' => [[], ['COORDINATOR']],
            'coordinator.committee.export' => [[], ['COORDINATOR']],
            'coordinator.evidence.list.all' => [[], ['COORDINATOR']],
            'coordinator.evidence.list.pending' => [[], ['COORDINATOR']],
            'coordinator.evidence.list.accepted' => [[], ['COORDINATOR']],
            'coordinator.evidence.list.rejected' => [[], ['COORDINATOR']],
            'coordinator.evidence.view' => [['id' => 'evidence'], ['COORDINATOR']],

            'secretary.meeting.manage' => [[], ['SECRETARY']],
            'secretary.meeting.manage.request.list' => [[], ['SECRETARY']],
            'secretary.meeting.manage.request.create' => [[], ['SECRETARY']],
            'secretary.meeting.manage.request.edit' => [['id' => 'request'], ['SECRETARY']],
            'secretary.meeting.manage.request.download' => [['id' => 'request'], ['SECRETARY']],
            'secretary.meeting.manage.signaturesheet.list' => [[], ['SECRETARY']],
            'secretary.meeting.manage.signaturesheet.create' => [[], ['SECRETARY']],
            'secretary.meeting.manage.signaturesheet.edit' => [['id' => 'sheet'], ['SECRETARY']],
            'secretary.meeting.manage.signaturesheet.view' => [['id' => 'sheet'], ['SECRETARY']],
            'secretary.meeting.manage.minutes.list' => [[], ['SECRETARY']],
            'secretary.meeting.manage.minutes.create' => [[], []], // redirects to step 1
            'secretary.meeting.manage.minutes.create.step1' => [[], ['SECRETARY']],
            'secretary.meeting.manage.minutes.create.step2' => [[], ['SECRETARY']],
            'secretary.meeting.manage.minutes.create.step3' => [[], ['SECRETARY']],
            'secretary.meeting.manage.minutes.edit' => [['id' => 'minutes'], ['SECRETARY']],
            'secretary.meeting.defaultlist' => [['id' => 'list'], ['SECRETARY']],
            'secretary.defaultlist.list' => [[], ['SECRETARY']],
            'secretary.defaultlist.create' => [[], ['SECRETARY']],
            'secretary.defaultlist.edit' => [['id' => 'list'], ['SECRETARY']],
            'secretary.bonus.list' => [[], ['SECRETARY']],
            'secretary.bonus.create' => [[], ['SECRETARY']],
            'secretary.bonus.edit' => [['id' => 'bonus'], ['SECRETARY']],
            'gp.users.all' => [[], ['SECRETARY']],

            'registercoordinator.token' => [[], ['REGISTER_COORDINATOR']],
            'registercoordinator.event.list' => [[], ['REGISTER_COORDINATOR']],
            'registercoordinator.attendee.list' => [[], ['REGISTER_COORDINATOR']],
            'registercoordinator.attendee.export' => [[], ['REGISTER_COORDINATOR']],
            'attendee.create' => [[], ['REGISTER_COORDINATOR']],
            'attendee.import' => [[], ['REGISTER_COORDINATOR']],
            'registercoordinator.event.groups' => [[], ['REGISTER_COORDINATOR']],
            'attendee.edit' => [['id' => 'attendee'], ['REGISTER_COORDINATOR']],
            'meeting.search' => [[], self::EVERYONE],
            'certificate.attendance' => [['id' => 'event'], ['STUDENT']],
            'certificate.download' => [['code' => 'diploma'], ['STUDENT', ...self::MANAGEMENT]],
            'certificate.verify' => [['code' => 'diploma'], [self::GUEST, ...self::EVERYONE]],
            'certificate.index' => [[], self::MANAGEMENT],
            'certificate.create' => [[], self::MANAGEMENT],
            'registercoordinator.attendee.load.student' => [[], ['REGISTER_COORDINATOR']],
            'lecture.errors' => [[], self::MANAGEMENT],
            'lecture.errors.show' => [['id' => 'errorReport'], self::MANAGEMENT],
            'president.errors' => [[], self::MANAGEMENT],
            'president.errors.show' => [['id' => 'errorReport'], self::MANAGEMENT],
            'registercoordinator.event.create' => [[], ['REGISTER_COORDINATOR']],
            'registercoordinator.event.edit' => [['id' => 'manualEvent'], ['REGISTER_COORDINATOR']],

            // the queue of the presidency (App\Services\ReviewQueue); the lecturer may review it too
            'president.review.all' => [[], self::MANAGEMENT],
            'president.review.pending' => [[], self::MANAGEMENT],
            'president.review.accepted' => [[], self::MANAGEMENT],
            'president.review.rejected' => [[], self::MANAGEMENT],
            'president.review.view' => [['id' => 'delegated'], self::MANAGEMENT],

            'president.user.list' => [[], self::MANAGEMENT],
            'president.user.management' => [['id' => 'student'], self::MANAGEMENT],
            'president.committee.list' => [[], self::MANAGEMENT],
            'president.committee.view' => [['id' => 'committee'], self::MANAGEMENT],
            'president.config' => [[], self::MANAGEMENT],
            'president.evidence.list' => [[], self::MANAGEMENT],
            'president.meeting.list' => [[], self::MANAGEMENT],
            'president.attendee.list' => [[], self::MANAGEMENT],
            'president.bonus.list' => [[], self::MANAGEMENT],
            'president.meeting.request.list' => [[], self::MANAGEMENT],
            'president.meeting.signaturesheet.list' => [[], self::MANAGEMENT],
            'president.meeting.defaultlist.list' => [[], self::MANAGEMENT],
            'president.export' => [[], self::MANAGEMENT],
            'president.export.archive' => [[], self::MANAGEMENT],
            'lecture.user.list' => [[], self::MANAGEMENT],
            'lecture.user.management' => [['id' => 'student'], self::MANAGEMENT],
            'lecture.committee.list' => [[], self::MANAGEMENT],
            'lecture.committee.view' => [['id' => 'committee'], self::MANAGEMENT],
            'lecture.config' => [[], self::MANAGEMENT],
            'lecture.evidence.list' => [[], self::MANAGEMENT],
            'lecture.meeting.list' => [[], self::MANAGEMENT],
            'lecture.attendee.list' => [[], self::MANAGEMENT],
            'lecture.bonus.list' => [[], self::MANAGEMENT],
            'lecture.meeting.request.list' => [[], self::MANAGEMENT],
            'lecture.meeting.signaturesheet.list' => [[], self::MANAGEMENT],
            'lecture.meeting.defaultlist.list' => [[], self::MANAGEMENT],
            'lecture.export' => [[], self::MANAGEMENT],
            'lecture.export.archive' => [[], self::MANAGEMENT],
            'lecture.import' => [[], ['LECTURE']],
            'lecture.integrity' => [[], self::MANAGEMENT],
            'president.integrity' => [[], self::MANAGEMENT],
            'randomize.randomize' => [[], ['LECTURE']],
            'profiles.view' => [['id' => 'student'], self::MANAGEMENT],
            'profiles.export' => [['id' => 'student'], self::MANAGEMENT],
            'profiles.view.evidence' => [['id_user' => 'student', 'id_evidence' => 'evidence'], self::MANAGEMENT],

            // Activity log and histories (routes/activity.php)
            'activity.index' => [[], ['COORDINATOR', 'SECRETARY', ...self::MANAGEMENT]],
            'activity.export' => [[], self::MANAGEMENT],
            'activity.mine' => [[], self::EVERYONE],
            'activity.evidence' => [['id' => 'evidence'], ['STUDENT', 'COORDINATOR', 'SECRETARY', ...self::MANAGEMENT]],
            'activity.minutes' => [['id' => 'minutes'], self::EVERYONE],
            'activity.request' => [['id' => 'request'], self::EVERYONE],

            // Messages (routes/messages.php)
            'messages.inbox' => [[], ['COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR', ...self::MANAGEMENT]],
            'messages.mine' => [[], self::EVERYONE],
            'messages.create' => [[], self::EVERYONE],
            'messages.show' => [['thread' => 'thread'], ['STUDENT', 'COORDINATOR', 'PRESIDENT', 'LECTURE']],
        ];
    }

    public function test_every_page_of_the_app_is_covered(): void
    {
        $pages = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => in_array('GET', $route->methods()) && str_starts_with(ltrim($route->getActionName(), '\\'), 'App\\'))
            ->map(fn (Route $route) => $route->getName())
            ->reject(fn ($name) => in_array($name, self::NETWORK))
            ->sort()->values()->all();

        $listed = array_keys(self::pages());
        $this->assertSame([], array_values(array_diff($pages, $listed)), 'Pages missing from this test');
        $this->assertSame([], array_values(array_diff($listed, $pages)), 'Pages listed here that no longer exist');
    }

    #[DataProvider('pages')]
    public function test_only_its_roles_open_each_page(array $parameters, array $roles): void
    {
        $name = $this->dataName();
        $url = route($name, array_map(fn ($key) => $this->ids[$key] ?? $key, $parameters));

        foreach ([null, ...self::ROLES] as $role) {
            $role ? $this->actingAs($this->users[$role]) : auth()->logout();
            $level = ob_get_level();
            ob_start(); // some downloads close the active output buffer
            $status = $this->get($url)->getStatusCode();
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            $who = $role ?? self::GUEST;
            $this->assertLessThan(500, $status, "$name crashed for $who");
            if (in_array($who, $roles, true)) {
                $this->assertSame(200, $status, "$name should open for $who");
            } else {
                $this->assertContains($status, [302, 403, 404], "$name should be closed to $who");
            }
        }
    }
}
