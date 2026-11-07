<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\DefaultList;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BonusAndListActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Secretary $secretary;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate(['bonus_timestamp' => now()->addMonth()]);
        $this->user = User::factory()->create();
        $this->user->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));
        $this->secretary = Secretary::forceCreate([
            'user_id' => $this->user->id,
            'committee_id' => Committee::create(['name' => 'Logística'])->id,
        ]);
        $this->actingAs($this->user);
    }

    public function test_a_bonus_records_its_beneficiaries(): void
    {
        [$ana, $pedro] = User::factory()->count(2)->create();
        // los bonos solo van a quien trabaja en el comité
        foreach ([$ana, $pedro] as $member) {
            Evidence::create(['title' => 'Stand', 'description' => 'Stand', 'hours' => 1, 'status' => 'PENDING', 'user_id' => $member->id, 'committee_id' => $this->secretary->committee_id, 'last' => true]);
        }

        $this->post(route('secretary.bonus.new'), ['reason' => 'Montaje del escenario', 'hours' => 3, 'users' => [$ana->id, $pedro->id, $ana->id]])
            ->assertRedirect(route('secretary.bonus.list'));

        $log = ActivityLog::sole();
        $this->assertSame('bonus.created', $log->action);
        $this->assertSame('SECRETARY', $log->acting_role);
        $this->assertSame(Bonus::sole()->id, $log->subject_id);
        $this->assertSame($this->secretary->committee_id, $log->committee_id);
        $this->assertSame(['reason' => 'Montaje del escenario', 'hours' => 3, 'users' => 2], $log->details);
        $this->assertEqualsCanonicalizing([$ana->id, $pedro->id], $this->targets($log));
        $this->assertFalse($log->flagged);
    }

    public function test_a_bonus_for_the_secretary_is_flagged(): void
    {
        $this->post(route('secretary.bonus.new'), ['reason' => 'Coordinación del montaje', 'hours' => 2, 'users' => [$this->user->id]]);

        $this->assertSame(['role_overlap'], ActivityLog::sole()->flags);
    }

    public function test_editing_a_bonus_records_what_changed_and_keeps_the_flag(): void
    {
        $ana = User::factory()->create();
        $bonus = $this->bonus([$ana->id]);

        $this->post(route('secretary.bonus.save'), ['_id' => $bonus->id, 'reason' => 'Montaje del escenario', 'hours' => 4, 'users' => [$ana->id, $this->user->id]]);

        $log = ActivityLog::sole();
        $this->assertSame('bonus.edited', $log->action);
        $this->assertEquals(['hours' => 3, 'users' => [$ana->id]], $log->details['before']);
        $this->assertEquals(['hours' => 4, 'users' => collect([$ana->id, $this->user->id])->sort()->values()->all()], $log->details['after']);
        $this->assertSame(['role_overlap'], $log->flags);
        $this->assertEqualsCanonicalizing([$ana->id, $this->user->id], $this->targets($log));
    }

    public function test_removing_a_bonus_keeps_its_reason_and_who_loses_the_hours(): void
    {
        $ana = User::factory()->create();
        $bonus = $this->bonus([$ana->id]);

        $this->post(route('secretary.bonus.remove'), ['_id' => $bonus->id]);

        $log = ActivityLog::sole();
        $this->assertSame('bonus.deleted', $log->action);
        $this->assertSame('Montaje del escenario', $log->subject_title);
        $this->assertSame(['hours' => 3, 'users' => 1], $log->details);
        $this->assertSame([$ana->id], $this->targets($log));
    }

    public function test_default_lists_record_their_members(): void
    {
        [$ana, $pedro] = User::factory()->count(2)->create();

        $this->post(route('secretary.defaultlist.new'), ['name' => 'Equipo de montaje', 'users' => [$ana->id]]);
        $list = DefaultList::sole();
        $this->post(route('secretary.defaultlist.save'), ['_id' => $list->id, 'name' => 'Equipo de sonido', 'users' => [$ana->id, $pedro->id]]);
        $this->post(route('secretary.defaultlist.remove'), ['_id' => $list->id]);

        [$created, $edited, $deleted] = ActivityLog::orderBy('id')->get()->all();
        $this->assertSame(['defaultlist.created', 'defaultlist.edited', 'defaultlist.deleted'], [$created->action, $edited->action, $deleted->action]);
        $this->assertSame(['users' => 1], $created->details);
        $this->assertSame([$ana->id], $this->targets($created));
        $this->assertSame(['before' => ['name' => 'Equipo de montaje', 'users' => [$ana->id]], 'after' => ['name' => 'Equipo de sonido', 'users' => [$ana->id, $pedro->id]]], $edited->details);
        $this->assertSame('Equipo de sonido', $deleted->subject_title);
        $this->assertSame(['users' => 2], $deleted->details);
        $this->assertSame([$this->secretary->committee_id], ActivityLog::distinct()->pluck('committee_id')->all());
    }

    private function bonus(array $users): Bonus
    {
        $bonus = Bonus::create(['reason' => 'Montaje del escenario', 'hours' => 3]);
        $bonus->committee()->associate($this->secretary->committee_id)->save();
        $bonus->users()->attach($users);

        return $bonus;
    }

    /** @return list<int> */
    private function targets(ActivityLog $log): array
    {
        return DB::table('activity_log_targets')->where('activity_log_id', $log->id)->pluck('user_id')->map(fn ($id) => (int) $id)->all();
    }
}
