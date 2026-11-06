<?php

namespace App\Http\Controllers\Concerns;

use App\Support\ListPage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Server pagination for the controllers of the lists (see App\Support\ListPage). Filter, search, count and sum over
 * the whole query, then order it by its App\Support\ListSort and paginate it last:
 *
 *     $query = $this->searchList(Evidence::where(...), $request, ['title', 'user.name', 'user.surname']);
 *     $total = $query->clone()->sum('hours');
 *     $evidences = $this->paginateList($sort->apply($query), $request);
 *
 * and give the paginator to <x-ui.table :paginator="$evidences" :columns="$sort->columns([...])" filterable>.
 */
trait PaginatesLists
{
    /**
     * @param  list<string>  $columns
     */
    protected function searchList(Builder|Relation $query, Request $request, array $columns): Builder|Relation
    {
        return ListPage::search($query, ListPage::term($request), $columns);
    }

    protected function paginateList(Builder|Relation|Collection $rows, Request $request, int $default = ListPage::DEFAULT_SIZE): LengthAwarePaginator
    {
        return ListPage::paginate($rows, $request, $default);
    }
}
