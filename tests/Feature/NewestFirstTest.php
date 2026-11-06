<?php

namespace Tests\Feature;

use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Every dated list opens with the most recent row on top. */
class NewestFirstTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(E2eSeeder::class);
    }

    private function who(string $username): User
    {
        return User::where('username', $username)->sole();
    }

    /** Position of each text in the page, to compare their order. */
    private function before(string $html, string $first, string $second): bool
    {
        return strpos($html, $first) !== false && strpos($html, $first) < strpos($html, $second);
    }

    public function test_the_evidences_of_the_student_and_of_the_committee_start_with_the_newest(): void
    {
        $student = $this->who('estudiante');
        $old = Evidence::where('title', 'Montaje del photocall')->sole();
        $old->forceFill(['created_at' => now()->subWeek()])->save();
        Evidence::create(['title' => 'Evidencia recién creada', 'description' => 'x', 'hours' => 1, 'status' => 'PENDING',
            'user_id' => $student->id, 'committee_id' => Committee::where('name', 'Logística')->value('id')]);

        $mine = $this->actingAs($student)->get(route('evidence.list'))->assertOk()->getContent();
        $this->assertTrue($this->before($mine, 'Evidencia recién creada', 'Montaje del photocall'));

        $queue = $this->actingAs($this->who('coordinador'))->get(route('coordinator.evidence.list.pending'))->assertOk()->getContent();
        $this->assertTrue($this->before($queue, 'Evidencia recién creada', 'Montaje del photocall'));
    }

    public function test_meetings_and_bonuses_start_with_the_newest(): void
    {
        $committee = Committee::where('name', 'Logística')->sole();
        Meeting::forceCreate(['title' => 'Reunión antigua', 'datetime' => now()->subMonths(2), 'place' => 'A1', 'type' => '1', 'modality' => '1', 'hours' => 1, 'committee_id' => $committee->id]);
        Meeting::forceCreate(['title' => 'Reunión nueva', 'datetime' => now()->addDay(), 'place' => 'A1', 'type' => '1', 'modality' => '1', 'hours' => 1, 'committee_id' => $committee->id]);

        $meetings = $this->actingAs($this->who('profesor'))->get(route('lecture.meeting.list'))->assertOk()->getContent();
        $this->assertTrue($this->before($meetings, 'Reunión nueva', 'Reunión antigua'));

        $bonus = Bonus::create(['reason' => 'Bono más reciente', 'hours' => 1]);
        $bonus->committee()->associate($committee)->save();
        $bonus->forceFill(['created_at' => now()->addMinute()])->save();
        $bonuses = $this->actingAs($this->who('secretario'))->get(route('secretary.bonus.list'))->assertOk()->getContent();
        $this->assertTrue($this->before($bonuses, 'Bono más reciente', 'Montaje del escenario'));
    }
}
