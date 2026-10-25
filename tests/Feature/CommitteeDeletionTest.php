<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CommitteeDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:'.base64_encode(str_repeat('a', 32))]);
    }

    public static function roles(): array
    {
        return [['PRESIDENT', 'president'], ['LECTURE', 'lecture']];
    }

    #[DataProvider('roles')]
    public function test_authorized_users_can_remove_an_empty_committee(string $role, string $prefix): void
    {
        $this->withoutExceptionHandling();
        $this->signInWithRole($role);
        $committee = Committee::create(['name' => 'Empty committee']);
        $other = Committee::create(['name' => 'Keep this committee']);

        $this->post(route($prefix.'.committee.management.remove'), ['_id' => $committee->id])
            ->assertRedirect(route($prefix.'.committee.list'))
            ->assertSessionHas('success', 'Comité eliminado con éxito.');

        $this->assertModelMissing($committee);
        $this->assertModelExists($other);
    }

    public static function occupiedCommittees(): array
    {
        $cases = [];
        foreach (self::roles() as [$role, $prefix]) {
            foreach ([Coordinator::class, Secretary::class, Evidence::class, Meeting::class] as $model) {
                $cases[] = [$role, $prefix, $model];
            }
        }

        return $cases;
    }

    #[DataProvider('occupiedCommittees')]
    public function test_related_records_prevent_deletion(string $role, string $prefix, string $model): void
    {
        $user = $this->signInWithRole($role);
        $committee = Committee::create(['name' => 'Occupied committee']);
        $attributes = ['committee_id' => $committee->id];
        if ($model !== Meeting::class) {
            $attributes['user_id'] = $user->id;
        }
        if (in_array($model, [Evidence::class, Meeting::class])) {
            $attributes['title'] = 'Related record';
        }
        $related = $model::forceCreate($attributes);

        $this->post(route($prefix.'.committee.management.remove'), ['_id' => $committee->id])
            ->assertRedirect(route($prefix.'.committee.list'))
            ->assertSessionHas('error', 'El comité no ha podido ser eliminado.');

        $this->assertModelExists($committee);
        $this->assertSame('Occupied committee', $committee->fresh()->name);
        $this->assertModelExists($related);
    }

    #[DataProvider('roles')]
    public function test_missing_committees_return_not_found(string $role, string $prefix): void
    {
        $this->signInWithRole($role);
        $committee = Committee::create(['name' => 'Keep this committee']);

        $this->post(route($prefix.'.committee.management.remove'), ['_id' => $committee->id + 1])
            ->assertNotFound();
        $this->post(route($prefix.'.committee.management.remove'))->assertNotFound();

        $this->assertModelExists($committee);
    }

    public function test_guests_cannot_remove_committees(): void
    {
        $committee = Committee::create(['name' => 'Keep this committee']);

        foreach (['president', 'lecture'] as $prefix) {
            $this->post(route($prefix.'.committee.management.remove'), ['_id' => $committee->id])
                ->assertRedirect(route('login'));
        }

        $this->assertModelExists($committee);
    }

    public function test_students_cannot_remove_committees(): void
    {
        $this->signInWithRole('STUDENT');
        $committee = Committee::create(['name' => 'Keep this committee']);

        foreach (['president', 'lecture'] as $prefix) {
            $this->post(route($prefix.'.committee.management.remove'), ['_id' => $committee->id])
                ->assertNotFound();
        }

        $this->assertModelExists($committee);
    }

    #[DataProvider('roles')]
    public function test_rejected_committee_requests_preserve_all_stored_data(string $role, string $unused): void
    {
        $user = $this->signInWithRole($role);
        $snapshot = static function (): string {
            $data = [];
            foreach (\Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name") as $table) {
                $data[$table->name] = \Illuminate\Support\Facades\DB::table($table->name)->orderBy('rowid')->get()->all();
            }

            return hash('sha256', serialize($data));
        };
        $before = $snapshot();
        foreach (['president', 'lecture'] as $prefix) {
            foreach ([null, 0, -1, 'not-an-id', 999999] as $id) {
                $this->post('/'.$prefix.'/committee/management/remove', ['_id' => $id])->assertNotFound();
                $this->assertSame($before, $snapshot());
            }
        }
        foreach ([Coordinator::class, Secretary::class, Evidence::class, Meeting::class] as $model) {
            $committee = Committee::create(['name' => 'Guard '.$model]);
            $attributes = ['committee_id' => $committee->id];
            if ($model !== Meeting::class) {
                $attributes['user_id'] = $user->id;
            }
            if (in_array($model, [Evidence::class, Meeting::class])) {
                $attributes['title'] = 'Related';
            }
            $model::forceCreate($attributes);
            $before = $snapshot();
            foreach (['president', 'lecture'] as $prefix) {
                $this->post('/'.$prefix.'/committee/management/remove', ['_id' => $committee->id])
                    ->assertRedirect('/'.strtolower($role).'/committee/list')
                    ->assertSessionHas('error', 'El comité no ha podido ser eliminado.');
                $this->assertSame($before, $snapshot());
            }
        }
    }

    private function signInWithRole(string $name): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::forceCreate(['rol' => $name, 'slug' => strtolower($name)]));
        $this->actingAs($user);

        return $user;
    }
}
