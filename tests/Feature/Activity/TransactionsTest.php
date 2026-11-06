<?php

namespace Tests\Feature\Activity;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\CreatesAppFixtures;
use Tests\TestCase;

/**
 * The annotation is the last write of each transaction: when it fails, the whole action rolls back and no row is left
 * half written.
 */
class TransactionsTest extends TestCase
{
    use CreatesAppFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The failed annotation is a server error: the error recorder tells the lecturers, never through these tables
        Notification::fake();
        $this->createAppFixtures();
    }

    public static function actions(): array
    {
        return [
            'evidencia con prueba' => ['STUDENT', 'evidence.publish', ActivityAction::EvidencePublished, fn (array $ids) => [
                'title' => 'Montaje del escenario', 'hours' => 2, 'minutes' => 0, 'committee' => $ids['logistica'],
                'description' => 'Monté el escenario del congreso con el equipo.',
            ]],
            'convocatoria con hoja' => ['SECRETARY', 'secretary.meeting.manage.request.new', ActivityAction::SheetCreated, fn (array $ids) => [
                'title' => 'Reunión de montaje', 'place' => 'Aula A3.10', 'date' => now()->addWeek()->format('Y-m-d'), 'time' => '18:00',
                'type' => 1, 'modality' => 1, 'points_list' => json_encode(['Reparto de tareas']), 'create_signature_sheet' => 'on',
            ]],
            'acta' => ['SECRETARY', 'secretary.meeting.manage.minutes.create.step3_p', ActivityAction::MinutesCreated, fn (array $ids) => [
                'title' => 'Reunión de montaje', 'type' => 1, 'modality' => 1, 'hours' => 2, 'place' => 'Aula A3.10',
                'date' => now()->format('Y-m-d'), 'time' => '16:30', 'users' => [$ids['student']], 'meeting_request' => $ids['request'],
                'points_json' => json_encode([['title' => 'Reparto', 'duration' => 10, 'description' => 'Equipos', 'agreements' => [['description' => 'Turnos.']]]]),
            ]],
            'bono' => ['SECRETARY', 'secretary.bonus.new', ActivityAction::BonusCreated, fn (array $ids) => [
                'reason' => 'Montaje del escenario', 'hours' => 3, 'users' => [$ids['student']],
            ]],
            'edición de usuario' => ['LECTURE', 'lecture.user.management.save', ActivityAction::UserPasswordChangedByManager, fn (array $ids) => [
                'user_id' => $ids['student'], 'roles' => Role::whereIn('rol', ['STUDENT', 'COORDINATOR'])->pluck('id')->all(),
                'committee' => $ids['programa'], 'username' => 'nuevo-uvus', 'name' => 'Lucía', 'surname' => 'Pérez',
                'email' => 'lucia@alum.us.es', 'password' => 'Nueva-Clave-26', 'password_confirmation' => 'Nueva-Clave-26',
            ]],
        ];
    }

    #[DataProvider('actions')]
    public function test_a_failed_annotation_rolls_back_the_whole_action(string $role, string $route, ActivityAction $last, \Closure $data): void
    {
        $student = $this->users['STUDENT'];
        Storage::put('tmp/'.$student->username.'/transacciones/certificado.pdf', '%PDF-1.4');
        $form = $data($this->ids);
        $before = $this->databaseSnapshot();
        Activity::fake()->failOn($last);

        $this->actingAs($this->users[$role])->withSession(['_token' => 'transacciones'])
            ->post(route($route), $form)
            ->assertServerError();

        $this->assertSame($before, $this->databaseSnapshot());
        $this->assertTrue(User::find($this->ids['student'])->hasRole('STUDENT'));
    }
}
