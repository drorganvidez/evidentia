<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Services\Notifications\ReminderRegistry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_key_is_claimed_once_per_person(): void
    {
        $registry = app(ReminderRegistry::class);
        [$juan, $maria] = User::factory()->count(2)->create()->all();
        $key = 'deadline.upload:2:2026-06-15T20:00:00+02:00';

        $this->assertTrue($registry->claim($juan, $key));
        $this->assertFalse($registry->claim($juan, $key));
        $this->assertTrue($registry->claim($maria, $key));
        $this->assertTrue($registry->claim($juan, 'deadline.upload:7:2026-06-15T20:00:00+02:00'));
        $this->assertDatabaseCount('notification_reminders', 3);
    }

    public function test_the_last_reminder_with_a_prefix(): void
    {
        $registry = app(ReminderRegistry::class);
        [$juan, $maria] = User::factory()->count(2)->create()->all();

        $this->assertNull($registry->lastSentAt($juan, 'digest:'));

        $this->travelTo(Carbon::parse('2026-11-04 18:00:00'));
        $registry->claim($juan, 'digest:2026-11-04');
        $this->travelTo(Carbon::parse('2026-11-05 18:00:00'));
        $registry->claim($juan, 'digest:2026-11-05');
        $this->travelTo(Carbon::parse('2026-11-06 08:00:00'));
        $registry->claim($juan, 'deadline.upload:2:2026-11-08T20:00:00+01:00');
        $registry->claim($maria, 'digest:2026-11-06');

        $this->assertSame('2026-11-05 18:00:00', $registry->lastSentAt($juan, 'digest:')->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-06 08:00:00', $registry->lastSentAt($juan, 'deadline.')->format('Y-m-d H:i:s'));
        $this->assertNull($registry->lastSentAt($juan, 'other:'));
    }
}
