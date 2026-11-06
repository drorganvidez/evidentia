<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_chosen_users_survive_a_validation_error(): void
    {
        Configuration::forceCreate(['bonus_timestamp' => now()->addMonth()]);
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        Secretary::forceCreate(['user_id' => $user->id, 'committee_id' => Committee::create(['name' => 'Logística'])->id]);
        [$ana, $pedro] = User::factory()->count(2)->create();

        $html = $this->actingAs($user)
            ->from(route('secretary.bonus.create'))
            ->followingRedirects()
            ->post(route('secretary.bonus.new'), ['reason' => 'Mal', 'hours' => 2, 'users' => [$ana->id, $pedro->id]])
            ->assertOk()
            ->getContent();

        $dom = new DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        $selected = [];
        foreach ((new DOMXPath($dom))->query('//select[@name="users[]"]/option[@selected]') as $option) {
            $selected[] = (int) $option->getAttribute('value');
        }
        $this->assertEqualsCanonicalizing([$ana->id, $pedro->id], $selected);
    }
}
