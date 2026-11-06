<?php

namespace Tests\Feature\Messages;

use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The scopes of the threads never let someone see a thread that is not theirs, not even through the search.
 */
class MessageThreadScopesTest extends TestCase
{
    use RefreshDatabase;

    private Committee $logistica;

    private Committee $programa;

    /** @var array<string, User> */
    private array $u = [];

    /** @var array<string, MessageThread> */
    private array $t = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->logistica = Committee::create(['name' => 'Logística']);
        $this->programa = Committee::create(['name' => 'Programa']);
        $this->u = [
            'juan' => $this->userWith('Juan', 'Pérez', 'STUDENT'),
            'luis' => $this->userWith('Luis', 'Romero', 'STUDENT'),
            'coordinator' => $this->userWith('Carlos', 'Navarro', 'STUDENT', 'COORDINATOR'),
            'secretary' => $this->userWith('Sara', 'Díaz', 'STUDENT', 'SECRETARY'),
            'coordinatorPrograma' => $this->userWith('Lucía', 'Torres', 'STUDENT', 'COORDINATOR'),
            'president' => $this->userWith('Ana', 'Gómez', 'STUDENT', 'PRESIDENT'),
            'lecturer' => $this->userWith('Pablo', 'Moreno', 'LECTURE'),
        ];
        Coordinator::forceCreate(['user_id' => $this->u['coordinator']->id, 'committee_id' => $this->logistica->id]);
        Coordinator::forceCreate(['user_id' => $this->u['coordinatorPrograma']->id, 'committee_id' => $this->programa->id]);
        Secretary::forceCreate(['user_id' => $this->u['secretary']->id, 'committee_id' => $this->logistica->id]);

        $this->t = [
            'juanToLogistica' => $this->thread('juan', 'COORDINATOR', $this->logistica->id, 'Duda sobre mis horas', ['juan', 'coordinator']),
            'luisToPrograma' => $this->thread('luis', 'COORDINATOR', $this->programa->id, 'Comité equivocado', ['luis']),
            'luisToPresidency' => $this->thread('luis', 'PRESIDENT', null, 'Propuesta 100% nueva', ['luis']),
            'juanToLecturers' => $this->thread('juan', 'LECTURE', null, 'Sugerencia', ['juan'], 'resolved'),
            'coordinatorToSecretary' => $this->thread('coordinator', 'SECRETARY', $this->logistica->id, 'Acta pendiente', ['coordinator']),
        ];
    }

    private function userWith(string $name, string $surname, string ...$roles): User
    {
        $user = User::factory()->create(['name' => $name, 'surname' => $surname]);
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user;
    }

    /** @param  list<string>  $writers */
    private function thread(string $author, string $role, ?int $committeeId, string $subject, array $writers, string $state = 'open'): MessageThread
    {
        $thread = MessageThread::create([
            'subject' => $subject, 'author_id' => $this->u[$author]->id,
            'author_name' => $this->u[$author]->surname.', '.$this->u[$author]->name, 'author_role' => 'STUDENT',
            'recipient_role' => $role, 'committee_id' => $committeeId, 'state' => $state,
            'last_message_at' => now()->subHour(), 'last_message_by' => $this->u[end($writers)]->id,
        ]);
        foreach ($writers as $i => $writer) {
            Message::create([
                'message_thread_id' => $thread->id, 'user_id' => $this->u[$writer]->id, 'user_name' => $this->u[$writer]->name,
                'body' => 'Mensaje '.$i,
            ]);
        }

        return $thread;
    }

    private function names($query): array
    {
        $ids = $query->orderBy('id')->pluck('id')->all();

        return array_values(array_map(fn (int $id): string => array_search($id, array_map(fn ($t) => $t->id, $this->t), true), $ids));
    }

    private function read(string $thread, string $who, $at): void
    {
        DB::table('message_thread_reads')->updateOrInsert(
            ['message_thread_id' => $this->t[$thread]->id, 'user_id' => $this->u[$who]->id],
            ['read_at' => $at],
        );
    }

    public function test_the_threads_addressed_to_each_person(): void
    {
        $this->assertSame(['juanToLogistica'], $this->names(MessageThread::addressedTo($this->u['coordinator'])));
        $this->assertSame(['coordinatorToSecretary'], $this->names(MessageThread::addressedTo($this->u['secretary'])));
        $this->assertSame(['luisToPrograma'], $this->names(MessageThread::addressedTo($this->u['coordinatorPrograma'])));
        $this->assertSame(['luisToPresidency'], $this->names(MessageThread::addressedTo($this->u['president'])));
        $this->assertSame(['luisToPresidency', 'juanToLecturers'], $this->names(MessageThread::addressedTo($this->u['lecturer'])));
        $this->assertSame([], $this->names(MessageThread::addressedTo($this->u['juan'])));
    }

    public function test_the_inbox_has_the_threads_of_others_addressed_to_one_or_written_in(): void
    {
        $this->assertSame(['juanToLogistica'], $this->names(MessageThread::inbox($this->u['coordinator'])));
        $this->assertSame([], $this->names(MessageThread::inbox($this->u['juan'])));

        Message::create(['message_thread_id' => $this->t['luisToPrograma']->id, 'user_id' => $this->u['lecturer']->id, 'user_name' => 'Pablo', 'body' => 'Lo miro']);
        $this->assertSame(['luisToPrograma', 'luisToPresidency', 'juanToLecturers'], $this->names(MessageThread::inbox($this->u['lecturer'])));
    }

    public function test_the_lecturers_see_every_thread_and_the_rest_only_theirs(): void
    {
        $this->assertCount(5, MessageThread::visibleTo($this->u['lecturer'])->get());
        $this->assertSame(['juanToLogistica', 'juanToLecturers'], $this->names(MessageThread::visibleTo($this->u['juan'])));
        $this->assertSame(['luisToPrograma', 'luisToPresidency'], $this->names(MessageThread::visibleTo($this->u['luis'])));
        $this->assertSame(['juanToLogistica', 'coordinatorToSecretary'], $this->names(MessageThread::visibleTo($this->u['coordinator'])));
        $this->assertSame(['luisToPresidency'], $this->names(MessageThread::visibleTo($this->u['president'])));
    }

    public function test_the_search_never_widens_what_one_sees(): void
    {
        // el coordinador de Logística busca a Luis, que solo escribe a Programa y a presidencia
        $this->assertSame([], $this->names(MessageThread::visibleTo($this->u['coordinator'])->search('Romero')));
        $this->assertSame([], $this->names(MessageThread::inbox($this->u['coordinator'])->search('Romero')));
        $this->assertSame(['luisToPrograma', 'luisToPresidency'], $this->names(MessageThread::visibleTo($this->u['lecturer'])->search('Romero')));
        $this->assertSame(['juanToLogistica'], $this->names(MessageThread::visibleTo($this->u['coordinator'])->search('horas')));
    }

    public function test_the_wildcards_of_the_search_are_plain_text(): void
    {
        $this->assertSame(['luisToPresidency'], $this->names(MessageThread::search('100%')));
        $this->assertSame([], $this->names(MessageThread::search('%_')));
        $this->assertCount(5, MessageThread::search('')->get());
    }

    public function test_unread_means_someone_else_wrote_last_and_one_has_not_read_since(): void
    {
        // la última respuesta del hilo de Juan es del coordinador
        $this->assertSame(['juanToLogistica'], $this->names(MessageThread::where('author_id', $this->u['juan']->id)->unreadFor($this->u['juan'])));
        $this->assertTrue($this->t['juanToLogistica']->isUnreadFor($this->u['juan']));
        $this->assertFalse($this->t['juanToLogistica']->isUnreadFor($this->u['coordinator']));
        $this->assertSame(1, MessageThread::unreadMineCount($this->u['juan']));

        $this->read('juanToLogistica', 'juan', now());
        $this->assertFalse($this->t['juanToLogistica']->fresh()->isUnreadFor($this->u['juan']));
        $this->assertSame(0, MessageThread::unreadMineCount($this->u['juan']));

        $this->read('juanToLogistica', 'juan', now()->subDay());
        $this->assertTrue($this->t['juanToLogistica']->fresh()->isUnreadFor($this->u['juan']));
        $this->assertSame(1, MessageThread::unreadMineCount($this->u['juan']));
    }

    public function test_the_unread_inbox_of_a_coordinator(): void
    {
        // Luis escribió el último en su hilo a Programa y nadie lo ha leído
        $this->assertSame(1, MessageThread::unreadInboxCount($this->u['coordinatorPrograma']));
        $this->read('luisToPrograma', 'coordinatorPrograma', now());
        $this->assertSame(0, MessageThread::unreadInboxCount($this->u['coordinatorPrograma']));
        // la respuesta del propio coordinador no cuenta como sin leer
        $this->assertSame(0, MessageThread::unreadInboxCount($this->u['coordinator']));
    }

    public function test_open_and_resolved(): void
    {
        $this->assertSame(['juanToLecturers'], $this->names(MessageThread::resolved()));
        $this->assertCount(4, MessageThread::open()->get());
        $this->assertTrue($this->t['juanToLecturers']->isResolved());
        $this->assertFalse($this->t['juanToLogistica']->isResolved());
    }

    public function test_the_audience_and_the_participants_of_a_thread(): void
    {
        $thread = $this->t['juanToLogistica'];

        $this->assertSame('Coordinación de Logística', $thread->audienceLabel());
        $this->assertSame('COORDINATOR:'.$this->logistica->id, $thread->audience()->key());
        $this->assertSame('Presidencia', $this->t['luisToPresidency']->audienceLabel());
        $this->assertEqualsCanonicalizing(
            [$this->u['juan']->id, $this->u['coordinator']->id],
            $thread->participants()->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$this->u['luis']->id, $this->u['president']->id, $this->u['lecturer']->id],
            $this->t['luisToPresidency']->participants()->pluck('id')->all(),
        );
    }

    public function test_the_link_to_the_object_of_the_thread(): void
    {
        if (! Route::has('activity.evidence')) {
            Route::get('/_test/activity/evidence/{id}', fn () => 'ok')->name('activity.evidence');
            Route::getRoutes()->refreshNameLookups();
        }
        $old = Evidence::create(['title' => 'v1', 'status' => 'REJECTED', 'user_id' => $this->u['juan']->id, 'committee_id' => $this->logistica->id, 'last' => false]);
        $head = Evidence::create(['title' => 'v2', 'status' => 'PENDING', 'user_id' => $this->u['juan']->id, 'committee_id' => $this->logistica->id, 'points_to' => $old->id]);
        $thread = $this->t['juanToLogistica'];
        $thread->update(['about_type' => 'evidence', 'about_id' => $old->id, 'about_title' => 'v1']);

        $this->assertSame(route('activity.evidence', ['id' => $head->id]), $thread->aboutUrl($this->u['juan']));
        $this->assertSame(route('activity.evidence', ['id' => $head->id]), $thread->aboutUrl($this->u['coordinator']->fresh()));
        $this->assertNull($thread->aboutUrl($this->u['coordinatorPrograma']->fresh()));
        $this->assertNull($this->t['luisToPresidency']->aboutUrl($this->u['luis']));

        $thread->update(['about_id' => 999999]);
        $this->assertNull($thread->aboutUrl($this->u['juan']));
    }
}
