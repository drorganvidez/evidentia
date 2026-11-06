<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\Catalog;
use App\Services\Notifications\NotificationPreferences;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The inbox of the notices of the signed-in person, its read marks and the choice of which notices also arrive by
 * mail. Nobody reaches the notices of someone else: every query starts from the person.
 */
final class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $me */
        $me = $request->user();
        $onlyUnread = $request->query('unread') === '1';

        $notifications = $me->notifications()
            ->when($onlyUnread, fn ($query) => $query->whereNull('read_at'))
            ->paginate(20)
            ->withQueryString();

        $items = $notifications->getCollection()->map(fn (DatabaseNotification $notification): array => [
            'title' => (string) ($notification->data['title'] ?? 'Aviso'),
            'text' => $notification->data['text'] ?? null,
            'at' => $notification->created_at,
            'href' => route('notifications.open', $notification->id),
            'unread' => $notification->read_at === null,
            'tone' => $notification->data['tone'] ?? 'notice',
        ]);

        return view('notifications.index', [
            'notifications' => $notifications,
            'items' => $items,
            'onlyUnread' => $onlyUnread,
            'unread' => $me->unreadNotifications()->count(),
            'total' => $me->notifications()->count(),
        ]);
    }

    /** Marks the notice as read and follows its link, only ever to a page of the app. */
    public function open(Request $request, string $id): RedirectResponse
    {
        /** @var User $me */
        $me = $request->user();
        $notification = $me->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return redirect()->to(self::internal($url) ? $url : route('notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        /** @var User $me */
        $me = $request->user();
        $me->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'Avisos marcados como leídos.');
    }

    public function preferences(Request $request): View
    {
        /** @var User $me */
        $me = $request->user();

        return view('notifications.preferences', [
            'me' => $me,
            'catalog' => Catalog::forPreferences($me),
            'prefs' => app(NotificationPreferences::class),
            'unread' => $me->unreadNotifications()->count(),
        ]);
    }

    public function savePreferences(Request $request): RedirectResponse
    {
        /** @var User $me */
        $me = $request->user();
        $catalog = Catalog::forPreferences($me);
        $tunable = array_map(fn (string $class): string => $class::key(), [...$catalog['important'], ...$catalog['informative']]);

        $data = $request->validate([
            'mail' => ['sometimes', 'array'],
            'mail.*' => ['string', Rule::in($tunable)],
            'digest' => ['sometimes', 'boolean'],
        ]);

        app(NotificationPreferences::class)->update($me, array_values($data['mail'] ?? []), (bool) ($data['digest'] ?? false));

        return redirect()->route('notifications.preferences')->with('success', 'Preferencias guardadas.');
    }

    /** A path of this app: one slash, never //host nor a backslash that a browser reads as one. */
    private static function internal(mixed $url): bool
    {
        return is_string($url)
            && str_starts_with($url, '/')
            && ! str_starts_with($url, '//')
            && ! str_contains($url, '\\')
            && preg_match('/[\x00-\x1F\x7F]/', $url) === 0;
    }
}
