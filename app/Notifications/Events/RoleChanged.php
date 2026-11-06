<?php

namespace App\Notifications\Events;

use App\Enums\ActivityAction;
use App\Enums\NotificationLevel;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Concerns\FormatsNotices;
use App\Support\Roles;
use Illuminate\Support\Collection;

/** The roles of a person changed by the management, to that person with the roles before and after. */
class RoleChanged extends BaseNotification
{
    use FormatsNotices;

    public static function key(): string
    {
        return 'user.roles_changed';
    }

    public static function label(): string
    {
        return 'Cambio de roles';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Important;
    }

    public static function triggers(): array
    {
        return [ActivityAction::UserRolesChanged];
    }

    public static function recipients(ActivityLog $activity): Collection
    {
        return collect([$activity->targetUser])->filter()->values();
    }

    public function title(User $notifiable): string
    {
        return 'Tus roles han cambiado';
    }

    public function text(User $notifiable): ?string
    {
        return 'Ahora '.$this->roles('after');
    }

    public function url(User $notifiable): string
    {
        return route('home');
    }

    public function tone(): string
    {
        return 'notice';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Tus roles en Evidentia han cambiado';
    }

    public function mailLines(User $notifiable): array
    {
        return [
            self::fullName($this->activity?->user, $this->activity?->user_name).' ha cambiado tus roles.',
            'Antes '.$this->roles('before').'.',
            'Ahora '.$this->roles('after').'.',
        ];
    }

    public function mailAction(User $notifiable): string
    {
        return 'Entrar en Evidentia';
    }

    /** «estudiante y coordinador en el comité Logística», «ningún rol» */
    private function roles(string $when): string
    {
        $state = $this->activity?->details[$when] ?? [];
        $labels = collect(Roles::PRIORITY)
            ->filter(fn (string $role): bool => in_array($role, (array) ($state['roles'] ?? []), true))
            ->reverse()
            ->map(fn (string $role): string => mb_strtolower(Roles::label($role)))
            ->values();

        $list = match ($labels->count()) {
            0 => 'ningún rol',
            1 => $labels->first(),
            default => $labels->slice(0, -1)->implode(', ').' y '.$labels->last(),
        };

        return filled($state['committee'] ?? null) ? $list.' en el comité '.$state['committee'] : $list;
    }
}
