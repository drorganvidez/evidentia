<?php

namespace Tests\Feature\Notifications;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\Role;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Notifications\Fixtures\DummyDigestibleNotice;
use Tests\Feature\Notifications\Fixtures\DummyFixedNotice;
use Tests\Feature\Notifications\Fixtures\DummyHiddenNotice;
use Tests\Feature\Notifications\Fixtures\DummyInformativeNotice;
use Tests\Feature\Notifications\Fixtures\DummyNotice;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Catalog::flush();
        parent::tearDown();
    }

    private function userWith(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user;
    }

    public function test_every_notice_of_the_app_loads_with_a_unique_key_and_a_label(): void
    {
        $keys = [];
        foreach (Catalog::all() as $class) {
            $this->assertTrue(is_subclass_of($class, BaseNotification::class), $class);
            $this->assertNotSame('', $class::label(), $class);
            $this->assertStringNotContainsString(':', $class::label(), $class);
            $this->assertInstanceOf(NotificationLevel::class, $class::level());
            foreach ($class::triggers() as $trigger) {
                $this->assertInstanceOf(ActivityAction::class, $trigger, $class);
            }
            foreach ($class::audience() as $role) {
                $this->assertContains($role, ['STUDENT', 'COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'], $class);
            }
            $keys[] = $class::key();
        }

        $this->assertSame(count($keys), count(array_unique($keys)));
        $this->assertSame(Catalog::all(), array_values(array_unique(Catalog::all())));
    }

    public function test_the_notices_triggered_by_a_verb(): void
    {
        Catalog::using([DummyNotice::class, DummyInformativeNotice::class, DummyFixedNotice::class]);

        $this->assertSame([DummyNotice::class, DummyInformativeNotice::class], Catalog::triggeredBy('evidence.accepted'));
        $this->assertSame([DummyInformativeNotice::class], Catalog::triggeredBy('evidence.rejected'));
        $this->assertSame([], Catalog::triggeredBy('evidence.created'));
        $this->assertSame([], Catalog::triggeredBy('not.a.verb'));
    }

    public function test_the_preferences_of_each_role(): void
    {
        Catalog::using([DummyNotice::class, DummyFixedNotice::class, DummyInformativeNotice::class, DummyDigestibleNotice::class, DummyHiddenNotice::class]);

        $this->assertSame(
            ['fixed' => [DummyFixedNotice::class], 'important' => [DummyNotice::class], 'informative' => []],
            Catalog::forPreferences($this->userWith('STUDENT')),
        );
        $this->assertSame(
            ['fixed' => [DummyFixedNotice::class], 'important' => [DummyNotice::class, DummyDigestibleNotice::class], 'informative' => [DummyInformativeNotice::class]],
            Catalog::forPreferences($this->userWith('STUDENT', 'COORDINATOR')),
        );
        $this->assertSame(
            ['fixed' => [], 'important' => [DummyNotice::class], 'informative' => []],
            Catalog::forPreferences($this->userWith('LECTURE')),
        );
    }

    public function test_a_notice_by_its_key_and_the_reset_of_the_catalogue(): void
    {
        Catalog::using([DummyNotice::class, DummyFixedNotice::class]);

        $this->assertSame(DummyFixedNotice::class, Catalog::byKey('test.fixed'));
        $this->assertNull(Catalog::byKey('test.none'));

        Catalog::flush();
        $this->assertNull(Catalog::byKey('test.fixed'));
    }
}
