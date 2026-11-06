<?php

namespace App\Services\Messaging;

use App\Models\Committee;
use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Who a thread is addressed to: a role and, for the coordination and the secretary, a committee.
 * Keys → COORDINATOR:7, SECRETARY:7, REGISTER_COORDINATOR, PRESIDENT, LECTURE.
 */
final class Audience
{
    public const ROLES = ['COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'];

    /** Roles whose audience belongs to a committee */
    private const PER_COMMITTEE = ['COORDINATOR', 'SECRETARY'];

    public function __construct(public readonly string $role, public readonly ?int $committeeId = null) {}

    public static function fromKey(string $key): self
    {
        if (preg_match('/^(COORDINATOR|SECRETARY):([1-9]\d*)$/', $key, $matches)) {
            return new self($matches[1], (int) $matches[2]);
        }
        if (in_array($key, ['REGISTER_COORDINATOR', 'PRESIDENT', 'LECTURE'], true)) {
            return new self($key);
        }

        throw new InvalidArgumentException("Unknown audience [{$key}].");
    }

    public function key(): string
    {
        return $this->perCommittee() ? $this->role.':'.$this->committeeId : $this->role;
    }

    /** «Coordinación de Logística», «Secretaría de Logística», «Coordinación de registro», «Presidencia», «Profesorado» */
    public function label(): string
    {
        $committee = $this->perCommittee() ? Committee::query()->whereKey($this->committeeId)->value('name') : null;

        return match ($this->role) {
            'COORDINATOR' => $committee === null ? 'Coordinación' : 'Coordinación de '.$committee,
            'SECRETARY' => $committee === null ? 'Secretaría' : 'Secretaría de '.$committee,
            'REGISTER_COORDINATOR' => 'Coordinación de registro',
            'PRESIDENT' => 'Presidencia',
            default => 'Profesorado',
        };
    }

    /** @return Collection<int, User> */
    public function members(): Collection
    {
        return $this->membersQuery()->orderBy('surname')->orderBy('name')->get();
    }

    public function includes(User $user): bool
    {
        return match ($this->role) {
            'COORDINATOR' => $user->hasRole('COORDINATOR') && (int) $user->coordinator?->committee_id === $this->committeeId,
            'SECRETARY' => $user->hasRole('SECRETARY') && (int) $user->secretary?->committee_id === $this->committeeId,
            'PRESIDENT' => $user->hasRole('PRESIDENT') || $user->hasRole('LECTURE'),
            default => $user->hasRole($this->role),
        };
    }

    /**
     * Audiences the person belongs to, for the inbox and the filter of addressees.
     *
     * @return Collection<int, self>
     */
    public static function of(User $user): Collection
    {
        $audiences = collect();
        if ($user->hasRole('COORDINATOR') && $user->coordinator?->committee_id !== null) {
            $audiences->push(new self('COORDINATOR', (int) $user->coordinator->committee_id));
        }
        if ($user->hasRole('SECRETARY') && $user->secretary?->committee_id !== null) {
            $audiences->push(new self('SECRETARY', (int) $user->secretary->committee_id));
        }
        if ($user->hasRole('REGISTER_COORDINATOR')) {
            $audiences->push(new self('REGISTER_COORDINATOR'));
        }
        if ($user->hasRole('PRESIDENT') || $user->hasRole('LECTURE')) {
            $audiences->push(new self('PRESIDENT'));
        }
        if ($user->hasRole('LECTURE')) {
            $audiences->push(new self('LECTURE'));
        }

        return $audiences;
    }

    /**
     * Who the author can write to, the suggested one first: about an evidence its coordination, about minutes or a
     * meeting request its secretary, otherwise every committee with someone assigned (the author's own first, those
     * with an accepted evidence before the rest). Never
     * an audience the author belongs to or one without members.
     *
     * @return Collection<int, array{value: string, label: string}>
     */
    public static function optionsFor(User $author, ?Model $about): Collection
    {
        $committeeId = match (true) {
            $about instanceof Evidence => $about->headEvidence()->committee_id,
            $about instanceof MeetingMinutes => $about->meeting?->committee_id,
            $about instanceof MeetingRequest => $about->committee_id,
            default => null,
        };

        if ($about instanceof Evidence) {
            $committees = [$committeeId];
            $order = ['COORDINATOR', 'SECRETARY'];
        } elseif ($about instanceof MeetingMinutes || $about instanceof MeetingRequest) {
            $committees = [$committeeId];
            $order = ['SECRETARY', 'COORDINATOR'];
        } else {
            // The committees of the author's evidences first, those with an accepted one before the rest
            $own = Evidence::where('user_id', $author->getKey())->where('last', true)->whereNotNull('committee_id')
                ->orderByRaw("case when status = 'ACCEPTED' then 0 else 1 end")->orderByDesc('updated_at')
                ->pluck('committee_id')->map(fn ($id): int => (int) $id)->unique()->values()->all();
            $committees = Committee::orderBy('name')->pluck('id')
                ->sortBy(fn ($id): int => ($position = array_search((int) $id, $own, true)) === false ? PHP_INT_MAX : $position)
                ->all();
            $order = ['COORDINATOR', 'SECRETARY'];
        }

        $audiences = collect($committees)
            ->filter(fn ($id): bool => $id !== null)
            ->flatMap(fn ($id): array => array_map(fn (string $role): self => new self($role, (int) $id), $order))
            ->push(new self('REGISTER_COORDINATOR'), new self('PRESIDENT'), new self('LECTURE'));

        return $audiences
            ->reject(fn (self $audience): bool => $audience->includes($author) || ! $audience->membersQuery()->exists())
            ->map(fn (self $audience): array => ['value' => $audience->key(), 'label' => $audience->label()])
            ->values();
    }

    private function perCommittee(): bool
    {
        return in_array($this->role, self::PER_COMMITTEE, true);
    }

    /** @return Builder<User> */
    private function membersQuery(): Builder
    {
        $withRole = fn (array $roles) => fn (Builder $query) => $query->whereIn('rol', $roles);

        return match ($this->role) {
            'COORDINATOR' => User::whereHas('roles', $withRole(['COORDINATOR']))
                ->whereHas('coordinator', fn (Builder $query) => $query->where('committee_id', $this->committeeId)),
            'SECRETARY' => User::whereHas('roles', $withRole(['SECRETARY']))
                ->whereHas('secretary', fn (Builder $query) => $query->where('committee_id', $this->committeeId)),
            'PRESIDENT' => User::whereHas('roles', $withRole(['PRESIDENT', 'LECTURE'])),
            default => User::whereHas('roles', $withRole([$this->role])),
        };
    }
}
