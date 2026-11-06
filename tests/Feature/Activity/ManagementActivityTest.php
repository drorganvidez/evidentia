<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ManagementActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $lecture;

    private Committee $logistica;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['secret' => 'salt']);
        $this->lecture = User::factory()->create(['username' => 'profesor']);
        $this->lecture->roles()->attach($this->role('LECTURE'));
        $this->logistica = Committee::create(['name' => 'Logística']);
        $this->actingAs($this->lecture);
    }

    public function test_editing_the_data_of_a_person_records_only_the_fields_that_changed(): void
    {
        $user = $this->student();

        $this->post(route('lecture.user.management.save'), ['name' => 'Lucía', 'email' => 'lucia@alum.us.es'] + $this->form($user, ['STUDENT']))
            ->assertSessionHas('success');

        $log = ActivityLog::sole();
        $this->assertSame('user.edited', $log->action);
        $this->assertSame('LECTURE', $log->acting_role);
        $this->assertSame($user->id, $log->target_user_id);
        $this->assertSame(['before' => ['name' => 'Ana', 'email' => 'ana@alum.us.es'], 'after' => ['name' => 'Lucía', 'email' => 'lucia@alum.us.es']], $log->details);
        $this->assertFalse($log->flagged);
    }

    public function test_changing_the_roles_records_them_with_the_committee(): void
    {
        $user = $this->student();

        $this->post(route('lecture.user.management.save'), $this->form($user, ['STUDENT', 'COORDINATOR']));

        $this->assertSame(['user.edited', 'user.roles_changed'], ActivityLog::orderBy('id')->pluck('action')->all());
        $roles = ActivityLog::where('action', 'user.roles_changed')->sole();
        $this->assertSame([
            'before' => ['roles' => ['STUDENT'], 'committee' => null],
            'after' => ['roles' => ['COORDINATOR', 'STUDENT'], 'committee' => 'Logística'],
        ], $roles->details);
        $this->assertSame(['before' => ['committee' => null], 'after' => ['committee' => 'Logística']], ActivityLog::where('action', 'user.edited')->sole()->details);
    }

    public function test_blocking_unblocking_and_a_new_password_are_recorded_apart(): void
    {
        $user = $this->student();

        $this->post(route('lecture.user.management.save'), ['pass' => null, 'password' => 'Nueva-Clave-26', 'password_confirmation' => 'Nueva-Clave-26'] + $this->form($user, ['STUDENT']));
        $this->post(route('lecture.user.management.save'), $this->form($user, ['STUDENT']));

        $this->assertSame(['user.blocked', 'user.password_changed_by_manager', 'user.unblocked'], ActivityLog::orderBy('id')->pluck('action')->all());
        $this->assertSame(1, ActivityLog::where('action', '!=', 'user.unblocked')->distinct()->count('request_id'));
        $this->assertStringNotContainsString('Nueva-Clave-26', ActivityLog::all()->toJson());
    }

    public function test_a_manager_who_edits_themselves_is_flagged(): void
    {
        $this->post(route('lecture.user.management.save'), ['name' => 'Otro nombre'] + $this->form($this->lecture, ['LECTURE']));

        $log = ActivityLog::sole();
        $this->assertSame('user.edited', $log->action);
        $this->assertSame(['role_overlap'], $log->flags);
    }

    public function test_saving_the_committees_records_only_the_ones_that_changed(): void
    {
        $programa = Committee::create(['name' => 'Programa', 'icon' => 'book']);

        $this->post(route('lecture.committee.management.save'), [
            'name_'.$this->logistica->id => 'Logística y montaje', 'icon_'.$this->logistica->id => null,
            'name_'.$programa->id => 'Programa', 'icon_'.$programa->id => 'book',
        ])->assertSessionHas('success');

        $log = ActivityLog::sole();
        $this->assertSame('committee.edited', $log->action);
        $this->assertSame($this->logistica->id, $log->subject_id);
        $this->assertSame(['before' => ['name' => 'Logística'], 'after' => ['name' => 'Logística y montaje']], $log->details);
    }

    public function test_a_removed_committee_keeps_its_name_and_a_refused_removal_records_nothing(): void
    {
        $empty = Committee::create(['name' => 'Sin uso']);
        Evidence::create(['title' => 'Mesas', 'hours' => 1, 'status' => 'PENDING', 'user_id' => $this->lecture->id, 'committee_id' => $this->logistica->id]);

        $this->post(route('lecture.committee.management.remove'), ['_id' => $this->logistica->id])->assertSessionHas('error');
        $this->post(route('lecture.committee.management.remove'), ['_id' => $empty->id])->assertSessionHas('success');

        $log = ActivityLog::sole();
        $this->assertSame('committee.deleted', $log->action);
        $this->assertSame('Sin uso', $log->subject_title);
        $this->assertSame('Sin uso', $log->committee_name);
    }

    public function test_deleting_every_user_records_each_one_with_the_copied_name(): void
    {
        $ana = $this->student();
        $pedro = User::factory()->create(['name' => 'Pedro', 'surname' => 'López', 'username' => 'pedlop']);

        $this->post(route('management.user.delete.all'))->assertSessionHas('success');

        $this->assertSame(1, User::count());
        $logs = ActivityLog::orderBy('target_name')->get();
        $this->assertSame(['user.deleted', 'user.deleted'], $logs->pluck('action')->all());
        $this->assertSame(['García, Ana', 'López, Pedro'], $logs->pluck('subject_title')->all());
        $this->assertSame(['anagar', 'pedlop'], $logs->pluck('target_username')->all());
        $this->assertSame([['bulk' => true, 'roles' => ['STUDENT']], ['bulk' => true, 'roles' => []]], $logs->pluck('details')->all());
        $this->assertSame(1, $logs->pluck('request_id')->unique()->count());
        $this->assertSame([$ana->id, $pedro->id], $logs->pluck('target_user_id')->all());
    }

    public function test_the_configuration_records_only_the_deadlines_that_changed(): void
    {
        $date = Carbon::parse('2026-12-01');
        Configuration::whereKey(1)->update([
            'upload_evidences_timestamp' => '2026-12-01 20:00:00', 'validate_evidences_timestamp' => '2026-12-01 23:59:00',
            'meetings_timestamp' => '2026-12-01 23:59:00', 'bonus_timestamp' => '2026-12-01 23:59:00', 'attendee_timestamp' => '2026-12-01 23:59:00',
        ]);

        $this->post(route('lecture.config.save'), [
            'upload_evidences_date' => '2026-12-08', 'upload_evidences_time' => '20:00',
            'validate_evidences_date' => '2026-12-08', 'validate_evidences_time' => '23:59',
            'meetings_date' => $date->format('Y-m-d'), 'meetings_time' => '23:59',
            'bonus_date' => $date->format('Y-m-d'), 'bonus_time' => '23:59',
            'attendees_date' => $date->format('Y-m-d'), 'attendees_time' => '23:59',
        ])->assertRedirect(route('lecture.config'));

        $log = ActivityLog::sole();
        $this->assertSame('config.changed', $log->action);
        $this->assertSame('Configuración del curso', $log->subject_title);
        $this->assertSame([
            'before' => ['upload_evidences_timestamp' => '2026-12-01 20:00', 'validate_evidences_timestamp' => '2026-12-01 23:59'],
            'after' => ['upload_evidences_timestamp' => '2026-12-08 20:00', 'validate_evidences_timestamp' => '2026-12-08 23:59'],
        ], $log->details);
    }

    public function test_an_import_records_the_new_people(): void
    {
        $existing = $this->student();
        Storage::fake();
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            ['apellidos', 'nombre', 'uvus', 'email'],
            ['Pérez López', 'Lucía', 'lucperlop', 'lucia@alum.us.es'],
            ['Martín', 'Pedro', 'pedmar', 'pedro@alum.us.es'],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        (new Xlsx($spreadsheet))->save($path);
        Storage::put('tmp/profesor/import-token/alumnos.xlsx', file_get_contents($path));
        unlink($path);

        $this->withSession(['_token' => 'import-token'])->post(route('lecture.import.save'))->assertRedirect(route('lecture.user.list'));

        $log = ActivityLog::sole();
        $this->assertSame('import.users', $log->action);
        $this->assertSame('LECTURE', $log->acting_role);
        $this->assertSame(['file' => 'alumnos.xlsx', 'created' => 2], $log->details);
        $targets = DB::table('activity_log_targets')->where('activity_log_id', $log->id)->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $this->assertEqualsCanonicalizing(User::whereIn('username', ['lucperlop', 'pedmar'])->pluck('id')->all(), $targets);
        $this->assertNotContains($existing->id, $targets);
    }

    public function test_a_new_person_is_recorded_without_a_password(): void
    {
        $this->post(route('management.user.new'), ['name' => 'Lucía', 'surname' => 'Pérez', 'email' => 'lucia@alum.us.es', 'username' => 'lucper']);

        $user = User::where('username', 'lucper')->sole();
        $log = ActivityLog::sole();
        $this->assertSame('user.created', $log->action);
        $this->assertSame('LECTURE', $log->acting_role);
        $this->assertSame($user->id, $log->target_user_id);
        $this->assertSame(['username' => 'lucper', 'roles' => ['STUDENT']], $log->details);
    }

    public function test_exports_and_randomizing_are_recorded(): void
    {
        $level = ob_get_level();
        ob_start(); // the export closes the active output buffer
        $this->post(route('lecture.export.save'), ['evidences' => 'on', 'events' => 'on']);
        while (ob_get_level() > $level) {
            ob_end_clean();
        }
        $this->post(route('randomize.save'))->assertSessionHas('success');

        [$export, $randomize] = ActivityLog::orderBy('id')->get()->all();
        $this->assertSame(['sections' => ['Evidencias', 'Eventos']], $export->details);
        $this->assertSame('randomize.run', $randomize->action);
        $this->assertSame(['count' => 0], $randomize->details);
    }

    private function student(): User
    {
        $user = User::factory()->create(['name' => 'Ana', 'surname' => 'García', 'username' => 'anagar', 'email' => 'ana@alum.us.es']);
        $user->roles()->attach($this->role('STUDENT'));

        return $user;
    }

    private function role(string $name): Role
    {
        return Role::where('rol', $name)->first() ?? Role::forceCreate(['rol' => $name, 'slug' => strtolower($name)]);
    }

    private function form(User $user, array $roles): array
    {
        return [
            'user_id' => $user->id,
            'pass' => 'on',
            'roles' => array_map(fn ($role) => $this->role($role)->id, $roles),
            'committee' => $this->logistica->id,
            'username' => $user->username,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
        ];
    }
}
