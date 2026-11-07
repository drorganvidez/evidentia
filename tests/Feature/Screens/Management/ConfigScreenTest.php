<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Configuration;

class ConfigScreenTest extends ScreenTestCase
{
    public function test_both_managers_see_every_deadline_on_the_new_layout(): void
    {
        $upload = now()->addMonth();
        Configuration::query()->update(['upload_evidences_timestamp' => $upload, 'bonus_timestamp' => now()->subDay()]);

        foreach (['profesor' => 'lecture', 'presidente' => 'president'] as $who => $prefix) {
            $this->screen($who, route($prefix.'.config'))
                ->assertSee('action="'.route($prefix.'.config.save').'"', false)
                ->assertSee('name="upload_evidences_date"', false)
                ->assertSee('value="'.$upload->format('Y-m-d').'"', false)
                ->assertSee('value="'.$upload->format('H:i').'"', false)
                ->assertSee('name="attendees_time"', false)
                ->assertSee('Abierto')
                ->assertSee('Cerrado');
        }
    }

    public function test_a_rejected_save_keeps_what_was_typed(): void
    {
        $this->actingAs($this->person('profesor'))
            ->from(route('lecture.config'))
            ->post(route('lecture.config.save'), ['event_name' => 'Jornadas InnoSoft Days', 'upload_evidences_date' => '2031-03-04'])
            ->assertSessionHasErrors('validate_evidences_date');

        $this->screen('profesor', route('lecture.config'))
            ->assertSee('value="2031-03-04"', false)
            ->assertSee('Revisa el formulario');
    }
}
