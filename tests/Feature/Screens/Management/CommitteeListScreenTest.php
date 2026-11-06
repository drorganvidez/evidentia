<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Committee;

class CommitteeListScreenTest extends ScreenTestCase
{
    public function test_both_managers_edit_the_committees_from_the_new_layout(): void
    {
        $logistics = Committee::where('name', 'Logística')->sole();
        $communication = Committee::where('name', 'Comunicación')->sole();

        foreach (['profesor' => 'lecture', 'presidente' => 'president'] as $who => $prefix) {
            $this->screen($who, route($prefix.'.committee.list'))
                ->assertSee('Comités del curso')
                ->assertSee('action="'.route($prefix.'.committee.management.new').'"', false)
                ->assertSee('action="'.route($prefix.'.committee.management.save').'"', false)
                ->assertSee('name="name_'.$logistics->id.'"', false)
                ->assertSee('name="icon_'.$logistics->id.'"', false)
                // Only an empty committee can be removed
                ->assertSee('name="_id" value="'.$communication->id.'"', false)
                ->assertDontSee('name="_id" value="'.$logistics->id.'"', false);
        }
    }

    public function test_a_failed_new_committee_reopens_its_dialog(): void
    {
        $this->actingAs($this->person('profesor'))
            ->from(route('lecture.committee.list'))
            ->post(route('lecture.committee.management.new'), ['name' => 'Logística'])
            ->assertSessionHasErrors('name');

        $this->screen('profesor', route('lecture.committee.list'))
            ->assertSee('data-open', false)
            ->assertSee('Revisa el formulario');
    }
}
