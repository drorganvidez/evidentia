<?php

namespace App\Notifications\Concerns;

use App\Models\Evidence;
use App\Models\User;
use App\Services\ReviewQueue;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Route;

/**
 * Shared pieces of the texts of the notices: names, hours, dates and the links to the pages of the app.
 */
trait FormatsNotices
{
    /**
     * A page of the app; while the page of the activity log is not there yet, the page that shows the same thing
     * today.
     */
    protected static function link(string $route, array $parameters, string $fallback, array $fallbackParameters = []): string
    {
        return Route::has($route) ? route($route, $parameters) : route($fallback, $fallbackParameters);
    }

    /** 1,75 · 2 · 0,5 */
    protected static function hours(mixed $hours): string
    {
        $text = number_format((float) $hours, 2, ',', '.');

        return str_contains($text, ',') ? rtrim(rtrim($text, '0'), ',') : $text;
    }

    /** «Nombre Apellidos» of the person or, when it is gone, of the copied «Apellidos, Nombre». */
    protected static function fullName(?User $user, ?string $copied = null): string
    {
        if ($user !== null) {
            return trim($user->name.' '.$user->surname);
        }

        $copied = trim((string) $copied);
        if (str_contains($copied, ', ')) {
            [$surname, $name] = explode(', ', $copied, 2);

            return trim($name.' '.$surname);
        }

        return $copied;
    }

    protected static function day(CarbonInterface $at): string
    {
        return $at->locale('es')->format('d/m/Y');
    }

    protected static function clock(CarbonInterface $at): string
    {
        return $at->locale('es')->format('H:i');
    }

    /** «1 evidencia», «3 evidencias» */
    protected static function quantity(int $count, string $one, string $many): string
    {
        return $count.' '.($count === 1 ? $one : $many);
    }

    /** History of an evidence for its author (today, the detail of the author). */
    protected static function authorLink(Evidence|int $evidence): string
    {
        $id = $evidence instanceof Evidence ? $evidence->getKey() : $evidence;

        return self::link('activity.evidence', ['id' => $id], 'evidence.view', ['id' => $id]);
    }

    /** Where someone who is not the author reads an evidence. */
    protected static function evidenceLinkFor(User $user, Evidence $evidence): string
    {
        if ($user->hasRole('COORDINATOR') && (int) $user->coordinator?->committee_id === (int) $evidence->committee_id) {
            return route('coordinator.evidence.view', ['id' => $evidence->getKey()]);
        }

        if ($user->hasRole('PRESIDENT') && app(ReviewQueue::class)->reviewsAsPresidency($user, $evidence)) {
            return route('president.review.view', ['id' => $evidence->getKey()]);
        }

        if ($user->hasRole('PRESIDENT') || $user->hasRole('LECTURE')) {
            return self::link('activity.evidence', ['id' => $evidence->getKey()], 'profiles.view.evidence', [
                'id_user' => $evidence->user_id, 'id_evidence' => $evidence->getKey(),
            ]);
        }

        return self::link('activity.evidence', ['id' => $evidence->getKey()], 'home');
    }
}
