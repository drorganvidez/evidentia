<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigDeadlinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_deadline_is_extended_while_another_one_is_already_closed(): void
    {
        $closed = now()->subDay();
        $extended = now()->addWeeks(2);
        Configuration::forceCreate(['upload_evidences_timestamp' => $closed->format('Y-m-d').' 23:59:00']);
        $lecture = User::factory()->create();
        $lecture->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));

        $this->actingAs($lecture)->post(route('lecture.config.save'), [
            'event_name' => 'Jornadas InnoSoft Days', 'upload_evidences_date' => $closed->format('Y-m-d'), 'upload_evidences_time' => '23:59',
            'validate_evidences_date' => $extended->format('Y-m-d'), 'validate_evidences_time' => '23:59',
            'meetings_date' => $extended->format('Y-m-d'), 'meetings_time' => '23:59',
            'bonus_date' => $extended->format('Y-m-d'), 'bonus_time' => '23:59',
            'attendees_date' => $extended->format('Y-m-d'), 'attendees_time' => '23:59',
        ])->assertSessionHasNoErrors()->assertRedirect(route('lecture.config'));

        $configuration = Configuration::find(1);
        $this->assertSame($closed->format('Y-m-d').' 23:59', Carbon::parse($configuration->upload_evidences_timestamp)->format('Y-m-d H:i'));
        $this->assertSame($extended->format('Y-m-d').' 23:59', Carbon::parse($configuration->validate_evidences_timestamp)->format('Y-m-d H:i'));
    }

    public function test_the_president_saves_the_configuration_too(): void
    {
        Configuration::forceCreate([]);
        $president = User::factory()->create();
        $president->roles()->attach(Role::forceCreate(['rol' => 'PRESIDENT', 'slug' => 'president']));
        $date = now()->addWeek()->format('Y-m-d');

        $this->actingAs($president)->post(route('president.config.save'), [
            'event_name' => 'Jornadas InnoSoft Days', 'upload_evidences_date' => $date, 'upload_evidences_time' => '20:00',
            'validate_evidences_date' => $date, 'validate_evidences_time' => '21:00',
            'meetings_date' => $date, 'meetings_time' => '20:00',
            'bonus_date' => $date, 'bonus_time' => '20:00',
            'attendees_date' => $date, 'attendees_time' => '20:00',
        ])->assertRedirect(route('president.config'));

        $this->assertSame($date.' 21:00', Carbon::parse(Configuration::find(1)->validate_evidences_timestamp)->format('Y-m-d H:i'));
    }
}
