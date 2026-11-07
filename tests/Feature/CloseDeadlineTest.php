<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloseDeadlineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate([
            'id' => 1,
            'upload_evidences_timestamp' => now()->addWeek(),
            'validate_evidences_timestamp' => now()->addWeeks(2),
            'meetings_timestamp' => now()->addWeek(),
            'bonus_timestamp' => now()->subDay(),
            'attendee_timestamp' => now()->addWeek(),
        ]);
    }

    private function user(string $rol): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => $rol, 'slug' => strtolower($rol)]));

        return $user;
    }

    public function test_the_page_offers_to_close_only_the_open_deadlines(): void
    {
        $this->actingAs($this->user('LECTURE'))->get(route('lecture.config'))
            ->assertOk()
            ->assertSee('Cerrar un plazo ahora')
            ->assertSee('Cerrar subida de evidencias')
            ->assertDontSee('Cerrar registro de bonos');
    }

    public function test_the_lecturer_closes_the_upload_now(): void
    {
        $this->actingAs($this->user('LECTURE'))
            ->post(route('lecture.config.close'), ['deadline' => 'upload_evidences'])
            ->assertRedirect(route('lecture.config'))
            ->assertSessionHas('success');

        $configuration = Configuration::find(1);
        $this->assertFalse(Carbon::parse($configuration->upload_evidences_timestamp)->isFuture());
        $this->assertTrue(Carbon::parse($configuration->validate_evidences_timestamp)->isFuture());
        $this->assertDatabaseHas('activity_log', ['action' => 'config.changed']);
    }

    public function test_closing_the_validation_closes_the_upload_too_and_keeps_a_closed_deadline(): void
    {
        $bonus = Configuration::find(1)->bonus_timestamp;

        $this->actingAs($this->user('PRESIDENT'))
            ->post(route('president.config.close'), ['deadline' => 'validate_evidences'])
            ->assertRedirect(route('president.config'));
        $this->post(route('president.config.close'), ['deadline' => 'bonus']);

        $configuration = Configuration::find(1);
        $this->assertFalse(Carbon::parse($configuration->upload_evidences_timestamp)->isFuture());
        $this->assertFalse(Carbon::parse($configuration->validate_evidences_timestamp)->isFuture());
        $this->assertEquals(Carbon::parse($bonus)->format('Y-m-d H:i'), Carbon::parse($configuration->bonus_timestamp)->format('Y-m-d H:i'));
    }

    public function test_only_the_management_closes_and_only_a_real_deadline(): void
    {
        $this->actingAs($this->user('LECTURE'))
            ->post(route('lecture.config.close'), ['deadline' => 'everything'])
            ->assertSessionHasErrors('deadline');

        $this->actingAs($this->user('STUDENT'))
            ->post(route('lecture.config.close'), ['deadline' => 'upload_evidences']);
        $this->assertTrue(Carbon::parse(Configuration::find(1)->upload_evidences_timestamp)->isFuture());
    }
}
