<?php

namespace Tests\Feature\Notifications;

use App\Models\ActivityLog;
use App\Models\NotificationPreference;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Catalog;
use App\Services\Notifications\NotificationPreferences;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Notifications\Fixtures\DummyDigestibleNotice;
use Tests\Feature\Notifications\Fixtures\DummyFixedNotice;
use Tests\Feature\Notifications\Fixtures\DummyHiddenNotice;
use Tests\Feature\Notifications\Fixtures\DummyInformativeNotice;
use Tests\Feature\Notifications\Fixtures\DummyNotice;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    private NotificationPreferences $preferences;

    protected function setUp(): void
    {
        parent::setUp();
        Catalog::using([DummyNotice::class, DummyFixedNotice::class, DummyInformativeNotice::class, DummyDigestibleNotice::class, DummyHiddenNotice::class]);
        $this->preferences = app(NotificationPreferences::class);
    }

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

    private function channels(User $user, string $class): array
    {
        return $this->preferences->channelsFor($user, new $class);
    }

    public function test_the_channels_by_level(): void
    {
        $user = $this->userWith('STUDENT');

        $this->assertSame(['database', 'mail'], $this->channels($user, DummyFixedNotice::class));
        $this->assertSame(['database', 'mail'], $this->channels($user, DummyNotice::class));
        $this->assertSame(['database'], $this->channels($user, DummyInformativeNotice::class));
    }

    public function test_a_saved_deviation_wins_over_the_level_but_never_over_a_fixed_notice(): void
    {
        $user = $this->userWith('STUDENT');
        NotificationPreference::create(['user_id' => $user->id, 'mail' => ['test.important' => false, 'test.informative' => true, 'test.fixed' => false]]);

        $this->assertSame(['database'], $this->channels($user, DummyNotice::class));
        $this->assertSame(['database', 'mail'], $this->channels($user, DummyInformativeNotice::class));
        $this->assertSame(['database', 'mail'], $this->channels($user, DummyFixedNotice::class));
        $this->assertFalse($this->preferences->mailEnabled($user, 'test.important'));
        $this->assertTrue($this->preferences->mailEnabled($user, DummyInformativeNotice::class));
        $this->assertFalse($this->preferences->mailEnabled($user, 'unknown.key'));
    }

    public function test_the_digest_replaces_the_mail_of_a_digestible_notice_for_coordinators_and_secretaries(): void
    {
        $coordinator = $this->userWith('STUDENT', 'COORDINATOR');
        $student = $this->userWith('STUDENT');
        NotificationPreference::create(['user_id' => $coordinator->id, 'digest' => true]);
        NotificationPreference::create(['user_id' => $student->id, 'digest' => true]);

        $this->assertTrue($this->preferences->digest($coordinator));
        $this->assertSame(['database'], $this->channels($coordinator, DummyDigestibleNotice::class));
        $this->assertSame(['database', 'mail'], $this->channels($coordinator, DummyNotice::class));
        $this->assertSame(['database', 'mail'], $this->channels($student, DummyDigestibleNotice::class));
    }

    public function test_a_blocked_person_only_gets_the_inbox(): void
    {
        $user = $this->userWith('STUDENT');
        $user->forceFill(['block' => true])->save();

        $this->assertSame(['database'], $this->channels($user, DummyFixedNotice::class));
        $this->assertSame(['database'], $this->channels($user, DummyNotice::class));
    }

    public function test_saving_keeps_only_the_deviations_and_annotates_the_change(): void
    {
        $coordinator = $this->userWith('STUDENT', 'COORDINATOR');
        $this->actingAs($coordinator);

        // correo apagado en el importante y encendido en el informativo; el fijo no se toca
        $preference = $this->preferences->update($coordinator, ['test.informative', 'test.digestible'], true);

        $this->assertSame(['test.important' => false, 'test.informative' => true], $preference->fresh()->mail);
        $this->assertTrue($preference->fresh()->digest);
        $this->assertSame(['database'], $this->channels($coordinator, DummyNotice::class));
        $log = ActivityLog::sole();
        $this->assertSame('notification.preferences_changed', $log->action);
        $this->assertSame('user', $log->subject_type);
        $this->assertSame($coordinator->id, $log->subject_id);
        $this->assertNull($log->committee_id);
        $this->assertSame(['mail' => [], 'digest' => false], $log->details['before']);
        $this->assertSame(['mail' => ['test.important' => false, 'test.informative' => true], 'digest' => true], $log->details['after']);

        // volver a los valores del nivel deja la fila sin desviaciones
        $preference = $this->preferences->update($coordinator, ['test.important', 'test.digestible'], false);
        $this->assertNull($preference->fresh()->mail);
        $this->assertSame(1, NotificationPreference::count());
    }

    public function test_a_student_cannot_turn_on_the_digest_and_keeps_the_choices_of_notices_they_cannot_see(): void
    {
        $student = $this->userWith('STUDENT');
        NotificationPreference::create(['user_id' => $student->id, 'mail' => ['test.informative' => true]]);
        $this->actingAs($student);

        $preference = $this->preferences->update($student, [], true);

        $this->assertFalse($preference->fresh()->digest);
        // el informativo es solo para coordinadores: su desviación se conserva
        $this->assertSame(['test.important' => false, 'test.informative' => true], $preference->fresh()->mail);
    }
}
