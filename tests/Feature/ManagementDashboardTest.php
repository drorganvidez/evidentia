<?php

namespace Tests\Feature;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\User;
use App\View\ManagementDashboard;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The figures of the control panel, checked against the end-to-end seed (five students, one committee with work). */
class ManagementDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
    }

    private function panel(string $who = 'profesor'): array
    {
        return app(ManagementDashboard::class)->for(User::where('username', $who)->sole());
    }

    public function test_the_figures_add_every_origin_of_the_hours(): void
    {
        $kpis = $this->panel()['kpis'];

        // Lucía 2 accepted + 2 meeting + 1,5 event + 3 bonus; Marta 2 meeting; Pablo 1,5 event
        $this->assertEqualsWithDelta(12.0, $kpis['hours'], 0.001);
        $this->assertSame(5, $kpis['students']);
        $this->assertSame(2, $kpis['participating']);
        $this->assertSame(40, $kpis['participation']);
        $this->assertSame(2, $kpis['pending']);
        $this->assertSame(50, $kpis['acceptance']);
        $this->assertSame(1, $kpis['rejected']);
    }

    public function test_the_hours_spread_in_bands_and_by_origin(): void
    {
        $panel = $this->panel();

        $this->assertSame([2, 2, 1, 0, 0, 0], array_column($panel['distribution'], 'value'));
        $this->assertSame(['Evidencias', 'Reuniones', 'Asistencia', 'Bonos'], array_column($panel['origins'], 'label'));
        $this->assertSame(100, array_sum(array_column($panel['origins'], 'value')));
        $this->assertSame('3,00 h', $panel['origins'][3]['text']);
    }

    public function test_each_committee_shows_its_coordination_and_its_queue(): void
    {
        $committees = $this->panel()['committees']->keyBy('name');

        $this->assertSame('Carlos Navarro Ruiz', $committees['Logística']['coordinators']);
        $this->assertSame(2, $committees['Logística']['pending']);
        $this->assertSame(4, $committees['Logística']['total']);
        $this->assertSame('', $committees['Comunicación']['coordinators']);
        $this->assertSame(0, $committees['Comunicación']['total']);
    }

    public function test_the_traceability_reads_the_activity_log(): void
    {
        $lecturer = User::where('username', 'profesor')->sole();
        $student = User::where('username', 'estudiante')->sole();
        $this->actingAs($student);
        Activity::record(ActivityAction::Login);
        Activity::record(ActivityAction::LoginFailed);

        $panel = $this->panel();

        $this->assertSame(1, $panel['access']['logins']);
        $this->assertSame(1, $panel['access']['failed']);
        $this->assertTrue($panel['activity']->doesntContain('action', 'login'), 'The accesses stay out of the latest actions');
        $this->assertSame(['Autorrevisiones', 'Fuera de plazo', 'Doble rol'], array_column($panel['signals'], 'label'));
        $this->assertNotNull($this->panel('profesor')['serverErrors']);
        $this->assertSame('president.errors.show', $this->panel('presidente')['serverErrors']['show']);
        $this->assertNotNull($lecturer);
    }

    public function test_the_weeks_end_with_the_current_one(): void
    {
        $weeks = $this->panel()['weeks'];

        $this->assertCount(8, $weeks);
        $this->assertSame('Esta semana', $weeks[7]['label']);
        $this->assertSame(4, array_sum(array_column($weeks, 'value')), 'The four evidences sent by the seed this week');
    }
}
