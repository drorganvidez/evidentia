<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SignTest extends TestCase
{
    use RefreshDatabase;

    private SignatureSheet $sheet;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $secretary = User::factory()->create();
        $secretary->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $this->sheet = SignatureSheet::create([
            'title' => 'Hoja de firmas',
            'random_identifier' => '7391',
            'secretary_id' => Secretary::forceCreate([
                'user_id' => $secretary->id,
                'committee_id' => Committee::create(['name' => 'Logística'])->id,
            ])->id,
        ]);
        $this->student = User::factory()->create(['email' => 'ana@us.es', 'password' => Hash::make('secret123')]);
    }

    public function test_a_student_signs_through_the_sheet_link(): void
    {
        $this->get(route('sign', ['random_identifier' => '7391']))->assertOk();

        $this->sign('7391')->assertRedirect(route('sign.finish'));

        $this->assertTrue($this->sheet->users()->whereKey($this->student->id)->exists());
        $this->assertGuest();
    }

    public function test_the_sheet_id_is_not_enough_to_sign(): void
    {
        $this->sign((string) $this->sheet->id);

        $this->assertFalse($this->sheet->users()->whereKey($this->student->id)->exists());
    }

    public function test_password_guesses_are_limited_like_on_the_login_page(): void
    {
        foreach (range(1, 5) as $guess) {
            $this->sign('7391', "wrong-$guess")->assertSessionHas('error');
        }

        $this->sign('7391')->assertSessionHas('error');

        $this->assertFalse($this->sheet->users()->whereKey($this->student->id)->exists());
        $this->assertGuest();
    }

    private function sign(string $sheet, string $password = 'secret123')
    {
        return $this->post(route('sign_p'), ['signature_sheet' => $sheet, 'email' => 'ana@us.es', 'password' => $password]);
    }
}
