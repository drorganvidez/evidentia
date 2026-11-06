<?php

namespace App\Services;

use App\Enums\Deadline;
use App\Models\Configuration;
use Carbon\CarbonImmutable;
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
