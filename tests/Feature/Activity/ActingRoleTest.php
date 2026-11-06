<?php

namespace Tests\Feature\Activity;

use App\Http\Middleware\CheckRoles;
use App\Models\Role;
use App\Models\User;
use App\Services\Activity\ActingRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The role of an annotation comes from the route used, not from the roles the person has.
 */
class ActingRoleTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(string ...$roles): User
    {
        $user = User::factory()->create();
        foreach ($roles as $role) {
            $user->roles()->attach(Role::where('rol', $role)->first() ?? Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]));
        }

        return $user->load('roles');
    }

    private function resolve(User $user, string $routeName): ?string
    {
        $route = Route::getRoutes()->getByName($routeName);
        $this->assertNotNull($route, $routeName);

        return ActingRole::resolve($user, $route);
    }

    public function test_the_prefix_of_the_route_names_the_role(): void
    {
        $this->assertSame('COORDINATOR', $this->resolve($this->userWith('STUDENT', 'COORDINATOR'), 'coordinator.evidence.accept'));
        $this->assertSame('SECRETARY', $this->resolve($this->userWith('STUDENT', 'SECRETARY'), 'secretary.bonus.new'));
        $this->assertSame('SECRETARY', $this->resolve($this->userWith('STUDENT', 'SECRETARY'), 'gp.users.all'));
        $this->assertSame('REGISTER_COORDINATOR', $this->resolve($this->userWith('STUDENT', 'REGISTER_COORDINATOR'), 'registercoordinator.token.save'));
        $this->assertSame('REGISTER_COORDINATOR', $this->resolve($this->userWith('STUDENT', 'REGISTER_COORDINATOR'), 'attendee.store'));
        $this->assertSame('PRESIDENT', $this->resolve($this->userWith('PRESIDENT'), 'president.config.save'));
        $this->assertSame('LECTURE', $this->resolve($this->userWith('LECTURE'), 'lecture.config.save'));
        $this->assertSame('LECTURE', $this->resolve($this->userWith('LECTURE'), 'randomize.save'));
        $this->assertSame('LECTURE', $this->resolve($this->userWith('LECTURE', 'PRESIDENT'), 'management.user.delete.all'));
    }

    public function test_the_author_routes_act_as_student_even_without_the_role(): void
    {
        $coordinator = $this->userWith('COORDINATOR');

        $this->assertSame('STUDENT', $this->resolve($coordinator, 'evidence.draft'));
        $this->assertSame('STUDENT', $this->resolve($coordinator, 'evidence.remove'));
        $this->assertSame('STUDENT', $this->resolve($coordinator, 'meeting.list'));
        $this->assertSame('STUDENT', $this->resolve($this->userWith('REGISTER_COORDINATOR'), 'attendee.list'));
    }

    public function test_without_the_role_of_the_prefix_the_roles_of_check_roles_decide(): void
    {
        $this->assertSame('PRESIDENT', $this->resolve($this->userWith('PRESIDENT'), 'lecture.config.save'));
        $this->assertSame('LECTURE', $this->resolve($this->userWith('LECTURE'), 'president.user.management.save'));
    }

    public function test_the_middleware_of_the_controller_constructor_counts(): void
    {
        // management.user.new no lleva CheckRoles en la ruta, solo en el constructor de ManagementController
        $this->assertSame('LECTURE', $this->resolve($this->userWith('PRESIDENT', 'LECTURE'), 'management.user.new'));
        $this->assertSame('PRESIDENT', $this->resolve($this->userWith('PRESIDENT', 'STUDENT'), 'management.user.new'));
    }

    public function test_the_management_routes_without_prefix_take_the_highest_allowed_role(): void
    {
        $route = Route::getRoutes()->getByName('management.evidence.move');
        if ($route === null) {
            $route = Route::post('/_test/management/evidence/move', fn () => 'ok')
                ->middleware(CheckRoles::class.':PRESIDENT,LECTURE')->name('management.evidence.move');
        }

        $this->assertSame('LECTURE', ActingRole::resolve($this->userWith('PRESIDENT', 'LECTURE'), $route));
        $this->assertSame('PRESIDENT', ActingRole::resolve($this->userWith('PRESIDENT', 'COORDINATOR'), $route));
        $this->assertNull(ActingRole::resolve($this->userWith('COORDINATOR'), $route));
    }

    public function test_the_neutral_routes_have_no_role(): void
    {
        $lecturer = $this->userWith('LECTURE', 'STUDENT');

        foreach (['profile.upload.info', 'password.reset_p', 'password.update_p', 'sign_p', 'logout', 'home'] as $name) {
            $this->assertNull($this->resolve($lecturer, $name), $name);
        }
    }

    public function test_a_role_the_person_does_not_have_is_never_used(): void
    {
        $this->assertNull($this->resolve($this->userWith('SECRETARY'), 'coordinator.evidence.accept'));
    }

    public function test_no_person_no_route_or_an_unnamed_route_have_no_role(): void
    {
        $user = $this->userWith('LECTURE');

        $this->assertNull(ActingRole::resolve(null, Route::getRoutes()->getByName('lecture.config.save')));
        $this->assertNull(ActingRole::resolve($user, null));
        $this->assertNull(ActingRole::resolve($user, new RoutingRoute(['POST'], 'login', fn () => null)));
    }
}
