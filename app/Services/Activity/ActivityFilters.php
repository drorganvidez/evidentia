<?php

namespace App\Services\Activity;

use App\Enums\ActivityAction;
use App\Support\Roles;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Throwable;

/**
 * Filters of the activity log read from the query string. Invalid values are ignored, never a validation error;
 * a committee out of the scope of the viewer is ignored too.
 */
final class ActivityFilters
{
    private function __construct(
        public readonly ?string $type,
        public readonly ?int $user,
        public readonly ?string $role,
        public readonly ?int $committee,
        public readonly ?string $from,
        public readonly ?string $to,
        public readonly bool $flagged,
        public readonly bool $all,
        public readonly ?string $request,
    ) {}

    /** @param  list<int>|null  $committeeScope  null for the management */
    public static function fromRequest(Request $request, ?array $committeeScope): self
    {
        $type = self::string($request->query('type'));
        $validType = $type !== null && (ActivityAction::tryFrom($type) !== null
            || (str_starts_with($type, 'group:') && array_key_exists(substr($type, 6), ActivityAction::groups())));

        $role = self::string($request->query('role'));
        $committee = self::id($request->query('committee'));
        if ($committee !== null && $committeeScope !== null && ! in_array($committee, $committeeScope, true)) {
            $committee = null;
        }
        $requestId = self::string($request->query('request'));

        return new self(
            type: $validType ? $type : null,
            user: self::id($request->query('user')),
            role: $role !== null && array_key_exists($role, Roles::LABELS) ? $role : null,
            committee: $committee,
            from: self::date($request->query('from')),
            to: self::date($request->query('to')),
            flagged: $request->boolean('flagged'),
            all: $request->boolean('all'),
            request: $requestId !== null && preg_match('/^[A-Za-z0-9-]{1,64}$/', $requestId) ? $requestId : null,
        );
    }

    /** Narrows the query; without a type, a request or «all» the accesses and the sent notices stay out. */
    public function apply(Builder $query): Builder
    {
        return $query
            ->when($this->type !== null, fn (Builder $query) => $query->ofType($this->type))
            ->when($this->user !== null, fn (Builder $query) => $query->forUser($this->user))
            ->when($this->role !== null, fn (Builder $query) => $query->where('acting_role', $this->role))
            ->when($this->committee !== null, fn (Builder $query) => $query->where('committee_id', $this->committee))
            ->when($this->from !== null || $this->to !== null, fn (Builder $query) => $query->between(
                $this->from !== null ? CarbonImmutable::parse($this->from)->startOfDay() : null,
                $this->to !== null ? CarbonImmutable::parse($this->to)->endOfDay() : null,
            ))
            ->when($this->flagged, fn (Builder $query) => $query->where('flagged', true))
            ->when($this->request !== null, fn (Builder $query) => $query->where('request_id', $this->request))
            ->when($this->type === null && $this->request === null && ! $this->all, fn (Builder $query) => $query->withoutNoise());
    }

    /** Visible filters in use, for the counter of the filter bar. */
    public function active(): int
    {
        return count(array_filter([
            $this->type, $this->user, $this->role, $this->committee, $this->from, $this->to,
            $this->flagged ?: null, $this->all ?: null,
        ], fn ($value): bool => $value !== null));
    }

    /** @return array<string, string|int> the filters in use, for links that keep them */
    public function query(): array
    {
        return array_filter([
            'type' => $this->type,
            'user' => $this->user,
            'role' => $this->role,
            'committee' => $this->committee,
            'from' => $this->from,
            'to' => $this->to,
            'flagged' => $this->flagged ? 1 : null,
            'all' => $this->all ? 1 : null,
            'request' => $this->request,
        ], fn ($value): bool => $value !== null);
    }

    private static function string(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private static function id(mixed $value): ?int
    {
        return is_string($value) && ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
    }

    private static function date(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $value)->format('Y-m-d') === $value ? $value : null;
        } catch (Throwable) {
            return null;
        }
    }
}
