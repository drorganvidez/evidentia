<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Server pagination of the lists: the size of the page (10 by default, 25 or 50 by the query), the search q over the
 * columns each screen names, the query string kept in every link and the last valid page when a missing one is asked.
 * Controllers reach it through App\Http\Controllers\Concerns\PaginatesLists; views build their links with url().
 *
 *     $evidences = ListPage::search($query, ListPage::term($request), ['title', 'user.name', 'committee.name']);
 *     $page = ListPage::paginate($evidences, $request);   // LengthAwarePaginator with the query string
 *     ListPage::url(['estado' => 'pendientes']);          // the current list with another tab, back on page 1
 */
final class ListPage
{
    public const SIZES = [10, 25, 50];

    public const DEFAULT_SIZE = 10;

    public const PAGE = 'page';

    public const SIZE = 'per_page';

    public const SEARCH = 'q';

    private const MAX_LENGTH = 100;

    private const MAX_WORDS = 8;

    /** Size asked by the query when it is one of SIZES, otherwise the default of the list. */
    public static function size(Request $request, int $default = self::DEFAULT_SIZE): int
    {
        $asked = filter_var($request->query(self::SIZE), FILTER_VALIDATE_INT);

        return in_array($asked, self::SIZES, true) ? $asked : $default;
    }

    /** Search of the query, trimmed, with single spaces and cut to 100 characters; null when there is none. */
    public static function term(Request $request): ?string
    {
        $value = $request->query(self::SEARCH);
        if (! is_string($value)) {
            return null;
        }
        $term = Str::limit(Str::squish($value), self::MAX_LENGTH, '');

        return $term === '' ? null : $term;
    }

    /**
     * Keeps the rows where every word of the term is in one of the columns. A column with dots goes through its
     * relations (user.name, users.surname). The comparison is the one of the database: without case or accents
     * on MySQL, without case on SQLite.
     *
     * @param  list<string>  $columns
     */
    public static function search(Builder|Relation $query, ?string $term, array $columns): Builder|Relation
    {
        $words = $term === null ? [] : array_slice(array_unique(explode(' ', $term)), 0, self::MAX_WORDS);

        foreach ($words as $word) {
            // % and _ are text, not wildcards: a search never widens the list
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $word).'%';
            $query->where(function (Builder $query) use ($columns, $like) {
                foreach ($columns as $column) {
                    if (str_contains($column, '.')) {
                        $query->orWhereHas(Str::beforeLast($column, '.'), fn (Builder $related) => self::like($related, Str::afterLast($column, '.'), $like));
                    } else {
                        $query->orWhere(fn (Builder $own) => self::like($own, $column, $like));
                    }
                }
            });
        }

        return $query;
    }

    /** column like ? escape '!', the same on MySQL and SQLite; the column is the screen's, never the person's. */
    private static function like(Builder $query, string $column, string $like): void
    {
        $query->whereRaw($query->getQuery()->getGrammar()->wrap($query->qualifyColumn($column))." like ? escape '!'", [$like]);
    }

    /**
     * One page of the rows, a query or a collection already in order, with the whole query string in its links.
     * A page past the last one shows the last; an empty list shows its only page.
     */
    public static function paginate(Builder|Relation|Collection $rows, Request $request, int $default = self::DEFAULT_SIZE): LengthAwarePaginatorContract
    {
        $size = self::size($request, $default);
        $asked = Paginator::resolveCurrentPage(self::PAGE);

        if ($rows instanceof Collection) {
            $total = $rows->count();
            $page = min($asked, max(1, (int) ceil($total / $size)));

            return (new LengthAwarePaginator($rows->forPage($page, $size)->values(), $total, $size, $page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => self::PAGE,
            ]))->withQueryString();
        }

        $query = $rows instanceof Relation ? $rows->getQuery() : $rows;
        $total = $query->toBase()->getCountForPagination();
        $page = min($asked, max(1, (int) ceil($total / $size)));

        return $query->paginate($size, ['*'], self::PAGE, $page, $total)->withQueryString();
    }

    /**
     * The current query string without the page and the given keys, to resend it from a form.
     *
     * @param  list<string>  $except
     * @return array<string, mixed>
     */
    public static function query(array $except = [], ?Request $request = null): array
    {
        return Arr::except(($request ?? request())->query(), [self::PAGE, ...$except]);
    }

    /**
     * URL of the current list (or of path) with the query string changed and back on the first page: a null value
     * removes its key. Tabs, sort links and the reset of a filter bar keep the rest of the filters this way.
     *
     * @param  array<string, mixed>  $changes
     */
    public static function url(array $changes = [], ?string $path = null, ?Request $request = null): string
    {
        $request ??= request();
        $query = array_filter(
            array_merge(self::query([], $request), $changes),
            fn ($value): bool => $value !== null && $value !== '' && $value !== [],
        );

        return ($path ?? $request->url()).($query === [] ? '' : '?'.Arr::query($query));
    }

    /**
     * Hidden fields that resend a query string, nested arrays included (type[]=a becomes type[0]).
     *
     * @param  array<string, mixed>  $query
     * @return array<string, string>
     */
    public static function fields(array $query, string $prefix = ''): array
    {
        $fields = [];
        foreach ($query as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix.'['.$key.']';
            if (is_array($value)) {
                $fields += self::fields($value, $name);
            } elseif (is_scalar($value)) {
                $fields[$name] = (string) $value;
            }
        }

        return $fields;
    }
}
