<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use App\Services\Activity\ActivityPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The name and edition of the event belong to the course: the management sets them and the access page shows them.
 */
class EventNameTest extends TestCase
{
    use RefreshDatabase;

    private User $lecturer;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['id' => 1, 'event_name' => 'Jornadas InnoSoft Days', 'event_edition' => '2026']);
        $this->lecturer = User::factory()->create();
        $this->lecturer->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));
    }

    public function test_the_access_page_shows_the_name_and_the_edition_of_the_course(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('Jornadas InnoSoft Days')
            ->assertSeeInOrder(['Edición', '2026']);
    }

    public function test_the_access_page_works_without_a_named_course(): void
    {
        Configuration::query()->delete();

        $this->get(route('login'))->assertOk()
            ->assertDontSee('InnoSoft')
            ->assertDontSee('Edición');
    }

    public function test_the_management_renames_the_event_and_the_change_is_logged(): void
    {
        $this->save(['event_name' => '  Semana de la Ciencia ', 'event_edition' => '2027-28'])
            ->assertSessionHasNoErrors()->assertRedirect(route('lecture.config'));

        $configuration = Configuration::find(1);
        $this->assertSame('Semana de la Ciencia', $configuration->event_name);
        $this->assertSame('2027-28', $configuration->event_edition);
        $this->assertSame('Semana de la Ciencia 2027-28', Configuration::eventTitle());

        $entry = ActivityLog::query()->where('action', 'config.changed')->latest('id')->firstOrFail();
        $summary = (string) app(ActivityPresenter::class)->summary($entry);
        $this->assertStringContainsString('Evento «Jornadas InnoSoft Days» → «Semana de la Ciencia»', $summary);
        $this->assertStringContainsString('Edición «2026» → «2027-28»', $summary);

        auth()->logout();
        $this->get(route('login'))->assertSee('Semana de la Ciencia')->assertDontSee('InnoSoft');
    }

    public function test_the_edition_is_optional_and_the_name_is_required(): void
    {
        $this->save(['event_name' => 'Semana de la Ciencia', 'event_edition' => ''])->assertSessionHasNoErrors();
        $this->assertNull(Configuration::find(1)->event_edition);
        $this->assertSame('Semana de la Ciencia', Configuration::eventTitle());

        $this->save(['event_name' => ''])->assertSessionHasErrors('event_name');
        $this->save(['event_name' => str_repeat('a', 121)])->assertSessionHasErrors('event_name');
        $this->save(['event_name' => 'Jornadas', 'event_edition' => str_repeat('1', 21)])->assertSessionHasErrors('event_edition');
    }

    public function test_the_configuration_page_shows_the_event_fields(): void
    {
        $this->actingAs($this->lecturer)->get(route('lecture.config'))->assertOk()
            ->assertSee('Nombre del evento')
            ->assertSee('value="Jornadas InnoSoft Days"', false)
            ->assertSee('value="2026"', false);
    }

    private function save(array $event)
    {
        $date = now()->addWeek()->format('Y-m-d');

        return $this->actingAs($this->lecturer)->post(route('lecture.config.save'), $event + [
            'upload_evidences_date' => $date, 'upload_evidences_time' => '20:00',
            'validate_evidences_date' => $date, 'validate_evidences_time' => '21:00',
            'meetings_date' => $date, 'meetings_time' => '20:00',
            'bonus_date' => $date, 'bonus_time' => '20:00',
            'attendees_date' => $date, 'attendees_time' => '20:00',
        ]);
    }
}
