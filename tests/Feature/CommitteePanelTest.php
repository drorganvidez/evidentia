<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\User;
use App\View\CommitteePanel;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The figures of «Mi comité» over the seed of the end-to-end tests: Logística has Lucía (one evidence in each state,
 * the meeting and the bonus), Juan (one pending), Marta (the meeting) and Inés, its second coordinator, whose pending
 * evidence waits in the queue of the presidency; Comunicación is empty.
 */
class CommitteePanelTest extends TestCase
{
    use RefreshDatabase;

    private Committee $logistics;

    private Committee $communication;

    private User $coordinator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
        $this->logistics = Committee::where('name', 'Logística')->sole();
        $this->communication = Committee::where('name', 'Comunicación')->sole();
        $this->coordinator = $this->person('coordinador');
    }

    private function person(string $username): User
    {
        return User::where('username', $username)->sole();
    }

    private function panel(): array
    {
        return app(CommitteePanel::class)->for($this->logistics, $this->coordinator);
    }

    public function test_the_figures_of_the_seed(): void
    {
        $panel = $this->panel();

        $this->assertSame([
            'pending' => 2,
            'pendingDays' => 0,
            'delegated' => 1,
            'acceptedHours' => 2.0,
            'accepted' => 1,
            'hours' => 9.0,
            'rejected' => 1,
            'members' => 4,
        ], $panel['kpis']);
        $this->assertSame(['Carlos', 'Inés'], $panel['coordinators']->pluck('name')->all());
        $this->assertSame(['Marta'], $panel['secretaries']->pluck('name')->all());
    }

    public function test_each_member_with_their_evidences_hours_and_meetings(): void
    {
        $members = $this->panel()['members']->map(fn (array $member): array => array_intersect_key(
            $member,
            array_flip(['username', 'accepted', 'pending', 'rejected', 'hours', 'meetings']),
        ))->all();

        $this->assertSame([
            ['username' => 'coordinadora', 'accepted' => 0, 'pending' => 1, 'rejected' => 0, 'hours' => 0.0, 'meetings' => 0],
            ['username' => 'companero', 'accepted' => 0, 'pending' => 1, 'rejected' => 0, 'hours' => 0.0, 'meetings' => 0],
            ['username' => 'estudiante', 'accepted' => 1, 'pending' => 1, 'rejected' => 1, 'hours' => 7.0, 'meetings' => 1],
            ['username' => 'secretario', 'accepted' => 0, 'pending' => 0, 'rejected' => 0, 'hours' => 2.0, 'meetings' => 1],
        ], $members);
    }

    public function test_the_latest_activity_of_a_member_is_their_latest_evidence_meeting_or_bonus(): void
    {
        $members = $this->panel()['members']->keyBy('username');

        $this->assertTrue($members['secretario']['last']->isSameDay(now()->subDays(3)));
        $this->assertTrue($members['estudiante']['last']->isToday());
    }

    public function test_the_distribution_and_the_weeks(): void
    {
        $panel = $this->panel();

        $this->assertSame([
            ['label' => 'Lucía Pérez Romero', 'value' => 7.0],
            ['label' => 'Marta Ruiz Díaz', 'value' => 2.0],
        ], $panel['distribution']);
        $this->assertCount(8, $panel['weeks']);
        $this->assertSame(['label' => 'Esta semana', 'value' => 5], $panel['weeks'][7]);
        $this->assertSame(5, array_sum(array_column($panel['weeks'], 'value')));
    }

    public function test_the_meetings_of_the_committee(): void
    {
        $panel = $this->panel();

        $this->assertSame(['Convocatoria de logística'], $panel['requests']->pluck('title')->all());
        $this->assertSame(['Reunión de logística'], $panel['minutes']->map(fn ($minutes) => $minutes->meeting->title)->all());
    }

    public function test_the_evidences_of_the_coordinators_count_but_wait_in_the_presidency(): void
    {
        Evidence::create([
            'title' => 'Recuento de sillas', 'description' => '<p>Recuento.</p>', 'hours' => 1.5, 'status' => 'PENDING',
            'user_id' => $this->coordinator->id, 'committee_id' => $this->logistics->id,
        ]);

        $panel = $this->panel();

        $this->assertSame(2, $panel['kpis']['pending']);
        $this->assertSame(2, $panel['kpis']['delegated']);
        $this->assertSame(5, $panel['kpis']['members']);
        $own = $panel['members']->firstWhere('username', 'coordinador');
        $this->assertSame(1, $own['pending']);
    }

    public function test_the_queue_tells_how_long_the_oldest_pending_has_waited(): void
    {
        DB::table('evidences')->where('title', 'Atención en el registro')->update(['updated_at' => now()->subDays(5)->subHour()]);

        $this->assertSame(5, $this->panel()['kpis']['pendingDays']);

        Evidence::where('status', 'PENDING')->delete();
        $this->assertNull($this->panel()['kpis']['pendingDays']);
    }

    public function test_nothing_of_another_committee_leaks_in(): void
    {
        $companero = $this->person('companero');
        Evidence::create([
            'title' => 'Notas de prensa', 'description' => '<p>Notas.</p>', 'hours' => 5, 'status' => 'ACCEPTED',
            'user_id' => $companero->id, 'committee_id' => $this->communication->id,
        ]);
        $meeting = Meeting::forceCreate([
            'title' => 'Reunión de comunicación', 'datetime' => now()->subDay(), 'place' => 'Aula B1', 'type' => '1',
            'modality' => '1', 'hours' => 4, 'committee_id' => $this->communication->id,
        ]);
        $meeting->users()->attach($this->person('estudiante'));
        $bonus = Bonus::create(['reason' => 'Difusión', 'hours' => 6]);
        $bonus->committee()->associate($this->communication)->save();
        $bonus->users()->attach($this->person('registro'));
        Evidence::create([
            'title' => 'Borrador ajeno', 'description' => '<p>Borrador.</p>', 'hours' => 2, 'status' => 'DRAFT',
            'user_id' => $this->person('registro')->id, 'committee_id' => $this->logistics->id,
        ]);

        $panel = $this->panel();

        $this->assertSame(['coordinadora', 'companero', 'estudiante', 'secretario'], $panel['members']->pluck('username')->all());
        $this->assertSame(9.0, $panel['kpis']['hours']);
        $this->assertSame(1, $panel['members']->firstWhere('username', 'estudiante')['meetings']);
        $this->assertSame(0.0, $panel['members']->firstWhere('username', 'companero')['hours']);
    }

    public function test_the_activity_is_the_committee_one_without_accesses_nor_notices(): void
    {
        // Only the annotations of this test: the end-to-end seed also leaves a history of its own
        DB::table('activity_log_targets')->delete();
        DB::table('activity_log')->delete();
        $base = ['created_at' => now(), 'user_id' => $this->coordinator->id, 'acting_role' => 'COORDINATOR'];
        ActivityLog::create($base + ['action' => 'evidence.accepted', 'committee_id' => $this->logistics->id, 'committee_name' => 'Logística']);
        ActivityLog::create($base + ['action' => 'evidence.rejected', 'committee_id' => $this->communication->id, 'committee_name' => 'Comunicación']);
        ActivityLog::create($base + ['action' => 'notification.sent', 'committee_id' => $this->logistics->id]);
        ActivityLog::create($base + ['action' => 'notification.failed', 'committee_id' => $this->logistics->id]);
        ActivityLog::create($base + ['action' => 'login']);

        $this->assertSame(['evidence.accepted'], $this->panel()['activity']->pluck('action')->all());
    }

    public function test_the_csv_has_a_bom_semicolons_and_one_row_per_member(): void
    {
        $csv = app(CommitteePanel::class)->csv(app(CommitteePanel::class)->members($this->logistics));

        $this->assertStringStartsWith("\u{FEFF}nombre;apellidos;uvus;correo;aceptadas;pendientes;rechazadas;horas;reuniones\n", $csv);
        $lines = explode("\n", trim(substr($csv, 3)));
        $this->assertCount(5, $lines);
        $this->assertSame('Lucía;"Pérez Romero";estudiante;estudiante@e2e.test;1;1;1;7,00;1', $lines[3]);
        $this->assertSame('Juan;"García López";companero;companero@e2e.test;0;1;0;0,00;0', $lines[2]);
    }

    public function test_the_csv_keeps_formulas_as_text(): void
    {
        $intruder = User::factory()->create(['name' => '=HYPERLINK("http://x")', 'surname' => '+Suma', 'username' => '@uvus', 'email' => '-correo@e2e.test']);
        Evidence::create([
            'title' => 'Evidencia', 'description' => '<p>Texto.</p>', 'hours' => 1, 'status' => 'PENDING',
            'user_id' => $intruder->id, 'committee_id' => $this->logistics->id,
        ]);

        $csv = app(CommitteePanel::class)->csv(app(CommitteePanel::class)->members($this->logistics));

        $this->assertStringContainsString('"\'=HYPERLINK(""http://x"")";\'+Suma;\'@uvus;\'-correo@e2e.test;0;1;0;0,00;0', $csv);
        $this->assertSame("'=1", CommitteePanel::cell('=1'));
        $this->assertSame("'\t=1", CommitteePanel::cell("\t=1"));
        $this->assertSame('Lucía', CommitteePanel::cell('Lucía'));
        $this->assertSame('7,00', CommitteePanel::cell('7,00'));
    }
}
