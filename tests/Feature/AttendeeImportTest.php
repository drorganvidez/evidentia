<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attendee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class AttendeeImportTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['attendee_timestamp' => now()->addMonth()]);
        $this->coordinator = User::factory()->create(['username' => 'regcoo']);
        $this->coordinator->roles()->attach(Role::forceCreate(['rol' => 'REGISTER_COORDINATOR', 'slug' => 'register_coordinator']));
        $this->event = Event::forceCreate(['name' => 'Charla IA', 'hours' => 2]);
        $this->actingAs($this->coordinator)->withSession(['_token' => 'hoja']);
    }

    public function test_every_person_of_the_sheet_is_marked_as_attended_by_uvus_or_mail(): void
    {
        $ana = User::factory()->create(['username' => 'anagar']);
        $pedro = User::factory()->create(['email' => 'pedro@alum.us.es']);
        $carla = User::factory()->create(['username' => 'carrui']);
        Attendee::create(['event_id' => $this->event->id, 'user_id' => $carla->id, 'status' => 'Not Attending']);
        $this->sheet([['uvus', 'correo'], ['AnaGar', ''], ['', 'pedro@alum.us.es'], ['carrui', ''], ['nadie', ''], ['regcoo', ''], ['', '']]);

        $response = $this->post(route('attendee.import.save'), ['event_id' => $this->event->id]);

        $response->assertRedirect(route('registercoordinator.attendee.list'));
        $message = session('success');
        $this->assertStringContainsString('2 nuevas y 1 actualizadas', $message);
        $this->assertStringContainsString('Sin alumno en Evidentia nadie.', $message);
        $this->assertStringContainsString('Tu propia asistencia no se importa.', $message);
        foreach ([$ana, $pedro, $carla] as $person) {
            $this->assertDatabaseHas('attendees', ['event_id' => $this->event->id, 'user_id' => $person->id, 'status' => 'Checked In']);
        }
        $this->assertDatabaseMissing('attendees', ['user_id' => $this->coordinator->id]);
        $log = ActivityLog::where('action', 'attendee.imported')->sole();
        $this->assertSame(['created' => 2, 'updated' => 1, 'unmatched' => 1], array_intersect_key($log->details, array_flip(['created', 'updated', 'unmatched'])));
        $this->assertSame([], Storage::files('tmp/regcoo/hoja'));
    }

    public function test_without_a_sheet_or_with_an_empty_one_nothing_changes(): void
    {
        $this->post(route('attendee.import.save'), ['event_id' => $this->event->id])
            ->assertSessionHas('error', 'Sube primero la hoja con las asistencias.');

        $this->sheet([['nombre'], ['Ana']]);
        $this->post(route('attendee.import.save'), ['event_id' => $this->event->id])
            ->assertSessionHas('error', 'La hoja no tiene ninguna fila con uvus o correo.');

        $this->assertSame(0, Attendee::count());
        $this->assertSame(0, ActivityLog::where('action', 'attendee.imported')->count());
    }

    public function test_the_page_is_offered_from_the_attendances(): void
    {
        $this->get(route('registercoordinator.attendee.list'))->assertOk()->assertSee(route('attendee.import'), false);
        $this->get(route('attendee.import'))->assertOk()->assertSee('Charla IA');
    }

    public function test_a_closed_deadline_or_another_role_cannot_import(): void
    {
        $student = User::factory()->create();
        $this->actingAs($student)->get(route('attendee.import'))->assertNotFound();

        Configuration::query()->update(['attendee_timestamp' => now()->subDay()]);
        $this->sheet([['uvus'], [$student->username]]);
        $this->actingAs($this->coordinator)->post(route('attendee.import.save'), ['event_id' => $this->event->id]);
        $this->assertSame(0, Attendee::count());
    }

    private function sheet(array $rows): void
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray($rows);
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        (new Xlsx($spreadsheet))->save($path);
        Storage::put('tmp/regcoo/hoja/asistencias.xlsx', file_get_contents($path));
        unlink($path);
    }
}
