<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = User::factory()->create([
            'name' => 'Ana', 'surname' => 'García', 'email' => 'ana@alum.us.es', 'password' => Hash::make('secret123'),
            'biography' => '<p>Primer curso.</p>',
        ]);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->actingAs($this->student);
    }

    public function test_personal_data_records_only_what_changed_and_no_role(): void
    {
        $this->post(route('profile.upload.info'), ['name' => 'Ana', 'surname' => 'García', 'email' => 'ana.garcia@alum.us.es'])
            ->assertRedirect(route('profile.view'));

        $log = ActivityLog::sole();
        $this->assertSame('profile.edited', $log->action);
        $this->assertNull($log->acting_role);
        $this->assertSame($this->student->id, $log->subject_id);
        $this->assertSame(['before' => ['email' => 'ana@alum.us.es'], 'after' => ['email' => 'ana.garcia@alum.us.es']], $log->details);
    }

    public function test_the_biography_records_its_length_and_never_its_text(): void
    {
        $this->post(route('profile.upload.biography'), ['biography' => '<p>Cuarto curso de informática.</p>'])
            ->assertRedirect(route('profile.view'));

        $log = ActivityLog::sole();
        $this->assertSame([
            'fields' => ['biography'], 'length' => mb_strlen('Cuarto curso de informática.'),
        ], $log->details);
        $this->assertStringNotContainsString('Cuarto curso', json_encode($log->getAttributes()));
    }

    public function test_a_new_password_is_recorded_without_it(): void
    {
        $this->post(route('profile.upload.pass'), ['current_password' => 'secret123', 'password' => 'Nueva-Clave-26', 'password_confirmation' => 'Nueva-Clave-26'])
            ->assertRedirect(route('profile.view'));

        $log = ActivityLog::sole();
        $this->assertSame('user.password_changed', $log->action);
        $this->assertNull($log->details);
        $this->assertStringNotContainsString('Nueva-Clave-26', json_encode($log->getAttributes()));
    }
}
