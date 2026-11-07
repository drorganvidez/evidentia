<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attendee;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_downloads_the_same_certificate_of_an_attended_event(): void
    {
        $student = $this->user('STUDENT', ['name' => 'Lucía', 'surname' => 'Pérez Romero']);
        $event = Event::forceCreate(['name' => 'Taller de <b>Git</b>', 'hours' => 1.5, 'start_datetime' => '2026-10-20 09:00']);
        Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $student->id, 'status' => Attendee::ATTENDED]);

        $this->actingAs($student)->get(route('attendee.list'))
            ->assertSee(route('certificate.attendance', $event->id));

        $response = $this->get(route('certificate.attendance', $event->id))->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $certificate = Certificate::sole();
        $this->assertSame(Certificate::ATTENDANCE, $certificate->type);
        $this->assertSame('Lucía Pérez Romero', $certificate->recipient_name);
        $this->assertSame('Taller de Git', $certificate->title);
        $this->assertSame(1.5, $certificate->hours);
        $this->assertMatchesRegularExpression('/^[2-9A-Z]{4}-[2-9A-Z]{4}-[2-9A-Z]{4}$/', $certificate->code);

        $this->get(route('certificate.attendance', $event->id))->assertOk();
        $this->assertSame(1, Certificate::count());
    }

    public function test_no_certificate_without_a_confirmed_attendance(): void
    {
        $student = $this->user('STUDENT');
        $event = Event::forceCreate(['name' => 'Charla', 'hours' => 1]);
        Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $student->id, 'status' => 'Attending']);

        $this->actingAs($student)->get(route('certificate.attendance', $event->id))->assertNotFound();
        $this->assertSame(0, Certificate::count());
    }

    public function test_the_management_issues_a_diploma_that_the_recipient_downloads(): void
    {
        $president = $this->user('PRESIDENT');
        $student = $this->user('STUDENT', ['name' => 'Juan', 'surname' => 'García López']);

        $this->actingAs($president)->post(route('certificate.store'), ['title' => '', 'issued_at' => ''])
            ->assertSessionHasErrors(['title', 'issued_at', 'recipient_name']);

        $this->post(route('certificate.store'), [
            'user_id' => $student->id, 'title' => 'ganar el concurso de carteles', 'hours' => 2, 'issued_at' => '2026-11-07',
        ])->assertRedirect(route('certificate.index'));

        $diploma = Certificate::sole();
        $this->assertSame(Certificate::DIPLOMA, $diploma->type);
        $this->assertSame('Juan García López', $diploma->recipient_name);
        $this->assertSame($president->id, $diploma->issued_by);
        $this->assertTrue(ActivityLog::where('action', 'certificate.issued')->exists());

        $this->get(route('certificate.index'))->assertOk()->assertSee($diploma->code);

        $this->actingAs($student)->get(route('attendee.list'))->assertSee('ganar el concurso de carteles');
        $this->get(route('certificate.download', $diploma->code))->assertOk();
        $this->actingAs($this->user('STUDENT'))->get(route('certificate.download', $diploma->code))->assertNotFound();
    }

    public function test_a_diploma_for_somebody_outside_the_platform(): void
    {
        $this->actingAs($this->user('LECTURE'))->post(route('certificate.store'), [
            'recipient_name' => 'Ada Ponente', 'title' => 'impartir la charla de apertura', 'issued_at' => '2026-11-07',
        ])->assertRedirect();

        $this->assertNull(Certificate::sole()->user_id);
        $this->assertSame('Ada Ponente', Certificate::sole()->recipient_name);
    }

    public function test_only_the_management_lists_and_issues_diplomas(): void
    {
        $this->actingAs($this->user('STUDENT'));
        $this->get(route('certificate.index'))->assertNotFound();
        $this->post(route('certificate.store'), ['recipient_name' => 'X', 'title' => 'Y', 'issued_at' => '2026-11-07']);
        $this->assertSame(0, Certificate::count());
    }

    public function test_anybody_verifies_a_code_and_a_revoked_one_stops_being_valid(): void
    {
        $diploma = Certificate::create(['type' => Certificate::DIPLOMA, 'recipient_name' => 'Ada Ponente', 'title' => 'la charla de apertura']);

        $this->get(route('certificate.verify', strtolower($diploma->code)))
            ->assertOk()->assertSee('Certificado válido')->assertSee('Ada Ponente');
        $this->get(route('certificate.verify', ['code' => 'AAAA-BBBB-CCCC']))->assertOk()->assertSee('Código no válido');

        $this->actingAs($this->user('PRESIDENT'))->delete(route('certificate.destroy', $diploma->id))
            ->assertRedirect(route('certificate.index'));
        $this->assertTrue(ActivityLog::where('action', 'certificate.revoked')->exists());

        auth()->logout();
        $this->get(route('certificate.verify', $diploma->code))->assertSee('Código no válido');
    }

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));

        return $user;
    }
}
