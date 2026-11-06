<?php

namespace Tests\Feature\Screens\Management;

use App\Models\Role;
use App\Models\User;

/** The people of the course in pages: volume from the factory over the seed of the end-to-end tests. */
class UserListPagesTest extends ScreenTestCase
{
    /** 40 students whose surnames sort after the people of the seed. */
    private function volume(int $count = 40): void
    {
        $student = Role::where('rol', 'STUDENT')->sole();
        User::factory()->count($count)
            ->sequence(fn ($sequence) => [
                'name' => 'Ana',
                'surname' => sprintf('Zubiri %02d', $sequence->index + 1),
                'username' => sprintf('anazub%02d', $sequence->index + 1),
                'email' => sprintf('ana.zubiri%02d@alum.us.es', $sequence->index + 1),
            ])
            ->create()
            ->each(fn (User $user) => $user->roles()->attach($student));
    }

    private function rows(string $html): int
    {
        return substr_count($html, ' data-href="');
    }

    public function test_the_first_page_holds_25_in_order_and_counts_everybody(): void
    {
        $this->volume();

        $html = $this->screen('profesor', route('lecture.user.list'))
            ->assertSee('47 usuarios')
            ->assertSee('De la 1 a la 25 de 47')
            ->assertSeeInOrder(['Castro Vidal, Inés', 'García López, Juan', 'Torres Gómez, Pablo', 'Zubiri 01, Ana', 'Zubiri 18, Ana'])
            ->assertDontSee('Zubiri 19, Ana')
            ->assertDontSee('Romero Organvídez, David')
            ->getContent();

        $this->assertSame(25, $this->rows($html));
        $this->assertStringContainsString('href="'.route('lecture.user.list', ['page' => 2]).'" rel="next"', $html);

        // the president neither sees the lecturer nor themselves
        $this->screen('presidente', route('president.user.list'))->assertSee('46 usuarios');
    }

    public function test_the_order_the_filters_and_the_size_stay_in_every_link(): void
    {
        $this->volume();
        $url = route('lecture.user.list', ['rol' => 'STUDENT', 'sort' => 'surname', 'dir' => 'desc', 'per_page' => 25]);

        $html = $this->screen('profesor', $url)
            ->assertSeeInOrder(['Zubiri 40, Ana', 'Zubiri 39, Ana'])
            ->getContent();

        $this->assertStringContainsString('href="'.e($url.'&page=2').'" rel="next"', $html);
        // the sort link of the username keeps the role and the size, back on the first page
        $this->assertStringContainsString('href="'.e(route('lecture.user.list', ['rol' => 'STUDENT', 'sort' => 'username', 'dir' => 'asc', 'per_page' => 25])).'"', $html);
        // the filter bar resends the order and the size, the search form also the role
        $this->assertStringContainsString('<input type="hidden" name="sort" value="surname">', $html);
        $this->assertStringContainsString('<input type="hidden" name="rol" value="STUDENT">', $html);

        $last = $this->screen('profesor', $url.'&page=2')->getContent();

        $this->assertSame(21, $this->rows($last), '46 students but the lecturer, who is not one');
        $this->assertStringContainsString('García López, Juan', $last);
    }

    public function test_the_search_runs_on_the_server_over_name_username_and_email(): void
    {
        $this->volume();

        $this->screen('profesor', route('lecture.user.list', ['q' => 'navarro']))
            ->assertSee('Navarro Ruiz, Carlos')
            ->assertDontSee('Pérez Romero, Lucía')
            ->assertSee('1 usuario')
            ->assertSee('value="navarro"', false);

        $this->screen('profesor', route('lecture.user.list', ['q' => 'anazub07']))->assertSee('Zubiri 07, Ana')->assertDontSee('Zubiri 08, Ana');
        $this->screen('profesor', route('lecture.user.list', ['q' => 'ana.zubiri3']))->assertSee('Zubiri 39, Ana')->assertDontSee('Zubiri 29, Ana');
        $this->screen('profesor', route('lecture.user.list', ['q' => 'ana zubiri 1']))->assertSee('Zubiri 10, Ana')->assertDontSee('Zubiri 20, Ana');

        // the lecturer stays out of the search of the president
        $this->screen('presidente', route('president.user.list', ['q' => 'romero']))
            ->assertSee('Pérez Romero, Lucía')
            ->assertDontSee('Romero Organvídez, David');

        $this->screen('profesor', route('lecture.user.list', ['q' => 'nadie así']))
            ->assertSee('Ningún usuario coincide con la búsqueda')
            ->assertSee('Quitar la búsqueda');
    }

    public function test_a_page_past_the_last_shows_the_last_and_the_blocked_filter_still_works(): void
    {
        $this->volume();
        User::where('username', 'anazub40')->update(['block' => true]);

        $html = $this->screen('profesor', route('lecture.user.list', ['page' => 7]))
            ->assertSee('De la 26 a la 47 de 47')
            ->getContent();

        $this->assertSame(22, $this->rows($html));

        $this->screen('profesor', route('lecture.user.list', ['estado' => 'bloqueados']))
            ->assertSee('Zubiri 40, Ana')
            ->assertSee('1 usuario')
            ->assertDontSee('Zubiri 39, Ana');
        $this->screen('profesor', route('lecture.user.list', ['estado' => 'activos']))->assertSee('46 usuarios');
    }
}
