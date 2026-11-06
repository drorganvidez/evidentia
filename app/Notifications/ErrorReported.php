<?php

namespace App\Notifications;

use App\Enums\NotificationLevel;
use App\Models\ErrorReport;
use App\Models\User;
use Illuminate\Support\Str;

/** A server error that is new or came back after being resolved, to the lecturers (inbox and mail, always). */
class ErrorReported extends BaseNotification
{
    public function __construct(public ErrorReport $report)
    {
        parent::__construct();
    }

    public static function key(): string
    {
        return 'error.reported';
    }

    public static function label(): string
    {
        return 'Errores del servidor';
    }

    public static function level(): NotificationLevel
    {
        return NotificationLevel::Fixed;
    }

    public static function audience(): array
    {
        return ['LECTURE'];
    }

    public static function inPreferences(): bool
    {
        return false;
    }

    public function title(User $notifiable): string
    {
        return ($this->report->occurrences > 1 ? 'Ha vuelto un error' : 'Error nuevo').' · '.$this->report->shortException();
    }

    public function text(User $notifiable): ?string
    {
        return Str::limit((string) $this->report->message, 160);
    }

    public function url(User $notifiable): string
    {
        return route('lecture.errors.show', $this->report->id);
    }

    public function tone(): string
    {
        return 'rejected';
    }

    public function mailSubject(User $notifiable): string
    {
        return 'Error en Evidentia · '.$this->report->shortException();
    }

    public function mailLines(User $notifiable): array
    {
        return array_values(array_filter([
            $this->report->occurrences > 1
                ? 'Ha vuelto a aparecer un error que estaba resuelto.'
                : 'Se ha producido un error nuevo en el servidor.',
            $this->report->relativeFile() !== null ? 'Fichero '.$this->report->relativeFile().', línea '.$this->report->line.'.' : null,
        ]));
    }

    public function mailPanel(User $notifiable): ?string
    {
        return Str::limit((string) $this->report->message, 500);
    }

    public function mailAction(User $notifiable): string
    {
        return 'Ver el error';
    }
}
