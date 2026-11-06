<?php

namespace Tests\Feature;

use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;
use App\Support\ListPage;
use App\Support\ListSort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

class ListSortTest extends TestCase
{
    use RefreshDatabase;

    /** Makes the request of the list the current one, as a GET to /lista with the query string. */
    private function asked(array $query = []): Request
    {
        $request = Request::create('http://localhost/lista', 'GET', $query);
        $this->app->instance('request', $request);

        return $request;
    }

    private function sort(array $query = [], string $default = 'created'): ListSort
    {
        return ListSort::from($this->asked($query), [
            'title' => 'title',
            'author' => ['user.surname', 'user.name'],
            'hours' => 'hours',
            'status' => ListSort::rank('status', ['PENDING', 'ACCEPTED', 'REJECTED']),
            'created' => ListSort::desc('created_at'),
            'updated' => ListSort::desc(fn (Builder $query, string $dir) => $query->orderBy('updated_at', $dir)),
        ], $default);
    }

    /** Three evidences of different people, created on purpose out of the order of their ids. */
    private function evidences(): void
    {
        $committee = Committee::create(['name' => 'Logística']);
        $rows = [
            ['Montaje del photocall', 'Lucía', 'Pérez Romero', 'ACCEPTED', 2.5, '2026-10-01 09:00:00'],
            ['atención en el registro', 'Juan', 'Alvarez Gil', 'REJECTED', 10, '2026-11-02 18:30:00'],
            ['Diseño del cartel', 'Ana', 'Pérez Romero', 'PENDING', 1, '2026-09-15 12:00:00'],
        ];
        foreach ($rows as [$title, $name, $surname, $status, $hours, $created]) {
            $author = User::factory()->create(['name' => $name, 'surname' => $surname]);
            $evidence = Evidence::factory()->for($author, 'user')->for($committee)
                ->create(['title' => $title, 'status' => $status, 'hours' => $hours]);
            $evidence->forceFill(['created_at' => Carbon::parse($created), 'updated_at' => Carbon::parse($created)->addDays(3)])->saveQuietly();
        }
    }

    private function titles(ListSort $sort): array
    {
        return $sort->apply(Evidence::query()->latest('id'))->pluck('title')->all();
    }

    public function test_the_default_order_is_the_newest_first_by_the_real_date(): void
    {
        $this->evidences();

        $sort = $this->sort();

        $this->assertSame('created', $sort->key);
        $this->assertSame('desc', $sort->dir);
        $this->assertSame(['atención en el registro', 'Montaje del photocall', 'Diseño del cartel'], $this->titles($sort));
        $this->assertSame(
            ['Diseño del cartel', 'Montaje del photocall', 'atención en el registro'],
            $this->titles($this->sort(['sort' => 'created', 'dir' => 'asc'])),
        );
    }

    public function test_only_the_allowed_keys_and_directions_are_read(): void
    {
        foreach ([
            ['sort' => 'password'],
            ['sort' => 'created_at'],
            ['sort' => ['title']],
            ['sort' => 'title; drop table users'],
            ['sort' => ''],
            ['dir' => 'asc'],
        ] as $query) {
            $sort = $this->sort($query);
            $this->assertSame(['created', 'desc'], [$sort->key, $sort->dir], json_encode($query));
        }

        $this->assertSame(['title', 'asc'], [$this->sort(['sort' => 'title', 'dir' => 'sideways'])->key, $this->sort(['sort' => 'title', 'dir' => 'sideways'])->dir]);
        $this->assertSame('desc', $this->sort(['sort' => 'title', 'dir' => 'desc'])->dir);
        $this->assertSame('desc', $this->sort(['sort' => 'updated'])->dir, 'A date asked without a direction starts with the newest');
        $this->assertSame('desc', $this->sort(['sort' => 'created', 'dir' => ['asc']])->dir, 'An array is not a direction');
    }

    public function test_the_default_must_be_an_allowed_column(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ListSort::from($this->asked(), ['title' => 'title'], 'created');
    }

    public function test_texts_people_numbers_and_states_order_by_their_real_value(): void
    {
        $this->evidences();

        $this->assertSame(['Diseño del cartel', 'Montaje del photocall', 'atención en el registro'], $this->titles($this->sort(['sort' => 'hours', 'dir' => 'asc'])), '2.5 before 10');
        $this->assertSame(['Diseño del cartel', 'Montaje del photocall', 'atención en el registro'], $this->titles($this->sort(['sort' => 'status', 'dir' => 'asc'])), 'Pending, accepted, rejected');
        $this->assertSame(['atención en el registro', 'Montaje del photocall', 'Diseño del cartel'], $this->titles($this->sort(['sort' => 'status', 'dir' => 'desc'])));
        $this->assertSame(['atención en el registro', 'Diseño del cartel', 'Montaje del photocall'], $this->titles($this->sort(['sort' => 'author', 'dir' => 'asc'])), 'Surname, then name');
        $this->assertSame(['Montaje del photocall', 'Diseño del cartel', 'atención en el registro'], $this->titles($this->sort(['sort' => 'author', 'dir' => 'desc'])));
        $this->assertSame(['atención en el registro', 'Montaje del photocall', 'Diseño del cartel'], $this->titles($this->sort(['sort' => 'updated'])), 'A closure orders the query');
    }

    public function test_the_query_loses_its_previous_order_and_ties_go_by_the_key(): void
    {
        $committee = Committee::create(['name' => 'Logística']);
        $ids = Evidence::factory()->count(4)->for($committee)->create(['hours' => 3])->pluck('id')->all();

        $this->assertSame(array_reverse($ids), $this->sort(['sort' => 'hours', 'dir' => 'desc'])->apply(Evidence::query()->orderBy('title'))->pluck('id')->all());
        $this->assertSame($ids, $this->sort(['sort' => 'hours', 'dir' => 'asc'])->apply($committee->evidences())->pluck('id')->all(), 'A relation is ordered the same way');
    }

    public function test_a_collection_orders_by_dates_numbers_and_texts_without_case_or_accents(): void
    {
        $rows = collect([
            ['name' => 'Óscar', 'when' => Carbon::parse('2026-11-01 10:00'), 'hours' => '10', 'state' => 'REJECTED'],
            ['name' => 'ana', 'when' => Carbon::parse('2026-01-05 10:00'), 'hours' => '9.5', 'state' => 'PENDING'],
            ['name' => 'Bea', 'when' => null, 'hours' => null, 'state' => 'ACCEPTED'],
        ]);
        $sort = fn (array $query) => ListSort::from($this->asked($query), [
            'name' => 'name',
            'when' => ListSort::desc(fn (array $row) => $row['when']),
            'hours' => 'hours',
            'state' => ListSort::rank('state', ['PENDING', 'ACCEPTED', 'REJECTED']),
        ], 'when');
        $names = fn (array $query): array => $sort($query)->apply($rows)->pluck('name')->all();

        $this->assertSame(['Óscar', 'ana', 'Bea'], $names([]), 'The newest first, an empty date last');
        $this->assertSame(['Bea', 'ana', 'Óscar'], $names(['sort' => 'when', 'dir' => 'asc']));
        $this->assertSame(['ana', 'Bea', 'Óscar'], $names(['sort' => 'name', 'dir' => 'asc']));
        $this->assertSame(['Bea', 'ana', 'Óscar'], $names(['sort' => 'hours', 'dir' => 'asc']), '9.5 before 10');
        $this->assertSame(['ana', 'Bea', 'Óscar'], $names(['sort' => 'state', 'dir' => 'asc']));
        $this->assertSame([0, 1, 2], $sort(['sort' => 'name'])->apply($rows)->keys()->all(), 'Keys start again');
    }

    public function test_a_term_may_order_a_query_and_a_collection_alike(): void
    {
        $this->evidences();
        $term = ListSort::using(
            fn (Builder $query, string $dir) => $query->orderByRaw('length(title) '.$dir),
            fn (Evidence $evidence) => mb_strlen($evidence->title),
        );
        $sort = ListSort::from($this->asked(['sort' => 'length', 'dir' => 'asc']), ['length' => $term, 'created' => ListSort::desc('created_at')], 'created');
        $expected = ['Diseño del cartel', 'Montaje del photocall', 'atención en el registro'];

        $this->assertSame($expected, $sort->apply(Evidence::query())->pluck('title')->all());
        $this->assertSame($expected, $sort->apply(Evidence::all())->pluck('title')->all());
        $this->assertInstanceOf(Collection::class, $sort->apply(Evidence::all()), 'A collection of models stays one');
    }

    public function test_a_count_of_a_relation_orders_a_query_and_a_collection(): void
    {
        $small = Committee::create(['name' => 'Pequeño']);
        $big = Committee::create(['name' => 'Grande']);
        Committee::create(['name' => 'Vacío']);
        Evidence::factory()->count(1)->for($small)->create();
        Evidence::factory()->count(3)->for($big)->create();
        $sort = ListSort::from($this->asked(['sort' => 'evidences', 'dir' => 'desc']), ['evidences' => ListSort::count('evidences'), 'name' => 'name'], 'name', 'asc');

        $this->assertSame(['Grande', 'Pequeño', 'Vacío'], $sort->apply(Committee::query())->pluck('name')->all());
        $this->assertSame(['Grande', 'Pequeño', 'Vacío'], $sort->apply(Committee::with('evidences')->get())->pluck('name')->all());
        $this->assertSame(['Grande', 'Pequeño', 'Vacío'], $sort->apply(Committee::withCount('evidences')->get())->pluck('name')->all());
    }

    public function test_the_columns_take_links_that_keep_the_query_and_go_back_to_the_first_page(): void
    {
        $sort = $this->sort(['estado' => 'pendientes', 'q' => 'montaje', 'per_page' => '50', 'page' => '3', 'sort' => 'title', 'dir' => 'asc']);

        $this->assertSame([
            'href' => 'http://localhost/lista?estado=pendientes&q=montaje&per_page=50&sort=title&dir=desc',
            'dir' => 'asc',
            'asc' => 'http://localhost/lista?estado=pendientes&q=montaje&per_page=50&sort=title&dir=asc',
            'desc' => 'http://localhost/lista?estado=pendientes&q=montaje&per_page=50&sort=title&dir=desc',
        ], $sort->column('title'));
        $this->assertSame('http://localhost/lista?estado=pendientes&q=montaje&per_page=50&sort=created&dir=desc', $sort->column('created')['href'], 'A date starts with the newest');
        $this->assertSame('http://localhost/lista?estado=pendientes&q=montaje&per_page=50&sort=hours&dir=asc', $sort->column('hours')['href']);
        $this->assertNull($sort->column('created')['dir']);
        $this->assertNull($sort->column('actions'));
        $this->assertDoesNotMatchRegularExpression('/[?&]'.ListPage::PAGE.'=/', $sort->column('hours')['href'], 'Back on the first page');

        $columns = $sort->columns([
            ['label' => 'Evidencia', 'key' => 'title'],
            ['label' => 'Comité', 'key' => 'committee'],
            ['label' => 'Creada', 'key' => 'created', 'sort' => null],
            ['label' => 'Acciones', 'srOnly' => true],
        ]);

        $this->assertSame('asc', $columns[0]['sort']['dir']);
        $this->assertArrayNotHasKey('sort', $columns[1], 'A key that is not allowed is not sortable');
        $this->assertNull($columns[2]['sort'], 'A column may opt out');
        $this->assertArrayNotHasKey('sort', $columns[3]);
    }

    public function test_the_default_column_toggles_to_the_oldest_first_and_a_path_may_be_given(): void
    {
        $sort = ListSort::from($this->asked(['comite' => '3']), ['created' => ListSort::desc('created_at')], 'created', path: '/otra');

        $this->assertSame('desc', $sort->column('created')['dir']);
        $this->assertSame('/otra?comite=3&sort=created&dir=asc', $sort->column('created')['href']);
        $this->assertSame('desc', $sort->is('created'));
        $this->assertNull($sort->is('title'));
    }
}
