<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use Database\Seeders\E2eSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The notices of the end-to-end seed come from the real classes and leave unread ones to the people of the specs. */
class NotificationsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_seed_leaves_real_notices_to_the_student_the_coordinator_and_the_secretary(): void
    {
        $this->seed(E2eSeeder::class);
        $person = fn (string $username): User => User::where('username', $username)->sole();

        $student = $person('estudiante');
        // The other seeds of the end-to-end data (activity, messages) add notices of their own
        $unread = $student->unreadNotifications()->pluck('type')->all();
        $this->assertContains('evidence.rejected', $unread);
        $this->assertContains('minutes.registered', $unread);
        $this->assertContains('evidence.accepted', $student->readNotifications()->pluck('type')->all());
        $this->assertSame(2, $person('coordinador')->unreadNotifications()->where('type', 'evidence.submitted')->count());
        $this->assertSame(2, $person('secretario')->unreadNotifications()->where('type', 'evidence.submitted')->count());
        $this->assertSame(0, $person('companero')->notifications()->where('type', 'like', 'evidence.%')->count());
    }
}
