<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityPruneTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function entry(string $action, int $daysAgo, array $attributes = []): int
    {
        return ActivityLog::create([
            'created_at' => now()->subDays($daysAgo), 'action' => $action, 'ip' => '10.0.0.1', 'user_agent' => 'Firefox',
            ...$attributes,
        ])->id;
    }

    private function notification(int $userId, ?int $readDaysAgo): string
    {
        $id = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $id, 'type' => 'evidence.rejected', 'notifiable_type' => 'user', 'notifiable_id' => $userId,
            'data' => '{}', 'read_at' => $readDaysAgo === null ? null : now()->subDays($readDaysAgo),
            'created_at' => now()->subDays(200), 'updated_at' => now()->subDays(200),
        ]);

        return $id;
    }

    public function test_it_applies_every_retention_rule(): void
    {
        $failedOld = $this->entry('login_failed', 366);
        $failedRecent = $this->entry('login_failed', 364);
        $expired = $this->entry('evidence.created', 5 * 366);
        DB::table('activity_log_targets')->insert(['activity_log_id' => $expired, 'user_id' => $this->user->id]);
        $kept = $this->entry('evidence.created', 4 * 365);
        $yearOld = $this->entry('evidence.accepted', 400);
        $recent = $this->entry('evidence.accepted', 10);
        $readOld = $this->notification($this->user->id, 181);
        $readRecent = $this->notification($this->user->id, 179);
        $unread = $this->notification($this->user->id, null);
        $orphan = $this->notification(999999, null);
        DB::table('notification_reminders')->insert([
            ['user_id' => $this->user->id, 'key' => 'digest:old', 'sent_at' => now()->subDays(366)],
            ['user_id' => $this->user->id, 'key' => 'digest:new', 'sent_at' => now()->subDays(10)],
        ]);

        $this->artisan('activity:prune')->assertSuccessful();

        $this->assertDatabaseMissing('activity_log', ['id' => $failedOld]);
        $this->assertDatabaseHas('activity_log', ['id' => $failedRecent]);
        $this->assertDatabaseMissing('activity_log', ['id' => $expired]);
        $this->assertDatabaseMissing('activity_log_targets', ['activity_log_id' => $expired]);
        $this->assertDatabaseHas('activity_log', ['id' => $kept, 'ip' => null, 'user_agent' => null]);
        $this->assertDatabaseHas('activity_log', ['id' => $yearOld, 'ip' => null, 'user_agent' => null]);
        $this->assertDatabaseHas('activity_log', ['id' => $recent, 'ip' => '10.0.0.1', 'user_agent' => 'Firefox']);
        $this->assertDatabaseMissing('notifications', ['id' => $readOld]);
        $this->assertDatabaseHas('notifications', ['id' => $readRecent]);
        $this->assertDatabaseHas('notifications', ['id' => $unread]);
        $this->assertDatabaseMissing('notifications', ['id' => $orphan]);
        $this->assertSame(['digest:new'], DB::table('notification_reminders')->pluck('key')->all());

        $pruned = ActivityLog::where('action', 'activity.pruned')->sole();
        $this->assertNull($pruned->user_id);
        $this->assertEquals([
            'failed_logins' => 1, 'entries' => 1, 'network_cleared' => 2, 'read_notifications' => 1,
            'orphan_notifications' => 1, 'reminders' => 1,
        ], $pruned->details);
    }

    public function test_the_retention_comes_from_the_configuration(): void
    {
        config(['activity.retention_years' => 1, 'activity.network_data_retention_days' => 30]);
        $old = $this->entry('evidence.created', 400);
        $month = $this->entry('evidence.created', 40);

        $this->artisan('activity:prune')->assertSuccessful();

        $this->assertDatabaseMissing('activity_log', ['id' => $old]);
        $this->assertDatabaseHas('activity_log', ['id' => $month, 'ip' => null]);
    }

    public function test_it_works_in_batches(): void
    {
        $rows = array_map(fn (int $i): array => [
            'created_at' => now()->subDays(400), 'action' => 'login_failed', 'ip' => '10.0.0.1',
        ], range(1, 2500));
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('activity_log')->insert($chunk);
        }

        $this->artisan('activity:prune')->assertSuccessful();

        $this->assertSame(1, ActivityLog::count());
        $this->assertSame(2500, ActivityLog::sole()->details['failed_logins']);
    }

    public function test_a_dry_run_deletes_nothing_and_annotates_nothing(): void
    {
        $failedOld = $this->entry('login_failed', 366);
        $orphan = $this->notification(999999, null);

        $this->artisan('activity:prune', ['--dry-run' => true])
            ->expectsOutputToContain('Simulación')
            ->assertSuccessful();

        $this->assertDatabaseHas('activity_log', ['id' => $failedOld, 'ip' => '10.0.0.1']);
        $this->assertDatabaseHas('notifications', ['id' => $orphan]);
        $this->assertSame(0, ActivityLog::where('action', 'activity.pruned')->count());
    }
}
