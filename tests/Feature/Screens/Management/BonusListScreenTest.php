<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Bonus;
use App\Models\Committee;

/** The hour bonuses of every committee, read only, for the lecturer and the president. */
class BonusListScreenTest extends ScreenTestCase
{
    public function test_both_roles_see_the_bonuses_of_every_committee_newest_first(): void
    {
        $other = Bonus::create(['reason' => 'Difusión en redes', 'hours' => 1.5]);
        $other->committee()->associate(Committee::where('name', 'Comunicación')->sole())->save();
        $other->users()->attach([$this->person('companero')->id, $this->person('registro')->id]);
        Bonus::where('reason', 'Montaje del escenario')->update(['created_at' => now()->subWeek()]);

        foreach (['profesor' => 'lecture', 'presidente' => 'president'] as $who => $prefix) {
            $html = $this->screen($who, route($prefix.'.bonus.list'))
                ->assertSee('Bonos de todos los comités')
                ->assertSee('Montaje del escenario')
                ->assertSee('Logística')
                ->assertSee('Difusión en redes')
                ->assertSee('Comunicación')
                ->assertSee('Pérez Romero, Lucía')
                ->assertSee('García López, Juan; Torres Gómez, Pablo')
                ->assertSee('2 bonos')
                ->assertSee('6,00 h')
                ->assertDontSee('Nuevo bono')
                ->assertDontSee('/secretary/bonus/', false)
                ->getContent();

            $this->assertLessThan(strpos($html, 'Montaje del escenario'), strpos($html, 'Difusión en redes'));
        }
    }

    public function test_without_bonuses_it_says_so(): void
    {
        Bonus::query()->delete();

        $this->screen('presidente', route('president.bonus.list'))->assertSee('No hay bonos');
    }

    public function test_nobody_else_opens_it(): void
    {
        foreach (['estudiante', 'secretario', 'coordinador', 'registro'] as $who) {
            $status = $this->actingAs($this->person($who))->get(route('lecture.bonus.list'))->getStatusCode();
            $this->assertContains($status, [302, 403, 404], $who);
        }
    }
}
