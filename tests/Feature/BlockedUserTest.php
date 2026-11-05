<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BlockedUserTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['upload_evidences_timestamp' => now()->addWeek()]);
        $this->student = User::factory()->create(['email' => 'ana@us.es', 'password' => Hash::make('secret123'), 'block' => true]);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
    }

    public function test_a_blocked_user_only_sees_the_blocked_page(): void
    {
        $this->post(route('login'), ['email' => 'ana@us.es', 'password' => 'secret123']);

        $this->get(route('home'))->assertRedirect(route('block'));
        $this->get(route('evidence.create'))->assertRedirect(route('block'));
        $this->get(route('block'))->assertOk();

        $this->post(route('logout'));
        $this->assertGuest();
    }

    public function test_an_unblocked_user_is_sent_home_from_the_blocked_page(): void
    {
        $this->student->update(['block' => false]);

        $this->actingAs($this->student)->get(route('block'))->assertRedirect(route('home'));
    }

    public function test_a_blocked_user_cannot_sign_a_signature_sheet(): void
    {
        $secretary = User::factory()->create();
        $sheet = SignatureSheet::create([
            'title' => 'Hoja de firmas',
            'random_identifier' => '7391',
            'secretary_id' => Secretary::forceCreate([
                'user_id' => $secretary->id,
                'committee_id' => Committee::create(['name' => 'Logística'])->id,
            ])->id,
        ]);

        $this->post(route('sign_p'), ['signature_sheet' => '7391', 'email' => 'ana@us.es', 'password' => 'secret123'])
            ->assertSessionHas('error');

        $this->assertFalse($sheet->users()->whereKey($this->student->id)->exists());
        $this->assertGuest();
    }
}
