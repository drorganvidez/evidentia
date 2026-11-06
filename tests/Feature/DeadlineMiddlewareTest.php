<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckRegisterBonus;
use App\Http\Middleware\CheckRegisterEventsAndAttendings;
use App\Http\Middleware\CheckRegisterMeeting;
use App\Http\Middleware\CheckUploadEvidences;
use App\Http\Middleware\CheckValidateEvidences;
use App\Models\Configuration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** The five deadline gates: open until the date, closed after it, and open when the date is not set. */
class DeadlineMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private const GATES = [
        'upload_evidences_timestamp' => CheckUploadEvidences::class,
        'validate_evidences_timestamp' => CheckValidateEvidences::class,
        'meetings_timestamp' => CheckRegisterMeeting::class,
        'bonus_timestamp' => CheckRegisterBonus::class,
        'attendee_timestamp' => CheckRegisterEventsAndAttendings::class,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        foreach (self::GATES as $column => $middleware) {
            Route::middleware($middleware)->get("/_gate/$column", fn () => 'abierto');
        }
    }

    public function test_each_gate_follows_its_own_date_and_an_unset_date_stays_open(): void
    {
        Configuration::forceCreate(['id' => 1]);

        foreach (array_keys(self::GATES) as $column) {
            $this->get("/_gate/$column")->assertOk()->assertSee('abierto');

            Configuration::query()->update([$column => now()->addDay()]);
            $this->get("/_gate/$column")->assertOk();

            Configuration::query()->update([$column => now()->subMinute()]);
            $this->get("/_gate/$column")->assertNotFound();

            Configuration::query()->update([$column => null]);
        }
    }

    public function test_without_a_configuration_row_the_gates_stay_open(): void
    {
        foreach (array_keys(self::GATES) as $column) {
            $this->get("/_gate/$column")->assertOk();
        }
    }
}
