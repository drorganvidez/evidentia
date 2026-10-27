<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MeetingAttendeesUniqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_migration_removes_repeated_attendees_and_keeps_the_rest(): void
    {
        $migration = require database_path('migrations/2026_10_27_000001_add_unique_attendee_to_meeting_user_table.php');
        $migration->down();

        $meeting = Meeting::forceCreate(['title' => 'Primera reunión Comité de Logística', 'hours' => 2]);
        $other = Meeting::forceCreate(['title' => 'Desglose de tareas', 'hours' => 1]);
        [$ana, $pedro, $secretary] = User::factory()->count(3)->create();
        // Mismo patrón que en producción: A B A B y el secretario una vez
        foreach ([$ana, $pedro, $ana, $pedro, $secretary] as $user) {
            DB::table('meeting_user')->insert(['meeting_id' => $meeting->id, 'user_id' => $user->id]);
        }
        DB::table('meeting_user')->insert(['meeting_id' => $other->id, 'user_id' => $ana->id]);
        $this->assertSame(5.0, (float) $ana->meetings()->sum('hours')); // la reunión de 2 h cuenta dos veces

        $migration->up();

        $this->assertEqualsCanonicalizing([$ana->id, $pedro->id, $secretary->id], DB::table('meeting_user')->where('meeting_id', $meeting->id)->pluck('user_id')->all());
        $this->assertSame([$ana->id], DB::table('meeting_user')->where('meeting_id', $other->id)->pluck('user_id')->all());
        $this->assertSame(3.0, (float) $ana->meetings()->sum('hours'));
    }

    public function test_an_attendee_cannot_be_added_twice_to_the_same_meeting(): void
    {
        $meeting = Meeting::forceCreate(['title' => 'Reunión inicial de Logística']);
        $user = User::factory()->create();
        $meeting->users()->attach($user);

        $this->expectException(QueryException::class);
        $meeting->users()->attach($user);
    }
}
