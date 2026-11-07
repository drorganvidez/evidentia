<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiographyLockTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = User::factory()->create(['biography' => 'Organicé las mesas del congreso.']);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
    }

    public function test_the_summary_can_change_before_the_random_pick(): void
    {
        $this->actingAs($this->student)->get(route('profile.view'))->assertOk()->assertSee('biography-form');

        $this->post(route('profile.upload.biography'), ['biography' => 'Coordiné el montaje de las salas.'])
            ->assertRedirect(route('profile.view'));
        $this->assertSame('Coordiné el montaje de las salas.', $this->student->fresh()->biography);
    }

    public function test_the_summary_is_fixed_once_the_evidences_are_randomized(): void
    {
        Evidence::create([
            'title' => 'Organización de mesas', 'description' => 'Organicé las mesas del congreso.', 'hours' => 2,
            'status' => 'ACCEPTED', 'rand' => true, 'user_id' => $this->student->id,
            'committee_id' => Committee::create(['name' => 'Programa'])->id,
        ]);

        $this->actingAs($this->student)->get(route('profile.view'))
            ->assertOk()
            ->assertSee('Resumen cerrado')
            ->assertDontSee('biography-form');

        $this->post(route('profile.upload.biography'), ['biography' => 'Otro texto distinto del anterior.'])
            ->assertRedirect(route('profile.view'))
            ->assertSessionHas('error');
        $this->assertSame('Organicé las mesas del congreso.', $this->student->fresh()->biography);
    }
}
