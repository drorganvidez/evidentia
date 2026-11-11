<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSession;
use App\Services\Deadlines;
use App\View\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The work stopwatch of the topbar: CRUD of the own sessions, the forbidden cases between people and the prefill of
 * the form of a new evidence with one or several of them.
 */
class WorkSessionTest extends TestCase
{
    use RefreshDatabase;

    private User $ana;

    private User $beto;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate([
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
            'meetings_timestamp' => now()->addWeek(),
            'bonus_timestamp' => now()->addWeek(),
            'attendee_timestamp' => now()->addWeek(),
        ]);
        Committee::create(['name' => 'Logística']);
        $this->ana = $this->person('STUDENT');
        $this->beto = $this->person('STUDENT');
    }

    private function person(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user->fresh();
    }

    private function workSession(User $owner, string $status = WorkSession::STATUS_STOPPED, int $seconds = 600, string $title = 'Montaje del stand'): WorkSession
    {
        return WorkSession::create([
            'user_id' => $owner->id,
            'title' => $title,
            'status' => $status,
            'accumulated_seconds' => $seconds,
            'started_at' => $status === WorkSession::STATUS_RUNNING ? now() : null,
            'stopped_at' => $status === WorkSession::STATUS_STOPPED ? now() : null,
        ]);
    }

    private function fieldTag(string $html, string $name): string
    {
        $this->assertSame(1, preg_match('/<input\b[^>]*\bname="'.preg_quote($name, '/').'"[^>]*>/s', $html, $tag), 'The field '.$name);

        return $tag[0];
    }

    // ---------------------------------------------------------------- Create

    public function test_a_person_starts_a_session_with_a_title(): void
    {
        $response = $this->actingAs($this->ana)->postJson(route('work-sessions.start'), ['title' => '  Montaje del stand  ']);

        $response->assertCreated()
            ->assertJsonPath('session.title', 'Montaje del stand')
            ->assertJsonPath('session.status', 'running')
            ->assertJsonPath('active.id', $response->json('session.id'));
        $this->assertDatabaseHas('work_sessions', ['user_id' => $this->ana->id, 'title' => 'Montaje del stand', 'status' => 'running', 'accumulated_seconds' => 0]);
    }

    public function test_the_title_is_required_and_short_enough(): void
    {
        $this->actingAs($this->ana)->postJson(route('work-sessions.start'), ['title' => ''])->assertJsonValidationErrors('title');
        $this->postJson(route('work-sessions.start'), [])->assertJsonValidationErrors('title');
        $this->postJson(route('work-sessions.start'), ['title' => str_repeat('a', 256)])->assertJsonValidationErrors('title');
        $this->postJson(route('work-sessions.start'), ['title' => str_repeat('a', 255)])->assertCreated();

        $this->assertSame(1, WorkSession::count());
    }

    public function test_starting_another_session_pauses_the_one_running(): void
    {
        $first = $this->workSession($this->ana, WorkSession::STATUS_RUNNING, 0, 'Primera');

        $this->travel(75)->seconds();
        $this->actingAs($this->ana)->postJson(route('work-sessions.start'), ['title' => 'Segunda'])->assertCreated();

        $first->refresh();
        $this->assertSame(WorkSession::STATUS_PAUSED, $first->status);
        $this->assertSame(75, $first->accumulated_seconds);
        $this->assertSame(1, WorkSession::where('user_id', $this->ana->id)->where('status', 'running')->count());
    }

    // ------------------------------------------------------------------ Read

    public function test_the_state_lists_the_active_session_and_the_recent_stopped_ones_of_the_person(): void
    {
        $active = $this->workSession($this->ana, WorkSession::STATUS_PAUSED, 300, 'Pausada');
        $stopped = $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 3600, 'Terminada');

        $response = $this->actingAs($this->ana)->getJson(route('work-sessions.index'))->assertOk();

        $response->assertJsonPath('active.id', $active->id)
            ->assertJsonPath('active.status', 'paused')
            ->assertJsonPath('recent.0.id', $stopped->id)
            ->assertJsonPath('recent.0.hours', 1)
            ->assertJsonPath('recent.0.minutes', 0)
            ->assertJsonPath('recent.0.formatted_clock', '01:00:00')
            ->assertJsonCount(1, 'recent');
    }

    public function test_a_running_session_keeps_counting_when_the_state_is_read_again(): void
    {
        $session = $this->workSession($this->ana, WorkSession::STATUS_RUNNING, 60);

        $this->travel(120)->seconds();
        $response = $this->actingAs($this->ana)->getJson(route('work-sessions.index'))->assertOk();

        $this->assertSame($session->id, $response->json('active.id'));
        $this->assertSame(180, $response->json('active.elapsed_seconds'), 'What was accumulated plus what has passed since');
        $this->assertNotNull($response->json('active.started_at_ms'));
    }

    public function test_the_recent_list_is_limited_and_newest_first(): void
    {
        foreach (range(1, 17) as $i) {
            $this->travel(1)->minutes();
            $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 60, 'Sesión '.$i);
        }

        $recent = $this->actingAs($this->ana)->getJson(route('work-sessions.index'))->json('recent');

        $this->assertCount(15, $recent);
        $this->assertSame('Sesión 17', $recent[0]['title']);
        $this->assertSame('Sesión 3', $recent[14]['title']);
    }

    // ---------------------------------------------------------------- Update

    public function test_pause_resume_and_stop_walk_the_states_and_add_the_seconds(): void
    {
        $this->actingAs($this->ana);
        $id = $this->postJson(route('work-sessions.start'), ['title' => 'Cartelería'])->json('session.id');

        $this->travel(90)->seconds();
        $this->postJson(route('work-sessions.pause', ['id' => $id]))->assertOk()
            ->assertJsonPath('session.status', 'paused')
            ->assertJsonPath('session.accumulated_seconds', 90);

        $this->travel(500)->seconds();
        $this->assertSame(90, WorkSession::find($id)->elapsedSeconds(), 'A paused session does not count');

        $this->postJson(route('work-sessions.resume', ['id' => $id]))->assertOk()->assertJsonPath('session.status', 'running');
        $this->travel(30)->seconds();
        $this->postJson(route('work-sessions.stop', ['id' => $id]))->assertOk()
            ->assertJsonPath('session.status', 'stopped')
            ->assertJsonPath('session.accumulated_seconds', 120)
            ->assertJsonPath('session.minutes', 2)
            ->assertJsonPath('recent.0.id', $id)
            ->assertJsonPath('active', null);

        $stopped = WorkSession::find($id);
        $this->assertNotNull($stopped->stopped_at);
        $this->assertNull($stopped->started_at);
    }

    public function test_a_paused_session_can_be_stopped_and_stopping_twice_changes_nothing(): void
    {
        $session = $this->workSession($this->ana, WorkSession::STATUS_PAUSED, 200);

        $this->actingAs($this->ana)->postJson(route('work-sessions.stop', ['id' => $session->id]))->assertOk();
        $stoppedAt = $session->refresh()->stopped_at;

        $this->travel(60)->seconds();
        $this->postJson(route('work-sessions.stop', ['id' => $session->id]))->assertOk();

        $session->refresh();
        $this->assertSame(200, $session->accumulated_seconds);
        $this->assertEquals($stoppedAt, $session->stopped_at);
    }

    public function test_resuming_a_session_pauses_the_one_that_was_running(): void
    {
        $paused = $this->workSession($this->ana, WorkSession::STATUS_PAUSED, 100, 'Pausada');
        $running = $this->workSession($this->ana, WorkSession::STATUS_RUNNING, 0, 'En curso');

        $this->travel(40)->seconds();
        $this->actingAs($this->ana)->postJson(route('work-sessions.resume', ['id' => $paused->id]))->assertOk();

        $this->assertSame(WorkSession::STATUS_RUNNING, $paused->refresh()->status);
        $this->assertSame(WorkSession::STATUS_PAUSED, $running->refresh()->status);
        $this->assertSame(40, $running->accumulated_seconds);
    }

    public function test_the_title_can_be_corrected_while_pausing_or_stopping(): void
    {
        $session = $this->workSession($this->ana, WorkSession::STATUS_RUNNING, 0, 'Borrador');

        $this->actingAs($this->ana)->postJson(route('work-sessions.stop', ['id' => $session->id]), ['title' => 'Título final'])->assertOk();

        $this->assertSame('Título final', $session->refresh()->title);
    }

    // ---------------------------------------------------------------- Delete

    public function test_a_person_deletes_their_own_session(): void
    {
        $session = $this->workSession($this->ana);

        $this->actingAs($this->ana)->deleteJson(route('work-sessions.destroy', ['id' => $session->id]))->assertOk()->assertJsonCount(0, 'recent');

        $this->assertDatabaseMissing('work_sessions', ['id' => $session->id]);
    }

    public function test_a_session_that_does_not_exist_is_not_found(): void
    {
        $this->actingAs($this->ana);

        $this->postJson(route('work-sessions.pause', ['id' => 999]))->assertNotFound();
        $this->postJson(route('work-sessions.resume', ['id' => 999]))->assertNotFound();
        $this->postJson(route('work-sessions.stop', ['id' => 999]))->assertNotFound();
        $this->deleteJson(route('work-sessions.destroy', ['id' => 999]))->assertNotFound();
    }

    // -------------------------------------------------- Forbidden cases

    public function test_nobody_sees_the_sessions_of_another_person(): void
    {
        $this->workSession($this->ana, WorkSession::STATUS_RUNNING, 0, 'Secreto de Ana');
        $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 900, 'Reservado de Ana');
        $own = $this->workSession($this->beto, WorkSession::STATUS_STOPPED, 60, 'Propia de Beto');

        $response = $this->actingAs($this->beto)->getJson(route('work-sessions.index'))->assertOk();

        $response->assertJsonPath('active', null)->assertJsonCount(1, 'recent')->assertJsonPath('recent.0.id', $own->id);
        $this->assertStringNotContainsString('Ana', $response->getContent());
    }

    public function test_nobody_controls_the_session_of_another_person(): void
    {
        $running = $this->workSession($this->ana, WorkSession::STATUS_RUNNING, 10, 'De Ana en curso');
        $paused = $this->workSession($this->ana, WorkSession::STATUS_PAUSED, 20, 'De Ana en pausa');
        $stopped = $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 30, 'De Ana terminada');
        $before = $this->databaseSnapshot();

        $this->actingAs($this->beto);
        $this->postJson(route('work-sessions.pause', ['id' => $running->id]))->assertForbidden();
        $this->postJson(route('work-sessions.resume', ['id' => $paused->id]))->assertForbidden();
        $this->postJson(route('work-sessions.stop', ['id' => $running->id]), ['title' => 'Robada'])->assertForbidden();
        $this->deleteJson(route('work-sessions.destroy', ['id' => $stopped->id]))->assertForbidden();

        $this->assertSame($before, $this->databaseSnapshot(), 'Not a single row changed');
        $this->assertSame('De Ana en curso', $running->refresh()->title);
    }

    public function test_starting_a_session_leaves_the_running_one_of_another_person_alone(): void
    {
        $anas = $this->workSession($this->ana, WorkSession::STATUS_RUNNING, 0, 'De Ana');

        $this->actingAs($this->beto)->postJson(route('work-sessions.start'), ['title' => 'De Beto'])->assertCreated();

        $this->assertSame(WorkSession::STATUS_RUNNING, $anas->refresh()->status);
    }

    public function test_a_guest_is_sent_to_the_login(): void
    {
        $session = $this->workSession($this->ana);

        $this->get(route('work-sessions.index'))->assertRedirect(route('login'));
        $this->post(route('work-sessions.start'), ['title' => 'Anónimo'])->assertRedirect(route('login'));
        $this->post(route('work-sessions.stop', ['id' => $session->id]))->assertRedirect(route('login'));
        $this->delete(route('work-sessions.destroy', ['id' => $session->id]))->assertRedirect(route('login'));

        $this->assertSame(1, WorkSession::count());
    }

    public function test_a_lecturer_on_their_own_has_no_stopwatch(): void
    {
        $lecturer = $this->person('LECTURE');

        $this->actingAs($lecturer)->getJson(route('work-sessions.index'))->assertNotFound();
        $this->postJson(route('work-sessions.start'), ['title' => 'Profesor'])->assertNotFound();
        $this->assertSame(0, WorkSession::count());
    }

    // ------------------------------------------------------ Evidence prefill

    public function test_one_stopped_session_prefills_the_title_and_the_duration(): void
    {
        $session = $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 5400, 'Montaje del stand');

        $html = $this->actingAs($this->ana)->get(route('evidence.create', ['sessions' => [$session->id]]))->assertOk()->getContent();

        $this->assertStringContainsString('value="Montaje del stand"', $this->fieldTag($html, 'title'));
        $this->assertStringContainsString('value="1"', $this->fieldTag($html, 'hours'));
        $this->assertStringContainsString('value="30"', $this->fieldTag($html, 'minutes'));
    }

    public function test_the_stop_action_link_opens_the_form_with_that_session(): void
    {
        $id = $this->actingAs($this->ana)->postJson(route('work-sessions.start'), ['title' => 'Cartelería'])->json('session.id');
        $this->travel(61)->seconds();
        $url = $this->postJson(route('work-sessions.stop', ['id' => $id]))->json('session.create_evidence_url');

        $html = $this->get($url)->assertOk()->getContent();

        $this->assertStringContainsString('value="Cartelería"', $this->fieldTag($html, 'title'));
        $this->assertStringContainsString('value="0"', $this->fieldTag($html, 'hours'));
        $this->assertStringContainsString('value="1"', $this->fieldTag($html, 'minutes'));
    }

    public function test_several_sessions_sum_their_duration_into_one_evidence(): void
    {
        $ids = [
            $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 1800, 'Montaje')->id,
            $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 1200, 'Cartelería')->id,
            $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 600, 'Recogida')->id,
        ];

        $html = $this->actingAs($this->ana)->get(route('evidence.create', ['sessions' => $ids]))->assertOk()->getContent();

        $this->assertStringContainsString('value="Montaje · Cartelería · Recogida"', $this->fieldTag($html, 'title'));
        $this->assertStringContainsString('value="1"', $this->fieldTag($html, 'hours'), '30 + 20 + 10 minutes: the frontier of 60 passes to one hour');
        $this->assertStringContainsString('value="0"', $this->fieldTag($html, 'minutes'));
    }

    public function test_the_sessions_of_another_person_are_never_added_to_the_form(): void
    {
        $own = $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 600, 'Propia');
        $foreign = $this->workSession($this->beto, WorkSession::STATUS_STOPPED, 7200, 'Ajena de Beto');

        $html = $this->actingAs($this->ana)->get(route('evidence.create', ['sessions' => [$own->id, $foreign->id]]))->assertOk()->getContent();

        $this->assertStringContainsString('value="Propia"', $this->fieldTag($html, 'title'));
        $this->assertStringContainsString('value="0"', $this->fieldTag($html, 'hours'), 'The two hours of Beto are not added');
        $this->assertStringContainsString('value="10"', $this->fieldTag($html, 'minutes'));
        $this->assertStringNotContainsString('Ajena de Beto', $html);

        $only = $this->get(route('evidence.create', ['sessions' => [$foreign->id]]))->assertOk()->getContent();

        $this->assertStringNotContainsString('Ajena de Beto', $only);
        $this->assertStringNotContainsString('value="2"', $this->fieldTag($only, 'hours'));
        $this->assertDoesNotMatchRegularExpression('/value="[^"]+"/', $this->fieldTag($only, 'title'), 'The form stays empty');
    }

    public function test_only_stopped_sessions_prefill_and_wrong_ids_are_ignored(): void
    {
        $running = $this->workSession($this->ana, WorkSession::STATUS_RUNNING, 600, 'En curso');
        $stopped = $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 120, 'Terminada');

        $html = $this->actingAs($this->ana)
            ->get(route('evidence.create', ['sessions' => [$running->id, $stopped->id, 'abc', '-1', '999999', ['x']]]))
            ->assertOk()->getContent();

        $this->assertStringContainsString('value="Terminada"', $this->fieldTag($html, 'title'));
        $this->assertStringContainsString('value="2"', $this->fieldTag($html, 'minutes'), 'The ten minutes of the running one are not added');
        $this->assertStringNotContainsString('En curso', $this->fieldTag($html, 'title'));

        $this->get(route('evidence.create', ['sessions' => 'not-a-list']))->assertOk();
    }

    public function test_the_form_without_sessions_stays_empty(): void
    {
        $html = $this->actingAs($this->ana)->get(route('evidence.create'))->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/value="[^"]+"/', $this->fieldTag($html, 'title'));
        $this->assertDoesNotMatchRegularExpression('/value="[^"]+"/', $this->fieldTag($html, 'hours'));
    }

    // ----------------------------------------------------------------- Topbar

    public function test_the_topbar_carries_the_stopwatch_with_only_the_sessions_of_the_person(): void
    {
        $this->workSession($this->ana, WorkSession::STATUS_STOPPED, 60, 'Visible para Ana');
        $this->workSession($this->beto, WorkSession::STATUS_STOPPED, 60, 'Oculta de Beto');

        $html = $this->actingAs($this->ana)->get(route('evidence.list'))->assertOk()->getContent();

        $this->assertStringContainsString('x-data="workTimer"', $html);
        $this->assertStringContainsString('Visible para Ana', $html);
        $this->assertStringNotContainsString('Oculta de Beto', $html);
        $this->assertStringContainsString('id="work-timer-panel"', $html);
    }

    public function test_the_stopwatch_leaves_the_topbar_when_the_upload_closes_or_there_are_no_evidences(): void
    {
        $this->assertIsArray(app(Navigation::class)->for($this->ana, 'home')['timer']);
        $this->assertNull(app(Navigation::class)->for($this->person('LECTURE'), 'home')['timer'], 'A lecturer on their own has no evidences');

        Configuration::query()->update(['upload_evidences_timestamp' => now()->subDay()]);
        app(Deadlines::class)->refresh();

        $this->assertNull(app(Navigation::class)->for($this->ana, 'home')['timer']);
    }
}
