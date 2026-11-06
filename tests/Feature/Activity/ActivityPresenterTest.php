<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MessageThread;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use App\Services\Activity\ActivityPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ActivityPresenterTest extends TestCase
{
    use RefreshDatabase;

    private ActivityPresenter $presenter;

    protected function setUp(): void
    {
        parent::setUp();
        // las fichas llegan con sus rutas en otro cambio; aquí basta con que existan
        foreach (['activity.evidence' => 'evidence', 'activity.minutes' => 'minutes', 'activity.request' => 'request'] as $name => $uri) {
            if (! Route::has($name)) {
                Route::get('/_test/activity/'.$uri.'/{id}', fn () => 'ok')->name($name);
            }
        }
        Route::getRoutes()->refreshNameLookups();
        $this->presenter = app(ActivityPresenter::class);
    }

    private function entry(string $action, array $details = [], array $attributes = []): ActivityLog
    {
        return new ActivityLog(['action' => $action, 'details' => $details === [] ? null : $details, ...$attributes]);
    }

    private function userWith(string ...$roles): User
    {
        $user = User::factory()->create(['name' => 'Juan', 'surname' => 'Pérez']);
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user;
    }

    public function test_the_label_and_the_tone_of_known_and_retired_verbs(): void
    {
        $this->assertSame('Evidencia rechazada', $this->presenter->label($this->entry('evidence.rejected')));
        $this->assertSame('rejected', $this->presenter->tone($this->entry('evidence.rejected')));
        $this->assertSame('retired.verb', $this->presenter->label($this->entry('retired.verb')));
        $this->assertSame('draft', $this->presenter->tone($this->entry('retired.verb')));
    }

    public function test_the_actor_is_the_current_name_the_copied_one_or_the_system(): void
    {
        $user = $this->userWith('STUDENT');

        $this->assertSame('Juan Pérez', $this->presenter->actorName($this->entry('login', attributes: ['user_id' => $user->id, 'user_name' => 'Antiguo, Nombre'])));
        $this->assertSame('Borrado, Usuario', $this->presenter->actorName($this->entry('login', attributes: ['user_id' => 999999, 'user_name' => 'Borrado, Usuario'])));
        $this->assertSame('Sistema', $this->presenter->actorName($this->entry('notification.sent')));
    }

    public function test_the_role_line(): void
    {
        $this->assertSame('como coordinador de Logística', $this->presenter->roleLine($this->entry('evidence.accepted', attributes: ['acting_role' => 'COORDINATOR', 'committee_name' => 'Logística'])));
        $this->assertSame('como profesor', $this->presenter->roleLine($this->entry('evidence.accepted', attributes: ['acting_role' => 'LECTURE', 'committee_name' => 'Logística'])));
        $this->assertSame('como coordinador', $this->presenter->roleLine($this->entry('message.sent', attributes: ['acting_role' => 'COORDINATOR'])));
        $this->assertNull($this->presenter->roleLine($this->entry('profile.edited')));
    }

    public function test_the_summaries_of_the_verbs(): void
    {
        $cases = [
            ['login', ['via' => 'cookie', 'blocked' => true], 'Recordarme · Cuenta bloqueada'],
            ['login', ['via' => 'form', 'blocked' => false], null],
            ['login_failed', ['lockout' => true, 'context' => 'sign'], 'Bloqueo por intentos · En la firma pública'],
            ['password_reset_requested', ['mail_sent' => false], 'Correo no enviado'],
            ['password_reset_requested', ['mail_sent' => true], null],
            ['profile.edited', ['fields' => ['email', 'biography']], 'Correo · Biografía'],
            ['user.edited', ['before' => ['name' => 'Ana', 'email' => 'a@b.es'], 'after' => ['name' => 'Ana', 'email' => 'c@d.es']], 'Correo'],
            ['evidence.created', ['hours' => 1.5, 'files' => ['a.pdf', 'b.pdf']], '1,50 h · 2 pruebas'],
            ['evidence.published', ['version' => 2, 'hours' => 2, 'automatic' => true], 'Versión 2 · 2,00 h · Aceptada al enviarla'],
            ['evidence.edited', [
                'before' => ['committee_id' => 1, 'committee_name' => 'Programa', 'hours' => 1.5, 'title' => 'Mesas'],
                'after' => ['committee_id' => 2, 'committee_name' => 'Logística', 'hours' => 2, 'title' => 'Mesas'],
                'version' => 3,
            ], 'Comité Programa → Logística · Horas 1,50 → 2,00 · versión 3'],
            ['evidence.reedited', ['before' => 'REJECTED', 'after' => 'DRAFT'], 'Rechazada → borrador'],
            ['evidence.deleted', ['versions' => 3, 'files' => ['a.pdf']], '3 versiones · 1 prueba'],
            ['proof.uploaded', ['file' => 'certificado.pdf', 'size' => 8], 'certificado.pdf'],
            ['evidence.accepted', ['before' => 'PENDING'], null],
            ['evidence.rejected', ['reason' => 'Falta el certificado.'], '«Falta el certificado.»'],
            ['evidence.moved', ['before' => ['committee_name' => 'Programa'], 'after' => ['committee_name' => 'Logística'], 'status' => 'PENDING'], 'Programa → Logística'],
            ['request.created', ['datetime' => '2026-11-20 10:30:00', 'place' => 'Aula A3.10'], '20/11/2026 10:30 · Aula A3.10'],
            ['request.deleted', ['sheet_unlinked' => true], 'Hoja desvinculada'],
            ['sheet.deleted', ['signatures_count' => 12], '12 firmas'],
            ['minutes.created', ['users' => [1, 2, 3], 'hours' => 2], '3 asistentes · 2,00 h'],
            ['minutes.edited', ['users_added' => [4, 5], 'users_removed' => [1]], '+2 −1 asistentes'],
            ['minutes.deleted', ['hours' => 1, 'users' => [1]], '1,00 h · 1 asistente'],
            ['bonus.created', ['users' => [1, 2], 'hours' => 3], '2 personas · 3,00 h'],
            ['defaultlist.deleted', ['users' => [1, 2, 3, 4]], '4 personas'],
            ['attendee.created', ['status' => 'Checked In'], 'Checked In'],
            ['eventbrite.events_synced', ['created' => 2, 'updated' => 1], '2 nuevos · 1 actualizado'],
            ['eventbrite.attendees_synced', ['created' => 10, 'updated' => 0, 'unmatched' => 3], '10 nuevas · 0 actualizadas · 3 sin emparejar'],
            ['user.created', ['username' => 'juaperlop', 'roles' => ['STUDENT']], 'juaperlop'],
            ['user.roles_changed', ['before' => ['roles' => ['STUDENT']], 'after' => ['roles' => ['STUDENT', 'COORDINATOR']]], 'Estudiante → Estudiante, Coordinador'],
            ['user.deleted', ['roles' => ['STUDENT', 'SECRETARY'], 'bulk' => true], 'Estudiante, Secretario'],
            ['config.changed', ['before' => ['upload_evidences' => '2026-11-20 20:00:00'], 'after' => ['upload_evidences' => '2026-11-27 20:00:00']], 'Subida 20/11 20:00 → 27/11 20:00'],
            ['config.changed', ['before' => ['bonus_timestamp' => null], 'after' => ['bonus_timestamp' => '2026-12-01 20:00:00']], 'Bonos sin fecha → 01/12 20:00'],
            ['import.users', ['created' => 25], '25 usuarios nuevos'],
            ['export.evidences', ['sections' => ['Evidencias', 'Reuniones']], 'Evidencias, Reuniones'],
            ['export.activity', ['rows' => 1], '1 fila'],
            ['export.record', ['rows' => 12], '12 filas'],
            ['committee.exported', ['rows' => 3], '3 filas'],
            ['randomize.run', ['count' => 4], '4 evidencias'],
            ['notification.sent', ['title' => 'Evidencia rechazada', 'channels' => ['database', 'mail']], 'Evidencia rechazada · bandeja y correo'],
            ['notification.sent', ['channels' => ['database']], 'bandeja'],
            ['notification.failed', ['channel' => 'mail', 'error' => 'SMTP'], 'Correo no enviado'],
            ['self_review.blocked', ['attempted' => 'evidence.accepted'], 'Intento de evidencia aceptada'],
            ['committee.edited', ['before' => ['name' => 'Logistica'], 'after' => ['name' => 'Logística']], 'Nombre Logistica → Logística'],
            ['activity.pruned', ['failed_logins' => 2, 'entries' => 3, 'network_cleared' => 4, 'read_notifications' => 5, 'orphan_notifications' => 1, 'reminders' => 0], '5 anotaciones borradas · 6 avisos borrados · 4 anotaciones sin datos de red'],
            ['logout', [], null],
            ['retired.verb', ['anything' => 1], null],
        ];

        foreach ($cases as [$action, $details, $summary]) {
            $this->assertSame($summary, $this->presenter->summary($this->entry($action, $details)), $action.' '.json_encode($details));
        }
    }

    public function test_the_version_can_be_left_out_of_the_summary(): void
    {
        $entry = $this->entry('evidence.published', ['version' => 2, 'hours' => 2]);

        $this->assertSame(2, $this->presenter->version($entry));
        $this->assertSame('2,00 h', $this->presenter->summary($entry, withVersion: false));
        $this->assertNull($this->presenter->version($this->entry('evidence.created')));
    }

    public function test_the_label_of_the_signals(): void
    {
        $this->assertSame('Autorrevisión · Fuera de plazo', $this->presenter->flagsLabel($this->entry('evidence.accepted', attributes: ['flags' => ['self_review', 'after_deadline']])));
        $this->assertNull($this->presenter->flagsLabel($this->entry('evidence.accepted')));
    }

    public function test_the_link_to_the_subject_only_when_it_exists_and_the_viewer_can_open_it(): void
    {
        $committee = Committee::create(['name' => 'Logística']);
        $author = $this->userWith('STUDENT');
        $coordinator = $this->userWith('STUDENT', 'COORDINATOR');
        Coordinator::forceCreate(['user_id' => $coordinator->id, 'committee_id' => $committee->id]);
        $lecturer = $this->userWith('LECTURE');
        $old = Evidence::create(['title' => 'v1', 'status' => 'REJECTED', 'user_id' => $author->id, 'committee_id' => $committee->id, 'last' => false]);
        $head = Evidence::create(['title' => 'v2', 'status' => 'PENDING', 'user_id' => $author->id, 'committee_id' => $committee->id, 'points_to' => $old->id]);
        $draft = Evidence::create(['title' => 'Borrador', 'status' => 'DRAFT', 'user_id' => $author->id, 'committee_id' => $committee->id]);
        $meeting = Meeting::forceCreate(['title' => 'Reunión', 'datetime' => now(), 'hours' => 1, 'committee_id' => $committee->id]);
        $secretary = Secretary::forceCreate(['user_id' => $this->userWith('SECRETARY')->id, 'committee_id' => $committee->id]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $secretary->id]);
        $about = fn (string $type, int $id): ActivityLog => $this->entry('evidence.created', attributes: ['subject_type' => $type, 'subject_id' => $id]);

        $this->assertSame(route('activity.evidence', ['id' => $head->id]), $this->presenter->subjectUrl($about('evidence', $old->id), $author));
        $this->assertSame(route('activity.evidence', ['id' => $head->id]), $this->presenter->subjectUrl($about('evidence', $old->id), $coordinator->fresh()));
        $this->assertSame(route('activity.evidence', ['id' => $draft->id]), $this->presenter->subjectUrl($about('evidence', $draft->id), $author));
        $this->assertNull($this->presenter->subjectUrl($about('evidence', $draft->id), $coordinator->fresh()));
        $this->assertNull($this->presenter->subjectUrl($about('evidence', 999999), $author));
        $this->assertSame(route('activity.minutes', ['id' => $minutes->id]), $this->presenter->subjectUrl($about('meeting_minutes', $minutes->id), $author));
        $this->assertNull($this->presenter->subjectUrl($about('meeting_minutes', 999999), $author));
        $this->assertSame(route('profiles.view', ['id' => $author->id]), $this->presenter->subjectUrl($about('user', $author->id), $lecturer));
        $this->assertNull($this->presenter->subjectUrl($about('user', $author->id), $coordinator));
        $this->assertNull($this->presenter->subjectUrl($about('bonus', 1), $lecturer));
        $this->assertNull($this->presenter->subjectUrl($this->entry('import.users'), $lecturer));
    }

    public function test_a_message_names_its_audience_and_links_its_thread_to_who_can_see_it(): void
    {
        if (! Route::has('messages.show')) {
            Route::get('/_test/messages/{thread}', fn () => 'ok')->name('messages.show');
            Route::getRoutes()->refreshNameLookups();
        }
        $committee = Committee::create(['name' => 'Logística']);
        $author = $this->userWith('STUDENT');
        $stranger = $this->userWith('STUDENT');
        $lecturer = $this->userWith('LECTURE');
        $thread = MessageThread::create([
            'subject' => 'Duda', 'author_id' => $author->id, 'author_name' => 'Pérez, Juan', 'author_role' => 'STUDENT',
            'recipient_role' => 'COORDINATOR', 'committee_id' => $committee->id, 'last_message_at' => now(),
        ]);
        $sent = $this->entry('message.sent', ['audience' => 'COORDINATOR:'.$committee->id], ['subject_type' => 'message_thread', 'subject_id' => $thread->id]);

        $this->assertSame('Para Coordinación de Logística', $this->presenter->summary($sent));
        $this->assertSame('Para Profesorado', $this->presenter->summary($this->entry('message.sent', ['audience' => 'LECTURE'])));
        $this->assertSame('Para Delegación', $this->presenter->summary($this->entry('message.sent', ['audience' => 'Delegación'])));
        $this->assertSame(route('messages.show', ['thread' => $thread->id]), $this->presenter->subjectUrl($sent, $author));
        $this->assertSame(route('messages.show', ['thread' => $thread->id]), $this->presenter->subjectUrl($sent, $lecturer));
        $this->assertNull($this->presenter->subjectUrl($sent, $stranger));
    }
}
