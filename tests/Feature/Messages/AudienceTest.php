<?php

namespace Tests\Feature\Messages;

use App\Models\Committee;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use App\Services\Messaging\Audience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class AudienceTest extends TestCase
{
    use RefreshDatabase;

    private Committee $logistica;

    private Committee $programa;

    /** @var array<string, User> */
    private array $u = [];

    private Secretary $secretary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logistica = Committee::create(['name' => 'Logística']);
        $this->programa = Committee::create(['name' => 'Programa']);
        Committee::create(['name' => 'Vacío']);
        $this->u = [
            'student' => $this->userWith('Zapata', 'STUDENT'),
            'coordinator' => $this->userWith('Navarro', 'STUDENT', 'COORDINATOR'),
            'secretary' => $this->userWith('Sánchez', 'STUDENT', 'SECRETARY'),
            'coordinatorPrograma' => $this->userWith('Ruiz', 'STUDENT', 'COORDINATOR'),
            'register' => $this->userWith('Gómez', 'STUDENT', 'REGISTER_COORDINATOR'),
            'president' => $this->userWith('Álvarez', 'STUDENT', 'PRESIDENT'),
            'lecturer' => $this->userWith('Moreno', 'LECTURE'),
        ];
        Coordinator::forceCreate(['user_id' => $this->u['coordinator']->id, 'committee_id' => $this->logistica->id]);
        Coordinator::forceCreate(['user_id' => $this->u['coordinatorPrograma']->id, 'committee_id' => $this->programa->id]);
        $this->secretary = Secretary::forceCreate(['user_id' => $this->u['secretary']->id, 'committee_id' => $this->logistica->id]);
    }

    private function userWith(string $surname, string ...$roles): User
    {
        $user = User::factory()->create(['surname' => $surname]);
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user;
    }

    private function keys(User $author, $about = null): array
    {
        return Audience::optionsFor($author->fresh(), $about)->pluck('value')->all();
    }

    public function test_the_keys_of_the_audiences(): void
    {
        $coordination = Audience::fromKey('COORDINATOR:7');
        $this->assertSame('COORDINATOR', $coordination->role);
        $this->assertSame(7, $coordination->committeeId);
        $this->assertSame('COORDINATOR:7', $coordination->key());
        $this->assertSame('LECTURE', Audience::fromKey('LECTURE')->key());
        $this->assertNull(Audience::fromKey('PRESIDENT')->committeeId);

        foreach (['COORDINATOR', 'SECRETARY:', 'PRESIDENT:3', 'STUDENT', 'COORDINATOR:abc', 'COORDINATOR:0', '', 'lecture'] as $invalid) {
            try {
                Audience::fromKey($invalid);
                $this->fail("[$invalid] was accepted.");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_the_labels(): void
    {
        $this->assertSame('Coordinación de Logística', (new Audience('COORDINATOR', $this->logistica->id))->label());
        $this->assertSame('Secretaría de Logística', (new Audience('SECRETARY', $this->logistica->id))->label());
        $this->assertSame('Coordinación de registro', (new Audience('REGISTER_COORDINATOR'))->label());
        $this->assertSame('Presidencia', (new Audience('PRESIDENT'))->label());
        $this->assertSame('Profesorado', (new Audience('LECTURE'))->label());
        $this->assertSame('Coordinación', (new Audience('COORDINATOR', 999))->label());
    }

    public function test_the_members_and_the_presidency_includes_the_lecturers(): void
    {
        $ids = fn (Audience $audience): array => $audience->members()->pluck('id')->sort()->values()->all();

        $this->assertSame([$this->u['coordinator']->id], $ids(new Audience('COORDINATOR', $this->logistica->id)));
        $this->assertSame([$this->u['secretary']->id], $ids(new Audience('SECRETARY', $this->logistica->id)));
        $this->assertSame([], $ids(new Audience('SECRETARY', $this->programa->id)));
        $this->assertSame([$this->u['register']->id], $ids(new Audience('REGISTER_COORDINATOR')));
        $this->assertSame(collect([$this->u['president']->id, $this->u['lecturer']->id])->sort()->values()->all(), $ids(new Audience('PRESIDENT')));
        $this->assertSame([$this->u['lecturer']->id], $ids(new Audience('LECTURE')));

        $this->assertTrue((new Audience('COORDINATOR', $this->logistica->id))->includes($this->u['coordinator']));
        $this->assertFalse((new Audience('COORDINATOR', $this->programa->id))->includes($this->u['coordinator']));
        $this->assertTrue((new Audience('PRESIDENT'))->includes($this->u['lecturer']));
        $this->assertFalse((new Audience('LECTURE'))->includes($this->u['president']));
    }

    public function test_the_audiences_of_a_person(): void
    {
        $keys = fn (string $who): array => Audience::of($this->u[$who])->map->key()->all();

        $this->assertSame(['COORDINATOR:'.$this->logistica->id], $keys('coordinator'));
        $this->assertSame(['SECRETARY:'.$this->logistica->id], $keys('secretary'));
        $this->assertSame(['REGISTER_COORDINATOR'], $keys('register'));
        $this->assertSame(['PRESIDENT'], $keys('president'));
        $this->assertSame(['PRESIDENT', 'LECTURE'], $keys('lecturer'));
        $this->assertSame([], $keys('student'));
    }

    public function test_without_an_object_every_committee_with_someone_assigned_and_the_own_ones_first(): void
    {
        $l = $this->logistica->id;
        $p = $this->programa->id;
        $this->assertSame(
            ["COORDINATOR:$l", "SECRETARY:$l", "COORDINATOR:$p", 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'],
            $this->keys($this->u['student']),
        );

        Evidence::create(['title' => 'Mesas', 'status' => 'REJECTED', 'user_id' => $this->u['student']->id, 'committee_id' => $p]);
        $this->assertSame(
            ["COORDINATOR:$p", "COORDINATOR:$l", "SECRETARY:$l", 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'],
            $this->keys($this->u['student']),
        );
        $this->assertSame(
            ['label' => 'Coordinación de Programa', 'value' => "COORDINATOR:$p"],
            collect(Audience::optionsFor($this->u['student'], null)->first())->sortKeys()->all(),
        );
    }

    public function test_never_the_audiences_of_the_author(): void
    {
        $l = $this->logistica->id;
        $p = $this->programa->id;

        $this->assertSame(["SECRETARY:$l", "COORDINATOR:$p", 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'], $this->keys($this->u['coordinator']));
        $this->assertSame(["COORDINATOR:$l", "SECRETARY:$l", "COORDINATOR:$p", 'REGISTER_COORDINATOR'], $this->keys($this->u['lecturer']));
        $this->assertSame(["COORDINATOR:$l", "SECRETARY:$l", "COORDINATOR:$p", 'REGISTER_COORDINATOR', 'LECTURE'], $this->keys($this->u['president']));
    }

    public function test_about_an_evidence_its_coordination_first(): void
    {
        $l = $this->logistica->id;
        $old = Evidence::create(['title' => 'v1', 'status' => 'REJECTED', 'user_id' => $this->u['student']->id, 'committee_id' => $this->programa->id, 'last' => false]);
        Evidence::create(['title' => 'v2', 'status' => 'PENDING', 'user_id' => $this->u['student']->id, 'committee_id' => $l, 'points_to' => $old->id]);

        $this->assertSame(["COORDINATOR:$l", "SECRETARY:$l", 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'], $this->keys($this->u['student'], $old));
    }

    public function test_about_minutes_or_a_meeting_request_its_secretary_first(): void
    {
        $l = $this->logistica->id;
        $p = $this->programa->id;
        $meeting = Meeting::forceCreate(['title' => 'Reunión', 'datetime' => now(), 'hours' => 1, 'committee_id' => $l]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $this->secretary->id]);
        $request = MeetingRequest::forceCreate(['title' => 'Convocatoria', 'committee_id' => $p]);

        $this->assertSame(["SECRETARY:$l", "COORDINATOR:$l", 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'], $this->keys($this->u['student'], $minutes));
        $this->assertSame(["COORDINATOR:$p", 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'], $this->keys($this->u['student'], $request));
    }
}
