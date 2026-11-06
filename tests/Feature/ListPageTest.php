<?php

namespace Tests\Feature;

use App\Http\Controllers\Concerns\PaginatesLists;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\User;
use App\Support\ListPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ListPageTest extends TestCase
{
    use RefreshDatabase;

    /** Makes the request of the list the current one, as a GET to /lista with the query string. */
    private function asked(array $query = []): Request
    {
        $request = Request::create('http://localhost/lista', 'GET', $query);
        $this->app->instance('request', $request);

        return $request;
    }

    /** Evidences of one author in a new committee, Logística, which it returns: the migrations also create the presidency. */
    private function evidences(int $count, array $attributes = []): Committee
    {
        $author = User::factory()->create(['name' => 'Lucía', 'surname' => 'Pérez Romero']);
        $committee = Committee::create(['name' => 'Logística']);
        Evidence::factory()->count($count)->for($author, 'user')->for($committee)->create($attributes);

        return $committee;
    }

    public function test_the_size_is_10_by_default_and_the_query_chooses_25_or_50(): void
    {
        $this->assertSame(10, ListPage::size($this->asked()));
        $this->assertSame(25, ListPage::size($this->asked(['per_page' => '25'])));
        $this->assertSame(50, ListPage::size($this->asked(['per_page' => '50'])));
        $this->assertSame(10, ListPage::size($this->asked(['per_page' => '100'])), 'No longer a size');
        $this->assertSame(10, ListPage::size($this->asked(['per_page' => '30'])));
        $this->assertSame(10, ListPage::size($this->asked(['per_page' => 'todas'])));
        $this->assertSame(10, ListPage::size($this->asked(['per_page' => ['25']])));
        $this->assertSame(50, ListPage::size($this->asked(), 50), 'A list may default to another size');
    }

    public function test_the_term_is_squished_cut_and_only_a_string(): void
    {
        $this->assertNull(ListPage::term($this->asked()));
        $this->assertNull(ListPage::term($this->asked(['q' => '   '])));
        $this->assertNull(ListPage::term($this->asked(['q' => ['montaje']])));
        $this->assertSame('montaje del photocall', ListPage::term($this->asked(['q' => "  montaje \t del   photocall "])));
        $this->assertSame(100, mb_strlen(ListPage::term($this->asked(['q' => str_repeat('á', 300)]))));
    }

    public function test_a_query_is_paginated_with_its_whole_query_string_in_the_links(): void
    {
        $this->evidences(25);
        $request = $this->asked(['estado' => 'pendientes', 'comite' => '3', 'sort' => 'title', 'page' => '2']);

        $page = ListPage::paginate(Evidence::query()->latest('id'), $request);

        $this->assertSame(25, $page->total());
        $this->assertSame(10, $page->perPage());
        $this->assertSame(2, $page->currentPage());
        $this->assertSame(3, $page->lastPage());
        $this->assertCount(10, $page->items());
        $this->assertSame(Evidence::orderByDesc('id')->skip(10)->value('id'), $page->items()[0]->id, 'Newest first, second page');
        $this->assertSame('http://localhost/lista?estado=pendientes&comite=3&sort=title&page=3', $page->nextPageUrl());
        $this->assertSame('http://localhost/lista?estado=pendientes&comite=3&sort=title&page=1', $page->previousPageUrl());
    }

    public function test_the_size_of_the_query_string_sets_the_page_and_stays_in_the_links(): void
    {
        $this->evidences(120);

        $page = ListPage::paginate(Evidence::query(), $this->asked(['per_page' => '50']));

        $this->assertSame(50, $page->perPage());
        $this->assertSame(3, $page->lastPage());
        $this->assertSame('http://localhost/lista?per_page=50&page=2', $page->nextPageUrl());
    }

    public function test_a_page_past_the_last_shows_the_last_and_an_empty_list_its_only_page(): void
    {
        $this->evidences(25);

        $page = ListPage::paginate(Evidence::query(), $this->asked(['page' => '99']));

        $this->assertSame(3, $page->currentPage());
        $this->assertCount(5, $page->items());
        $this->assertNull($page->nextPageUrl());

        $none = ListPage::paginate(Evidence::where('status', 'ACCEPTED'), $this->asked(['page' => '4']));

        $this->assertSame(1, $none->currentPage());
        $this->assertSame(0, $none->total());
        $this->assertFalse($none->hasPages());

        $this->assertSame(1, ListPage::paginate(Evidence::query(), $this->asked(['page' => 'última']))->currentPage());
        $this->assertSame(1, ListPage::paginate(Evidence::query(), $this->asked(['page' => '-2']))->currentPage());
    }

    public function test_a_relation_and_a_collection_are_paginated_the_same_way(): void
    {
        $committee = $this->evidences(15);

        $relation = ListPage::paginate($committee->evidences(), $this->asked(['page' => '2']));

        $this->assertSame(15, $relation->total());
        $this->assertCount(5, $relation->items());

        $collection = ListPage::paginate(collect(range(1, 15)), $this->asked(['page' => '9', 'q' => 'x']));

        $this->assertSame(2, $collection->currentPage());
        $this->assertSame([11, 12, 13, 14, 15], $collection->items());
        $this->assertSame('http://localhost/lista?q=x&page=1', $collection->previousPageUrl());
    }

    public function test_the_search_needs_every_word_in_some_column_also_through_relations(): void
    {
        $committee = $this->evidences(3);
        Evidence::query()->orderBy('id')->first()->update(['title' => 'Montaje del photocall']);
        $other = User::factory()->create(['name' => 'Juan', 'surname' => 'García López']);
        Evidence::factory()->for($other, 'user')->for($committee)->create(['title' => 'Atención en el registro']);
        $columns = ['title', 'user.name', 'user.surname', 'committee.name'];
        $found = fn (?string $term): array => ListPage::search(Evidence::query(), $term, $columns)->orderBy('id')->pluck('title')->all();

        $this->assertCount(4, $found(null));
        $this->assertSame(['Montaje del photocall'], $found('photocall'));
        $this->assertSame(['Montaje del photocall'], $found('PHOTOCALL montaje'), 'Words in any order, without case');
        $this->assertSame(['Atención en el registro'], $found('garcía'));
        $this->assertSame(['Atención en el registro'], $found('juan registro'), 'One word in the relation, another in the title');
        $this->assertCount(4, $found('logística'));
        $this->assertSame([], $found('photocall garcía'));
        $this->assertSame([], $found('%'), 'A wildcard is text');
        $this->assertSame([], $found('mont_je'));
        $this->assertSame([], $found('!'));
    }

    public function test_the_controllers_reach_it_through_the_trait(): void
    {
        $this->evidences(30);
        $controller = new class
        {
            use PaginatesLists;

            public function index(Request $request)
            {
                return $this->paginateList($this->searchList(Evidence::query(), $request, ['title']), $request, 50);
            }
        };

        $page = $controller->index($this->asked(['q' => 'Evidencia']));

        $this->assertSame(50, $page->perPage());
        $this->assertSame(30, $page->total());
        $this->assertSame(0, $controller->index($this->asked(['q' => 'nada parecido']))->total());
    }

    public function test_the_url_changes_the_query_string_and_goes_back_to_the_first_page(): void
    {
        $this->asked(['estado' => 'pendientes', 'q' => 'montaje', 'page' => '3', 'per_page' => '50']);

        $this->assertSame('http://localhost/lista?estado=pendientes&q=montaje&per_page=50', ListPage::url());
        $this->assertSame('http://localhost/lista?estado=aceptadas&q=montaje&per_page=50', ListPage::url(['estado' => 'aceptadas']));
        $this->assertSame('http://localhost/lista?per_page=50', ListPage::url(['estado' => null, 'q' => '']));
        $this->assertSame('/otra?estado=pendientes&q=montaje&per_page=50&sort=title', ListPage::url(['sort' => 'title'], '/otra'));
        $this->assertSame(['estado' => 'pendientes', 'per_page' => '50'], ListPage::query(['q']));
    }

    public function test_the_fields_resend_a_nested_query_string(): void
    {
        $this->assertSame(
            ['estado' => 'pendientes', 'type[0]' => 'a', 'type[1]' => 'b', 'range[from]' => '2026-01-01'],
            ListPage::fields(['estado' => 'pendientes', 'type' => ['a', 'b'], 'range' => ['from' => '2026-01-01']]),
        );
    }
}
