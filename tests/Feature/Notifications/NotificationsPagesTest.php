<?php

namespace Tests\Feature\Notifications;

use App\Models\Evidence;
use App\Models\User;
use App\Services\EvidenceReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Screens\PublicAndStudent\ScreenTestCase;

/**
 * The inbox of the notices: only the notices of the person, the unread filter, the read marks and links that only
 * ever lead to a page of the app.
 */
class NotificationsPagesTest extends ScreenTestCase
{
    /** A row of the inbox written by hand, for the cases the real notices never produce. */
    private function notice(User $user, string $title, string $url = '/', bool $read = false, ?string $at = null): string
    {
        $id = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $id, 'type' => 'evidence.accepted', 'notifiable_type' => 'user', 'notifiable_id' => $user->id,
            'data' => json_encode(['title' => $title, 'text' => null, 'tone' => 'accepted', 'url' => $url]),
            'read_at' => $read ? now() : null, 'created_at' => $at ?? now(), 'updated_at' => $at ?? now(),
        ]);

        return $id;
    }

    /** The coordinator rejects the pending evidence of the student: a real notice in her inbox. */
    private function rejectPhotocall(): Evidence
    {
        $evidence = Evidence::where('title', 'Montaje del photocall')->sole();
        $this->actingAs($this->person('coordinador'));
        app(EvidenceReview::class)->reject($evidence, 'Faltan las fotos del montaje terminado.', 'COORDINATOR');

        return $evidence;
    }

    public function test_the_inbox_shows_the_notices_of_the_person_and_nobody_elses(): void
    {
        $this->rejectPhotocall();
        $this->notice($this->person('estudiante'), 'Evidencia aceptada', read: true, at: now()->subDay()->toDateTimeString());
        $this->notice($this->person('companero'), 'Aviso de Juan');

        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('notifications.index')));

        $this->assertStringContainsString('Evidencia rechazada', $html);
        $this->assertStringContainsString('Montaje del photocall · Faltan las fotos del montaje terminado.', $html);
        $this->assertStringContainsString('Evidencia aceptada', $html);
        $this->assertStringNotContainsString('Aviso de Juan', $html);
        $this->assertSame(1, substr_count($html, ', sin leer</span>'));
        $this->assertStringContainsString('Avisos, 1 sin leer', $html, 'The bell names the unread notices');
        $this->assertStringContainsString('action="'.route('notifications.read_all').'"', $html);
        $this->assertNoAutofocus($html);
        $this->assertLessThan(strpos($html, 'Evidencia aceptada'), strpos($html, 'Evidencia rechazada'), 'Newest first');
    }

    public function test_the_unread_filter_and_the_empty_inbox(): void
    {
        $student = $this->person('estudiante');
        $this->notice($student, 'Aviso leído', read: true);
        $this->notice($student, 'Aviso nuevo');

        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('notifications.index', ['unread' => 1])));
        $this->assertStringContainsString('Aviso nuevo', $html);
        $this->assertStringNotContainsString('Aviso leído', $html);
        $this->assertMatchesRegularExpression('/aria-current="page"\s*>\s*Sin leer/', $html);

        // anything else is ignored, never an error
        $html = $this->assertAppScreen($this->get(route('notifications.index', ['unread' => 'quizá'])));
        $this->assertStringContainsString('Aviso leído', $html);

        $html = $this->assertAppScreen($this->signedAs('profesor')->get(route('notifications.index')));
        $this->assertStringContainsString('No tienes avisos', $html);
        $this->assertMatchesRegularExpression('/<button[^>]*type="submit"[^>]*disabled/', $html, 'Nothing to mark as read');
    }

    public function test_the_inbox_pages_by_twenty(): void
    {
        $student = $this->person('estudiante');
        foreach (range(1, 22) as $n) {
            $this->notice($student, 'Aviso número '.$n, at: now()->subMinutes($n)->toDateTimeString());
        }

        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('notifications.index')));
        $this->assertMatchesRegularExpression('/Aviso número 20\s*</', $html);
        $this->assertDoesNotMatchRegularExpression('/Aviso número 21\s*</', $html);
        $this->assertStringContainsString('Paginación de los avisos', $html);

        $html = $this->assertAppScreen($this->get(route('notifications.index', ['page' => 2])));
        $this->assertMatchesRegularExpression('/Aviso número 22\s*</', $html);
    }

    public function test_opening_a_notice_marks_it_and_follows_its_link(): void
    {
        $evidence = $this->rejectPhotocall();
        $student = $this->person('estudiante');
        $notification = $student->notifications()->sole();

        $this->signedAs('estudiante')
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(url($notification->data['url']));

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertStringEndsWith('/'.$evidence->id, $notification->data['url']);
    }

    public function test_a_notice_of_someone_else_is_not_found_and_stays_unread(): void
    {
        $id = $this->notice($this->person('companero'), 'Aviso de Juan', '/evidence/list');

        $this->signedAs('estudiante')->get(route('notifications.open', $id))->assertNotFound();

        $this->assertNull(DB::table('notifications')->where('id', $id)->value('read_at'));
    }

    public function test_a_link_out_of_the_app_leads_to_the_inbox(): void
    {
        $student = $this->person('estudiante');

        foreach (['https://evil.test/x', '//evil.test/x', '/\\evil.test', 'javascript:alert(1)', ''] as $url) {
            $id = $this->notice($student, 'Aviso', $url);
            $this->signedAs('estudiante')->get(route('notifications.open', $id))->assertRedirect(route('notifications.index'));
        }
    }

    public function test_marking_everything_as_read_only_touches_the_notices_of_the_person(): void
    {
        $student = $this->person('estudiante');
        $this->notice($student, 'Uno');
        $this->notice($student, 'Dos');
        $other = $this->notice($this->person('companero'), 'Aviso de Juan');

        $this->signedAs('estudiante')
            ->post(route('notifications.read_all'))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Avisos marcados como leídos.');

        $this->assertSame(0, $student->unreadNotifications()->count());
        $this->assertNull(DB::table('notifications')->where('id', $other)->value('read_at'));
    }

    public function test_a_guest_is_sent_to_the_login(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->post(route('notifications.read_all'))->assertRedirect(route('login'));
    }
}
