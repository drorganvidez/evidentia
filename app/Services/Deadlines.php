<?php

namespace App\Services;

use App\Enums\Deadline;
use App\Models\Configuration;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use WeakMap;

/**
 * The deadlines of the course with the same meaning as their middleware: a deadline that is not set is open, and the
 * closing instant itself is still open. The configuration row is read once per request.
 */
final class Deadlines
{
    /** @var WeakMap<object, Configuration|false> */
    private WeakMap $rows;

    public function __construct()
    {
        $this->rows = new WeakMap;
    }

    public function closesAt(Deadline $deadline): ?CarbonImmutable
    {
        $value = $this->configuration()?->getAttribute($deadline->column());

        return blank($value) ? null : CarbonImmutable::parse($value, config('app.timezone'));
    }

    public function isSet(Deadline $deadline): bool
    {
        return $this->closesAt($deadline) !== null;
    }

    /** Not set → open; otherwise open until the closing instant included (the middleware abort with now()->gt()). */
    public function isOpen(Deadline $deadline): bool
    {
        $closesAt = $this->closesAt($deadline);

        return $closesAt === null || now()->lte($closesAt);
    }

    /** Strict variant of the menus: only a deadline that is set and still open. */
    public function isOpenForMenu(Deadline $deadline): bool
    {
        return $this->isSet($deadline) && $this->isOpen($deadline);
    }

    /** Time left; null when the deadline is not set or already closed. */
    public function remaining(Deadline $deadline): ?CarbonInterval
    {
        if (! $this->isOpenForMenu($deadline)) {
            return null;
        }

        return now()->diffAsCarbonInterval($this->closesAt($deadline));
    }

    /** «cierra hoy a las 20:00», «queda un día», «quedan 9 días»; null when not set or already closed. */
    public function remainingText(Deadline $deadline): ?string
    {
        if (! $this->isOpenForMenu($deadline)) {
            return null;
        }

        $closesAt = $this->closesAt($deadline);
        $days = (int) now()->startOfDay()->diffInDays($closesAt->startOfDay());

        return match (true) {
            $days <= 0 => 'cierra hoy a las '.$closesAt->format('H:i'),
            $days === 1 => 'queda un día',
            default => 'quedan '.$days.' días',
        };
    }

    /**
     * Data of the deadline card of the sidebar; null unless the deadline is set and open.
     *
     * @return array{label: string, closesAt: CarbonImmutable, opensAt: null, href: string}|null
     */
    public function card(Deadline $deadline): ?array
    {
        if (! $this->isOpenForMenu($deadline)) {
            return null;
        }

        return [
            'label' => $deadline->label(),
            'closesAt' => $this->closesAt($deadline),
            'opensAt' => null,
            'href' => route($deadline->listRoute()),
        ];
    }

    /** Forgets the configuration read in this request, after the deadlines change. */
    public function refresh(): void
    {
        unset($this->rows[request()]);
    }

    private function configuration(): ?Configuration
    {
        $request = request();
        if (! isset($this->rows[$request])) {
            $this->rows[$request] = Configuration::query()->find(1) ?? false;
        }

        return $this->rows[$request] ?: null;
    }
}
