<?php

namespace App\Support;

use BackedEnum;
use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Server order of the lists: the column and the direction come from the query (sort, dir) and only the keys each
 * screen allows are read, each one tied to its real order. Unknown keys or directions fall back to the default of
 * the screen, the newest first. It orders a query (before paginating it) or a collection, and gives x-ui.table the
 * sort links of its columns, which keep the rest of the query string and go back to the first page.
 *
 *     $sort = ListSort::from($request, [
 *         'title' => 'title',                                          // a column of the table
 *         'author' => ['user.surname', 'user.name'],                   // a relation, then another key on a tie
 *         'status' => ListSort::rank('status', ['PENDING', 'ACCEPTED', 'REJECTED']),  // a logical order
 *         'created' => ListSort::desc('created_at'),                   // a date: newest first on the first click
 *         'members' => fn (Builder $query, string $dir) => $query->orderBy('users_count', $dir),
 *     ], default: 'created');
 *     $evidences = $sort->apply(Evidence::where(...))->paginate();      // or ListPage::paginate($sort->apply(...))
 *     $columns = $sort->columns([['label' => 'Evidencia', 'key' => 'title'], ...]);   // in the view
 *
 * Dates always order by their timestamp (created_at, updated_at, datetime...), never by the text the table shows.
 * Over a collection a string reads data_get (user.surname), a closure returns the value of a row
 * (fn ($row) => $row->event?->start_datetime) and the comparison ignores case and accents.
 */
final class ListSort
{
    public const KEY = 'sort';

    public const DIR = 'dir';

    /**
     * @param  array<string, array{terms: list<mixed>, first: string}>  $columns
     */
    private function __construct(
        private readonly array $columns,
        public readonly string $key,
        public readonly string $dir,
        private readonly Request $request,
        private readonly ?string $path,
    ) {}

    /**
     * Order asked by the request among the allowed columns, or the default one (the default direction is desc,
     * the newest first; a column asked without a valid dir takes its own first direction).
     *
     * @param  array<string, string|Closure|array<mixed>>  $columns
     */
    public static function from(Request $request, array $columns, string $default, string $dir = 'desc', ?string $path = null): self
    {
        $columns = array_map(self::normalize(...), $columns);
        throw_unless(isset($columns[$default]), InvalidArgumentException::class, "The default order {$default} is not an allowed column.");

        $asked = $request->query(self::KEY);
        $askedDir = $request->query(self::DIR);
        if (is_string($asked) && isset($columns[$asked])) {
            $direction = in_array($askedDir, ['asc', 'desc'], true) ? $askedDir : $columns[$asked]['first'];

            return new self($columns, $asked, $direction, $request, $path);
        }

        return new self($columns, $default, $dir === 'asc' ? 'asc' : 'desc', $request, $path);
    }

    /**
     * A column whose first click orders it descending: dates, so the newest come first.
     *
     * @return array{terms: list<mixed>, first: string}
     */
    public static function desc(string|Closure|array ...$terms): array
    {
        return ['terms' => self::terms($terms), 'first' => 'desc'];
    }

    /**
     * A logical order of the values of a column (pending, accepted, rejected); other values go last.
     *
     * @param  list<string|int>  $values
     * @return array{rank: string, values: list<string|int>}
     */
    public static function rank(string $column, array $values): array
    {
        return ['rank' => $column, 'values' => array_values($values)];
    }

    /** Whether the list is ordered by this column, and in which direction. */
    public function is(string $key): ?string
    {
        return $this->key === $key ? $this->dir : null;
    }

    /**
     * Orders the rows by the current column, then by the key of the model in the same direction so that the pages
     * never repeat or skip a row. A query loses its previous order; a collection keeps it on a tie.
     *
     * @template T of Builder|Relation|Collection
     *
     * @param  T  $rows
     * @return T
     */
    public function apply(Builder|Relation|Collection $rows): Builder|Relation|Collection
    {
        $terms = $this->columns[$this->key]['terms'];

        if ($rows instanceof Collection) {
            return $this->sortCollection($rows, $terms);
        }

        $rows->reorder();
        foreach ($terms as $index => $term) {
            $this->orderQuery($rows, $term, $index);
        }

        return $rows->orderBy($rows->getModel()->getQualifiedKeyName(), $this->dir);
    }

    /**
     * Sort of a column for x-ui.table: the link that toggles it (its first direction when it is not the current
     * one), the current direction and the links of each direction for the phone control.
     *
     * @return array{href: string, dir: ?string, asc: string, desc: string}|null
     */
    public function column(string $key): ?array
    {
        if (! isset($this->columns[$key])) {
            return null;
        }
        $current = $this->is($key);
        $next = match ($current) {
            'asc' => 'desc',
            'desc' => 'asc',
            default => $this->columns[$key]['first'],
        };

        return [
            'href' => $this->url($key, $next),
            'dir' => $current,
            'asc' => $this->url($key, 'asc'),
            'desc' => $this->url($key, 'desc'),
        ];
    }

    /**
     * The columns of a table with the sort of every allowed key; the rest (actions) stay as they are.
     *
     * @param  iterable<array<string, mixed>>  $columns
     * @return list<array<string, mixed>>
     */
    public function columns(iterable $columns): array
    {
        $sorted = [];
        foreach ($columns as $column) {
            $sort = is_string($column['key'] ?? null) && ! array_key_exists('sort', $column) ? $this->column($column['key']) : null;
            $sorted[] = $sort === null ? $column : $column + ['sort' => $sort];
        }

        return $sorted;
    }

    /** URL of the list ordered by a column in a direction, with the rest of the query string, on the first page. */
    public function url(string $key, string $dir): string
    {
        return ListPage::url([self::KEY => $key, self::DIR => $dir], $this->path, $this->request);
    }

    /**
     * @return array{terms: list<mixed>, first: string}
     */
    private static function normalize(string|Closure|array $column): array
    {
        if (is_array($column) && array_key_exists('terms', $column)) {
            return $column;
        }

        return ['terms' => self::terms([$column]), 'first' => 'asc'];
    }

    /**
     * @param  array<mixed>  $terms
     * @return list<mixed>
     */
    private static function terms(array $terms): array
    {
        $flat = [];
        foreach ($terms as $term) {
            if (is_array($term) && ! array_key_exists('rank', $term)) {
                array_push($flat, ...self::terms($term));
            } elseif (is_string($term) || $term instanceof Closure || is_array($term)) {
                $flat[] = $term;
            } else {
                throw new InvalidArgumentException('A sort term is a column, a relation column, a rank or a closure.');
            }
        }
        throw_if($flat === [], InvalidArgumentException::class, 'A sortable column needs a term.');

        return $flat;
    }

    private function orderQuery(Builder|Relation $query, mixed $term, int $index): void
    {
        if ($term instanceof Closure) {
            $term($query, $this->dir);
        } elseif (is_array($term)) {
            $cases = implode(' ', array_fill(0, count($term['values']), 'when ? then ?'));
            $bindings = [];
            foreach ($term['values'] as $position => $value) {
                array_push($bindings, $value, $position);
            }
            $column = $query->getQuery()->getGrammar()->wrap($query->qualifyColumn($term['rank']));
            $query->orderByRaw("case {$column} {$cases} else ? end {$this->dir}", [...$bindings, count($term['values'])]);
        } elseif (str_contains($term, '.')) {
            // A column of a relation, as a subquery: the order needs no join and keeps the selected columns
            $alias = 'sort_'.$index.'_'.Str::snake(str_replace('.', '_', $term));
            $query->withAggregate(Str::beforeLast($term, '.').' as '.$alias, Str::afterLast($term, '.'), 'max');
            $query->orderBy($alias, $this->dir);
        } else {
            $query->orderBy($query->qualifyColumn($term), $this->dir);
        }
    }

    /**
     * @param  list<mixed>  $terms
     */
    private function sortCollection(Collection $rows, array $terms): Collection
    {
        $keyed = $rows->map(fn ($row, $key): array => [
            'row' => $row,
            'key' => $key,
            'values' => array_map(fn (mixed $term): mixed => $this->valueOf($row, $term), $terms),
        ])->all();

        $sign = $this->dir === 'desc' ? -1 : 1;
        uasort($keyed, function (array $a, array $b) use ($sign): int {
            foreach ($a['values'] as $index => $value) {
                $order = self::compare($value, $b['values'][$index]);
                if ($order !== 0) {
                    return $order * $sign;
                }
            }

            return 0;
        });

        return collect(array_column($keyed, 'row'))->values();
    }

    private function valueOf(mixed $row, mixed $term): mixed
    {
        if ($term instanceof Closure) {
            return $term($row);
        }
        if (is_array($term)) {
            $position = array_search(data_get($row, $term['rank']), $term['values'], true);

            return $position === false ? count($term['values']) : $position;
        }

        return data_get($row, $term);
    }

    /** Nulls first, dates by their timestamp, numbers as numbers and texts without case or accents. */
    private static function compare(mixed $a, mixed $b): int
    {
        $a = self::comparable($a);
        $b = self::comparable($b);

        return match (true) {
            $a === null || $b === null => ($b === null) <=> ($a === null),
            is_float($a) && is_float($b) => $a <=> $b,
            default => strnatcmp((string) $a, (string) $b),
        };
    }

    private static function comparable(mixed $value): float|string|null
    {
        return match (true) {
            $value === null || $value === '' => null,
            $value instanceof DateTimeInterface => (float) $value->format('U.u'),
            $value instanceof BackedEnum => self::comparable($value->value),
            is_bool($value) => (float) $value,
            is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)) => (float) $value,
            default => Str::lower(Str::ascii((string) $value)),
        };
    }
}
