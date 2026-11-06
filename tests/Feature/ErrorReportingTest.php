<?php

namespace Tests\Feature;

use App\Models\ErrorOccurrence;
use App\Models\ErrorReport;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ErrorReported;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/**
 * Every server error is kept with its context, grouped, shown to the person with a reference and announced to the
 * lecturers, who resolve it in their screen.
 */
class ErrorReportingTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.debug' => false]);
        $this->lecturer = $this->person('LECTURE');
        Route::middleware('web')->get('/_boom', fn () => throw new RuntimeException('Fallo de prueba'));
        Route::middleware('web')->get('/_boom-elsewhere', fn () => throw new RuntimeException('Otro fallo'));
    }

    private function person(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));

        return $user;
    }

    public function test_a_server_error_is_kept_with_who_and_where_and_the_page_shows_its_reference(): void
    {
        Notification::fake();
        $student = $this->person('STUDENT');

        $html = $this->actingAs($student)->get('/_boom?x=1')->assertStatus(500)->getContent();

        $report = ErrorReport::sole();
        $this->assertSame(RuntimeException::class, $report->exception);
        $this->assertSame('Fallo de prueba', $report->message);
        $this->assertSame(1, $report->occurrences);
        $this->assertNull($report->resolved_at);
        $this->assertStringContainsString('ErrorReportingTest', (string) $report->trace);

        $occurrence = ErrorOccurrence::sole();
        $this->assertSame('GET', $occurrence->method);
        $this->assertStringEndsWith('/_boom?x=1', $occurrence->url);
        $this->assertSame($student->id, $occurrence->user_id);

        // The new error page, with the code the lecturer searches by
        $this->assertStringContainsString('Algo ha fallado', $html);
        $this->assertStringContainsString($occurrence->code(), $html);
        $this->assertStringNotContainsString('Fallo de prueba', $html, 'The page never shows the message');
    }

    public function test_the_same_error_is_grouped_and_the_lecturers_hear_about_it_once_an_hour_at_most(): void
    {
        Notification::fake();

        $this->get('/_boom')->assertStatus(500);
        $this->get('/_boom')->assertStatus(500);
        $this->get('/_boom-elsewhere')->assertStatus(500);

        $this->assertSame(2, ErrorReport::count());
        $this->assertSame(2, ErrorReport::where('message', 'Fallo de prueba')->sole()->occurrences);
        Notification::assertSentToTimes($this->lecturer, ErrorReported::class, 2);
    }

    public function test_a_resolved_error_that_comes_back_opens_again_and_is_announced(): void
    {
        Notification::fake();
        $this->get('/_boom');
        ErrorReport::query()->update(['resolved_at' => now(), 'notified_at' => now()->subHours(2)]);

        $this->get('/_boom');

        $report = ErrorReport::sole();
        $this->assertNull($report->resolved_at);
        $this->assertSame(2, $report->occurrences);
        Notification::assertSentToTimes($this->lecturer, ErrorReported::class, 2);
    }

    public function test_the_notice_goes_by_mail_and_to_the_inbox_of_the_lecturers_only(): void
    {
        Notification::fake();
        $president = $this->person('PRESIDENT');

        $this->get('/_boom');

        Notification::assertSentTo($this->lecturer, ErrorReported::class, function (ErrorReported $notice, array $channels) {
            return $channels === ['database', 'mail']
                && str_contains($notice->mailSubject($this->lecturer), 'RuntimeException')
                && str_ends_with($notice->url($this->lecturer), '/lecture/errors/'.$notice->report->id);
        });
        Notification::assertNotSentTo($president, ErrorReported::class);
    }

    public function test_every_error_also_lands_in_the_errors_log_with_its_context(): void
    {
        Notification::fake();
        $channel = \Mockery::mock();
        $channel->shouldReceive('error')->once()->withArgs(fn (string $message, array $context) => $message === 'Fallo de prueba'
            && $context['method'] === 'GET'
            && str_ends_with($context['url'], '/_boom')
            && strlen($context['reference']) === 36);
        Log::shouldReceive('channel')->with('errors')->andReturn($channel);
        Log::shouldReceive('error', 'log', 'withContext', 'shareContext')->andReturnNull();

        $this->get('/_boom')->assertStatus(500);
    }

    public function test_a_broken_error_store_never_hides_the_original_error(): void
    {
        Notification::fake();
        Schema::drop('error_occurrences');

        $this->get('/_boom')->assertStatus(500)->assertSee('Algo ha fallado');

        $this->assertSame(0, ErrorReport::count(), 'The failed recording rolled back');
    }

    public function test_not_found_and_validation_errors_are_not_server_errors(): void
    {
        Notification::fake();

        $this->get('/no-such-page')->assertNotFound()->assertSee('No encontramos esa página');

        $this->assertSame(0, ErrorReport::count());
    }

    public function test_the_lecturer_lists_finds_by_code_resolves_and_reopens_the_errors(): void
    {
        Notification::fake();
        $this->get('/_boom');
        $this->get('/_boom-elsewhere');
        $report = ErrorReport::where('message', 'Fallo de prueba')->sole();
        $code = ErrorOccurrence::where('error_report_id', $report->id)->sole()->code();

        $this->actingAs($this->lecturer);
        $this->get(route('lecture.errors'))->assertOk()->assertSee('RuntimeException')->assertSee('Otro fallo');
        $this->get(route('lecture.errors', ['codigo' => $code]))->assertOk()->assertSee('Fallo de prueba')->assertDontSee('Otro fallo');
        $this->get(route('lecture.errors.show', $report->id))->assertOk()->assertSee($code)->assertSee('Marcar como resuelto');

        $this->post(route('lecture.errors.resolve', $report->id))->assertRedirect(route('lecture.errors'));
        $this->assertNotNull($report->fresh()->resolved_at);
        $this->get(route('lecture.errors'))->assertDontSee('Fallo de prueba');
        $this->get(route('lecture.errors', ['estado' => 'resueltos']))->assertSee('Fallo de prueba');

        $this->post(route('lecture.errors.reopen', $report->id))->assertRedirect(route('lecture.errors.show', $report->id));
        $this->assertNull($report->fresh()->resolved_at);
    }

    public function test_the_errors_come_in_pages_that_keep_the_state(): void
    {
        foreach (range(1, 30) as $index) {
            ErrorReport::create([
                'fingerprint' => sha1('fallo '.$index), 'exception' => RuntimeException::class, 'message' => sprintf('Fallo %02d', $index),
                'occurrences' => 1, 'first_seen_at' => now(), 'last_seen_at' => now()->subMinutes($index),
            ]);
        }

        $this->actingAs($this->lecturer);
        $this->get(route('lecture.errors', ['estado' => 'todos']))->assertOk()
            ->assertSee('<nav aria-label="Páginas de errores"', false)
            ->assertSee('De la 1 a la 10 de 30')
            ->assertSee('Fallo 01')
            ->assertSee('Fallo 10')
            ->assertDontSee('Fallo 11')
            ->assertSee('href="'.e(route('lecture.errors', ['estado' => 'todos', 'page' => 2])).'" rel="next"', false);
        $this->get(route('lecture.errors', ['estado' => 'todos', 'page' => 9]))->assertOk()
            ->assertSee('De la 21 a la 30 de 30')
            ->assertSee('Fallo 30');

        // ordered by how many times, the tabs and the search by code keep the order and the rows per page
        $this->get(route('lecture.errors', ['estado' => 'todos', 'sort' => 'count', 'dir' => 'asc', 'per_page' => 50]))->assertOk()
            ->assertSee('href="'.e(route('lecture.errors', ['estado' => 'abiertos', 'sort' => 'count', 'dir' => 'asc', 'per_page' => 50])).'"', false)
            ->assertSee('<input type="hidden" name="per_page" value="50">', false)
            ->assertSee('<input type="hidden" name="sort" value="count">', false)
            ->assertSee('<input type="hidden" name="dir" value="asc">', false);
    }

    public function test_the_president_lists_resolves_and_reopens_them_under_their_own_routes(): void
    {
        Notification::fake();
        $this->get('/_boom');
        $report = ErrorReport::where('message', 'Fallo de prueba')->sole();

        $this->actingAs($this->person('PRESIDENT'));
        $this->get(route('president.errors'))->assertOk()->assertSee('Fallo de prueba')
            ->assertSee('href="'.route('president.errors.show', $report->id).'"', false)
            ->assertDontSee('/lecture/errors', false);
        $this->get(route('president.errors.show', $report->id))->assertOk()
            ->assertSee('action="'.route('president.errors.resolve', $report->id).'"', false);

        $this->post(route('president.errors.resolve', $report->id))->assertRedirect(route('president.errors'));
        $this->assertNotNull($report->fresh()->resolved_at);
        $this->post(route('president.errors.reopen', $report->id))->assertRedirect(route('president.errors.show', $report->id));
        $this->assertNull($report->fresh()->resolved_at);
    }

    public function test_only_the_management_opens_the_errors_screen(): void
    {
        foreach (['STUDENT', 'COORDINATOR'] as $role) {
            $this->actingAs($this->person($role))->get(route('lecture.errors'))->assertNotFound();
            $this->actingAs($this->person($role))->get(route('president.errors'))->assertNotFound();
        }
    }
}
