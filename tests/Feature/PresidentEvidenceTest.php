<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PresidentEvidenceTest extends TestCase
{
    use RefreshDatabase;

    private Committee $committee;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Configuration::forceCreate(['upload_evidences_timestamp' => now()->addWeek()]);
        $this->committee = Committee::create(['name' => 'Presidencia']);
    }

    public function test_the_evidences_a_president_publishes_are_accepted(): void
    {
        $this->actingAs($this->user('PRESIDENT'));

        $this->post(route('evidence.publish'), $this->form())->assertRedirect(route('evidence.list'));
        $this->assertSame('ACCEPTED', Evidence::sole()->status);
    }

    public function test_an_edited_draft_a_president_publishes_is_accepted(): void
    {
        $this->actingAs($this->user('PRESIDENT'));
        $this->post(route('evidence.draft'), $this->form());
        $draft = Evidence::sole();
        $this->assertSame('DRAFT', $draft->status);

        $this->post(route('evidence.publish.edit'), ['_id' => $draft->id] + $this->form());

        $this->assertSame('ACCEPTED', Evidence::where('last', true)->sole()->status);
    }

    public function test_the_evidences_other_students_publish_wait_for_review(): void
    {
        $this->actingAs($this->user('STUDENT'));

        $this->post(route('evidence.publish'), $this->form());

        $this->assertSame('PENDING', Evidence::sole()->status);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));

        return $user;
    }

    private function form(): array
    {
        return [
            'title' => 'Coordinación general',
            'hours' => 3,
            'minutes' => 0,
            'committee' => $this->committee->id,
            'description' => 'Coordinación de todos los comités del congreso.',
        ];
    }
}
