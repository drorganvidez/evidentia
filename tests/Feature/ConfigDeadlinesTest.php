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
            'upload_evidences_date' => $closed->format('Y-m-d'), 'upload_evidences_time' => '23:59',
            'validate_evidences_date' => $extended->format('Y-m-d'), 'validate_evidences_time' => '23:59',
            'meetings_date' => $extended->format('Y-m-d'), 'meetings_time' => '23:59',
            'bonus_date' => $extended->format('Y-m-d'), 'bonus_time' => '23:59',
            'attendees_date' => $extended->format('Y-m-d'), 'attendees_time' => '23:59',
        ])->assertSessionHasNoErrors()->assertRedirect(route('lecture.config'));

        $configuration = Configuration::find(1);
        $this->assertSame($closed->format('Y-m-d').' 23:59', Carbon::parse($configuration->upload_evidences_timestamp)->format('Y-m-d H:i'));
        $this->assertSame($extended->format('Y-m-d').' 23:59', Carbon::parse($configuration->validate_evidences_timestamp)->format('Y-m-d H:i'));
    }
}
