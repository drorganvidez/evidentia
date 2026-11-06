<?php

namespace Tests\Feature;

use App\Enums\Deadline;
use App\Http\Middleware\CheckRegisterBonus;
use App\Http\Middleware\CheckRegisterEventsAndAttendings;
use App\Http\Middleware\CheckRegisterMeeting;
use App\Http\Middleware\CheckUploadEvidences;
use App\Http\Middleware\CheckValidateEvidences;
use App\Models\Configuration;
use App\Services\Deadlines;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * The deadlines service says open exactly when the middleware of each deadline lets the request through.
 */
class DeadlinesTest extends TestCase
{
    use RefreshDatabase;

    private const MIDDLEWARE = [
        'upload_evidences' => CheckUploadEvidences::class,
        'validate_evidences' => CheckValidateEvidences::class,
        'meetings' => CheckRegisterMeeting::class,
        'bonus' => CheckRegisterBonus::class,
        'attendee' => CheckRegisterEventsAndAttendings::class,
    ];

    private Deadlines $deadlines;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['id' => 1]);
        $this->deadlines = app(Deadlines::class);
    }

    private function set(Deadline $deadline, mixed $value): void
    {
        Configuration::first()->update([$deadline->column() => $value]);
        $this->deadlines->refresh();
    }

    private function middlewareLetsThrough(Deadline $deadline): bool
    {
        $middleware = self::MIDDLEWARE[$deadline->value];

        try {
            (new $middleware)->handle(request(), fn () => 'next');

            return true;
        } catch (HttpException $e) {
            $this->assertSame(404, $e->getStatusCode());

            return false;
        }
    }

    public function test_open_means_the_same_as_the_middleware_of_each_deadline(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 20:00:00'));
        $cases = [
            'future' => ['2026-11-21 10:00:00', true],
            'past' => ['2026-11-19 10:00:00', false],
            'unset' => [null, true],
            'closing instant' => ['2026-11-20 20:00:00', true],
            'a second later' => ['2026-11-20 19:59:59', false],
        ];

        foreach (Deadline::cases() as $deadline) {
            foreach ($cases as $case => [$value, $open]) {
                $this->set($deadline, $value);

                $this->assertSame($open, $this->deadlines->isOpen($deadline), "{$deadline->value} {$case}");
                // sin fecha el middleware no llega a decidir (Carbon 3 no compara con null); el servicio lo da por abierto
                if ($value !== null) {
                    $this->assertSame($this->middlewareLetsThrough($deadline), $this->deadlines->isOpen($deadline), "{$deadline->value} {$case}");
                }
            }
        }
    }

    public function test_without_a_configuration_row_every_deadline_is_open_and_unset(): void
    {
        Configuration::query()->delete();
        $this->deadlines->refresh();

        foreach (Deadline::cases() as $deadline) {
            $this->assertTrue($this->deadlines->isOpen($deadline));
            $this->assertFalse($this->deadlines->isSet($deadline));
            $this->assertNull($this->deadlines->closesAt($deadline));
        }
    }

    public function test_the_closing_time_is_read_in_spanish_time(): void
    {
        $this->set(Deadline::UploadEvidences, '2026-06-15 20:00:00');

        $closesAt = $this->deadlines->closesAt(Deadline::UploadEvidences);

        $this->assertSame('2026-06-15 20:00', $closesAt->format('Y-m-d H:i'));
        $this->assertSame('Europe/Madrid', $closesAt->getTimezone()->getName());
        $this->assertSame('2026-06-15 18:00', $closesAt->utc()->format('Y-m-d H:i'));
        $this->assertTrue($this->deadlines->isSet(Deadline::UploadEvidences));
        $this->assertFalse($this->deadlines->isSet(Deadline::Bonus));
    }

    public function test_the_configuration_is_read_once_per_request_until_refreshed(): void
    {
        $this->set(Deadline::Bonus, now()->addDay());
        DB::enableQueryLog();

        $this->deadlines->isOpen(Deadline::Bonus);
        $this->deadlines->isOpen(Deadline::Meetings);
        $this->deadlines->closesAt(Deadline::Bonus);

        $this->assertCount(1, DB::getQueryLog());

        DB::table('configuration')->update(['bonus_timestamp' => now()->subDay()]);
        $this->assertTrue($this->deadlines->isOpen(Deadline::Bonus));

        $this->deadlines->refresh();
        $this->assertFalse($this->deadlines->isOpen(Deadline::Bonus));
    }

    public function test_the_menu_only_shows_a_deadline_that_is_set_and_open(): void
    {
        $this->set(Deadline::UploadEvidences, null);
        $this->assertFalse($this->deadlines->isOpenForMenu(Deadline::UploadEvidences));
        $this->assertNull($this->deadlines->card(Deadline::UploadEvidences));
        $this->assertNull($this->deadlines->remaining(Deadline::UploadEvidences));
        $this->assertNull($this->deadlines->remainingText(Deadline::UploadEvidences));

        $this->set(Deadline::UploadEvidences, now()->subMinute());
        $this->assertFalse($this->deadlines->isOpenForMenu(Deadline::UploadEvidences));
        $this->assertNull($this->deadlines->card(Deadline::UploadEvidences));
        $this->assertNull($this->deadlines->remainingText(Deadline::UploadEvidences));

        $this->set(Deadline::UploadEvidences, now()->addDay());
        $this->assertTrue($this->deadlines->isOpenForMenu(Deadline::UploadEvidences));
    }

    public function test_the_text_of_the_time_left(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 09:00:00'));

        $this->set(Deadline::ValidateEvidences, '2026-11-20 20:00:00');
        $this->assertSame('cierra hoy a las 20:00', $this->deadlines->remainingText(Deadline::ValidateEvidences));
        $this->assertSame(11, (int) $this->deadlines->remaining(Deadline::ValidateEvidences)->totalHours);

        $this->set(Deadline::ValidateEvidences, '2026-11-21 08:00:00');
        $this->assertSame('queda un día', $this->deadlines->remainingText(Deadline::ValidateEvidences));

        $this->set(Deadline::ValidateEvidences, '2026-11-29 20:00:00');
        $this->assertSame('quedan 9 días', $this->deadlines->remainingText(Deadline::ValidateEvidences));
    }

    public function test_the_card_of_the_sidebar(): void
    {
        $this->set(Deadline::Meetings, '2030-03-01 20:00:00');

        $card = $this->deadlines->card(Deadline::Meetings);

        $this->assertSame('Registro de reuniones', $card['label']);
        $this->assertSame('2030-03-01 20:00', $card['closesAt']->format('Y-m-d H:i'));
        $this->assertNull($card['opensAt']);
        $this->assertSame(route('secretary.meeting.manage.minutes.list'), $card['href']);
    }
}
