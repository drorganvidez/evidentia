<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attendee;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\User;
use App\View\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HourLimitsTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $lecturer;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate([]);
        $this->student = User::factory()->create();
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->lecturer = User::factory()->create();
        $this->lecturer->roles()->attach(Role::forceCreate(['rol' => 'LECTURE', 'slug' => 'lecture']));
        $committee = Committee::create(['name' => 'Programa']);
        foreach ([6, 5] as $hours) {
            Evidence::create(['title' => 'Mesa '.$hours, 'description' => 'Mesa', 'hours' => $hours, 'status' => 'ACCEPTED',
                'user_id' => $this->student->id, 'committee_id' => $committee->id, 'last' => true]);
        }
        foreach ([3, 4] as $hours) {
            $event = Event::forceCreate(['name' => 'Charla '.$hours, 'hours' => $hours]);
            Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $this->student->id, 'status' => 'Checked In']);
        }
    }

    public function test_without_limits_every_hour_counts(): void
    {
        $this->assertEquals(11, $this->student->evidences_computed_hours());
        $this->assertEquals(7, $this->student->events_hours());
        $this->assertEquals(18, $this->student->total_computed_hours());
    }

    public function test_the_limits_cap_the_hours_that_count_everywhere(): void
    {
        Configuration::query()->update(['max_evidences_hours' => 8, 'max_attendees_hours' => 5.5]);

        $this->assertEquals(11, $this->student->evidences_accepted_hours());
        $this->assertEquals(8, $this->student->evidences_computed_hours());
        $this->assertEquals(5.5, $this->student->events_hours());
        $this->assertEquals(13.5, $this->student->total_computed_hours());
        $hours = app(Dashboard::class)->hours($this->student);
        $this->assertEquals(13.5, $hours['total']);
        $this->assertSame('8,00 h de 11,00 (límite)', $hours['segments'][0]['text']);
        $this->assertSame('5,50 h de 7,00 (límite)', $hours['segments'][2]['text']);
    }

    public function test_the_lecturer_sets_and_clears_the_limits(): void
    {
        $this->actingAs($this->lecturer)->get(route('lecture.config'))->assertOk()->assertSee('Límites de horas');

        $this->save(['max_evidences_hours' => '20', 'max_attendees_hours' => '7.5'])->assertSessionHasNoErrors();
        $configuration = Configuration::find(1);
        $this->assertEquals(20, $configuration->max_evidences_hours);
        $this->assertEquals(7.5, $configuration->max_attendees_hours);
        $log = ActivityLog::where('action', 'config.changed')->latest('id')->first();
        $this->assertEquals(['max_evidences_hours' => 20.0, 'max_attendees_hours' => 7.5], array_intersect_key($log->details['after'], array_flip(['max_evidences_hours', 'max_attendees_hours'])));
        $this->actingAs($this->lecturer)->get(route('lecture.config'))->assertSee('value="7.5"', false);

        $this->save(['max_evidences_hours' => '', 'max_attendees_hours' => ''])->assertSessionHasNoErrors();
        $this->assertNull(Configuration::find(1)->max_evidences_hours);

        $this->save(['max_evidences_hours' => '-1'])->assertSessionHasErrors('max_evidences_hours');
    }

    private function save(array $limits)
    {
        $date = now()->addWeek()->format('Y-m-d');

        return $this->actingAs($this->lecturer)->post(route('lecture.config.save'), $limits + [
            'event_name' => 'Jornadas InnoSoft Days', 'upload_evidences_date' => $date, 'upload_evidences_time' => '20:00',
            'validate_evidences_date' => $date, 'validate_evidences_time' => '21:00',
            'meetings_date' => $date, 'meetings_time' => '20:00',
            'bonus_date' => $date, 'bonus_time' => '20:00',
            'attendees_date' => $date, 'attendees_time' => '20:00',
        ]);
    }
}
