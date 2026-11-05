<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Real .xlsx files in and out: the import reads a spreadsheet written here and the exports are read back.
 */
class SpreadsheetTest extends TestCase
{
    use RefreshDatabase;

    private User $lecture;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['secret' => 'salt']);
        $this->lecture = User::factory()->create();
        $this->lecture->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));
        $this->student = User::factory()->create(['name' => 'Ana', 'surname' => 'García', 'username' => 'anagar']);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
    }

    public function test_the_lecturer_imports_students_from_a_spreadsheet(): void
    {
        $this->storeUpload('alumnos.xlsx', [
            ['apellidos', 'nombre', 'uvus', 'email'],
            [' Pérez López ', 'Lucía', 'lucperlop', 'lucia@alum.us.es'],
            ['Martín', 'Pedro', 'pedmar', 'pedro@alum.us.es'],
            [null, null, null, null],
        ]);

        $this->post(route('lecture.import.save'))
            ->assertRedirect(route('lecture.user.list'))
            ->assertSessionHas('success');

        $lucia = User::where('username', 'lucperlop')->sole();
        $this->assertSame(['Pérez López', 'Lucía', 'lucia@alum.us.es'], [$lucia->surname, $lucia->name, $lucia->email]);
        $this->assertSame('PEREZLOPEZ', $lucia->clean_surname);
        $this->assertTrue($lucia->hasRole('STUDENT'));
        $this->assertTrue(User::where('username', 'pedmar')->sole()->hasRole('STUDENT'));
        $this->assertSame(4, User::count());
    }

    public function test_importing_without_a_file_says_so(): void
    {
        $this->actingAs($this->lecture)->withSession(['_token' => 'import-token'])
            ->post(route('lecture.import.save'))
            ->assertRedirect(route('lecture.import'))
            ->assertSessionHas('error');

        $this->assertSame(2, User::count());
    }

    public function test_the_final_hours_export_downloads_a_spreadsheet(): void
    {
        $committee = Committee::create(['name' => 'Programa']);
        Evidence::forceCreate([
            'title' => 'Organización de mesas', 'hours' => 2, 'status' => 'ACCEPTED',
            'user_id' => $this->student->id, 'committee_id' => $committee->id,
        ]);

        $rows = $this->download(route('lecture.export.save'), ['evidences' => 'on', 'events' => 'on', 'meetings' => 'on', 'bonus' => 'on']);

        $this->assertSame('Apellidos', $rows[0][0]);
        $ana = collect($rows)->first(fn ($row) => $row[2] === 'anagar');
        $this->assertEquals(2, $ana[array_search('Horas de evidencias', $rows[0])]);
        $this->assertSame(0, $ana[array_search('Horas de reuniones', $rows[0])]);
        $this->assertEquals(2, $ana[array_search('Horas en total', $rows[0])]);
    }

    public function test_the_attendances_export_downloads_a_spreadsheet(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'register_coordinator']));
        $event = Event::forceCreate(['name' => 'Charla IA', 'id_eventbrite' => '111', 'hours' => 2]);
        Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $this->student->id, 'status' => 'Checked In']);
        $this->actingAs($coordinator);

        $rows = $this->download(route('registercoordinator.attendee.export'));

        $this->assertSame(['Apellidos', 'Nombre', 'Uvus', 'Horas de asistencia'], $rows[0]);
        $this->assertContains(['GARCÍA', 'ANA', 'anagar', 2], $rows);
        $this->assertSame(0, collect($rows)->firstWhere(2, $coordinator->username)[3]);
    }

    private function storeUpload(string $name, array $rows): void
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows);
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        (new Xlsx($spreadsheet))->save($path);

        // where the upload widget leaves the file for the current session
        $this->actingAs($this->lecture)->withSession(['_token' => 'import-token']);
        Storage::put('tmp/'.$this->lecture->username.'/import-token/'.$name, file_get_contents($path));
        unlink($path);
    }

    private function download(string $url, array $data = []): array
    {
        $this->actingAs(auth()->user() ?? $this->lecture);
        $level = ob_get_level();
        ob_start(); // the export closes the active output buffer
        $response = $data ? $this->post($url, $data) : $this->get($url);
        while (ob_get_level() > $level) {
            ob_end_clean();
        }

        $response->assertOk();

        return IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet()->toArray(null, true, false);
    }
}
