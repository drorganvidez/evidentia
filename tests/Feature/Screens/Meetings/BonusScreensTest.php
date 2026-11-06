<?php

namespace Tests\Feature\Screens\Meetings;

use App\Models\Bonus;
use App\Models\Configuration;

class BonusScreensTest extends MeetingScreenTestCase
{
    public function test_the_list_links_each_bonus_to_its_edition_and_removal(): void
    {
        $bonus = Bonus::sole();

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.bonus.list')));

        $this->assertStringContainsString('Montaje del escenario', $html);
        $this->assertStringContainsString(route('secretary.bonus.edit', ['id' => $bonus->id]), $html);
        $this->assertStringContainsString('action="'.route('secretary.bonus.remove').'"', $html);
        $this->assertStringContainsString(route('secretary.bonus.create'), $html);
    }

    public function test_the_list_is_read_only_after_the_bonus_deadline(): void
    {
        Configuration::query()->update(['bonus_timestamp' => now()->subDay()]);

        $html = $this->assertScreen($this->as('secretario')->get(route('secretary.bonus.list')));

        $this->assertStringContainsString('Montaje del escenario', $html);
        $this->assertStringNotContainsString(route('secretary.bonus.create'), $html);
        $this->assertStringNotContainsString(route('secretary.bonus.remove'), $html);
    }

    public function test_the_form_picks_people_with_the_default_lists(): void
    {
        $bonus = Bonus::sole();

        $create = $this->assertScreen($this->as('secretario')->get(route('secretary.bonus.create')));
        $this->assertStringContainsString('x-data="picker"', $create);
        $this->assertStringContainsString('name="users[]"', $create);
        $this->assertStringContainsString('Equipo de montaje', $create);

        $edit = $this->assertScreen($this->get(route('secretary.bonus.edit', ['id' => $bonus->id])));
        $this->assertStringContainsString('name="_id" value="'.$bonus->id.'"', $edit);
        $this->assertStringContainsString('value="Montaje del escenario"', $edit);
        $this->assertSame(1, substr_count($edit, '" selected'));
    }
}
