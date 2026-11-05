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
use Tests\TestCase;

/**
 * Real .xlsx files: the exports are downloaded and read back.
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
