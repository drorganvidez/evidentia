<?php

namespace App\Services\Activity;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Enums\Deadline;
use App\Models\ActivityLog;
use App\Models\Evidence;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\MessageThread;
use App\Models\SignatureSheet;
use App\Models\User;
use App\Services\Messaging\Audience;
use App\Support\Roles;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Throwable;

/**
 * Texts and links of one annotation for the screens: label, tone, who, the role line, a one line summary and the
 * page of its subject. Stateless; it receives the row.
 */
final class ActivityPresenter
{
    /** Names of the fields of the before and after details. */
    private const FIELDS = [
        'title' => 'Título', 'hours' => 'Horas', 'committee_name' => 'Comité', 'name' => 'Nombre', 'surname' => 'Apellidos',
        'email' => 'Correo', 'username' => 'Usuario', 'place' => 'Lugar', 'datetime' => 'Fecha', 'type' => 'Tipo',
        'modality' => 'Modalidad', 'reason' => 'Motivo', 'status' => 'Estado', 'icon' => 'Icono', 'users' => 'Personas',
        'points' => 'Orden del día', 'biography' => 'Biografía', 'participation' => 'Participación', 'block' => 'Bloqueo',
        'roles' => 'Roles', 'meeting_request_id' => 'Convocatoria', 'event_id' => 'Evento', 'user_id' => 'Persona',
        'description' => 'Descripción', 'coauthors' => 'Coautores',
    ];

    /** Fields whose value is a date and time. */
    private const DATES = ['datetime'];

    private const STATUSES = [
        'DRAFT' => 'borrador', 'PENDING' => 'pendiente', 'ACCEPTED' => 'aceptada', 'REJECTED' => 'rechazada',
    ];

    public function __construct(private ActivityAccess $access) {}

    public function label(ActivityLog $entry): string
    {
        return $entry->actionEnum()?->label() ?? (string) $entry->action;
    }

    public function tone(ActivityLog $entry): string
    {
        return $entry->actionEnum()?->tone() ?? 'draft';
    }

    /** The current name of the actor, the copied one if the person was deleted, or «Sistema». */
    public function actorName(ActivityLog $entry): string
    {
        if ($entry->user_id === null) {
            return 'Sistema';
        }

        $user = $entry->user;

        return $user ? trim($user->name.' '.$user->surname) : (string) $entry->user_name;
    }

    /** «como coordinador de Logística», «como profesor»; null without a role. */
    public function roleLine(ActivityLog $entry): ?string
    {
        $committee = in_array($entry->acting_role, ['COORDINATOR', 'SECRETARY'], true) ? $entry->committee_name : null;

        return Roles::asLabel($entry->acting_role, $committee);
    }

    public function version(ActivityLog $entry): ?int
    {
        $version = $entry->details['version'] ?? null;

        return is_numeric($version) ? (int) $version : null;
    }

    /** «Autorrevisión · Fuera de plazo»; null when the annotation carries no signal. */
    public function flagsLabel(ActivityLog $entry): ?string
    {
        $labels = array_map(fn (ActivityFlag $flag): string => $flag->label(), $entry->flagList());

        return $labels === [] ? null : implode(' · ', $labels);
    }

    /**
     * One line that tells what the annotation changed, from its details; null when there is nothing to add.
     * Without the version when the screen prints it apart.
     */
    public function summary(ActivityLog $entry, bool $withVersion = true): ?string
    {
        $d = $entry->details ?? [];
        $version = $withVersion ? $this->version($entry) : null;

        $parts = match ($entry->action) {
            'login' => [
                ($d['via'] ?? null) === 'cookie' ? 'Recordarme' : null,
                ! empty($d['blocked']) ? 'Cuenta bloqueada' : null,
            ],
            'login_failed' => [
                ! empty($d['lockout']) ? 'Bloqueo por intentos' : null,
                ($d['context'] ?? null) === 'sign' ? 'En la firma pública' : null,
            ],
            'password_reset_requested' => [($d['mail_sent'] ?? null) === false ? 'Correo no enviado' : null],
            'profile.edited', 'user.edited' => [$this->fieldNames($d)],
            'evidence.created' => [$this->hours($d['hours'] ?? null), $this->count($d['files'] ?? null, 'prueba', 'pruebas')],
            'evidence.published' => [
                $version !== null ? 'Versión '.$version : null,
                $this->hours($d['hours'] ?? null),
                ! empty($d['automatic']) ? 'Aceptada al enviarla' : null,
            ],
            'evidence.edited' => [$this->changes($d), $version !== null ? 'versión '.$version : null],
            'evidence.reedited' => ['Rechazada → borrador'],
            'evidence.deleted' => [$this->count($d['versions'] ?? null, 'versión', 'versiones'), $this->count($d['files'] ?? null, 'prueba', 'pruebas')],
            'proof.uploaded', 'proof.deleted' => [$this->text($d['file'] ?? null)],
            'evidence.rejected' => [filled($d['reason'] ?? null) ? '«'.$d['reason'].'»' : null],
            'evidence.moved' => [$this->arrow($d['before']['committee_name'] ?? null, $d['after']['committee_name'] ?? null)],
            'request.created' => [$this->date($d['datetime'] ?? null), $this->text($d['place'] ?? null)],
            'request.deleted' => [! empty($d['sheet_unlinked']) ? 'Hoja desvinculada' : null],
            'sheet.deleted' => [$this->count($d['signatures_count'] ?? null, 'firma', 'firmas')],
            'minutes.created' => [$this->count($d['users'] ?? null, 'asistente', 'asistentes'), $this->hours($d['hours'] ?? null)],
            'minutes.edited' => [$this->changes($d), $this->attendeesDelta($d)],
            'minutes.deleted' => [$this->hours($d['hours'] ?? null), $this->count($d['users'] ?? null, 'asistente', 'asistentes')],
            'bonus.created', 'bonus.deleted' => [$this->count($d['users'] ?? null, 'persona', 'personas'), $this->hours($d['hours'] ?? null)],
            'defaultlist.created', 'defaultlist.deleted' => [$this->count($d['users'] ?? null, 'persona', 'personas')],
            'attendee.created', 'attendee.deleted' => [$this->text($d['status'] ?? null)],
            'event.grouped', 'event.ungrouped' => [$this->text($d['group'] ?? null), $this->count($d['events'] ?? null, 'evento', 'eventos')],
            'eventbrite.events_synced' => [
                $this->count($d['created'] ?? null, 'nuevo', 'nuevos'),
                $this->count($d['updated'] ?? null, 'actualizado', 'actualizados'),
            ],
            'eventbrite.attendees_synced' => [
                $this->count($d['created'] ?? null, 'nueva', 'nuevas'),
                $this->count($d['updated'] ?? null, 'actualizada', 'actualizadas'),
                $this->count($d['unmatched'] ?? null, 'sin emparejar', 'sin emparejar'),
                $this->count($d['events'] ?? null, 'evento revisado', 'eventos revisados'),
            ],
            'certificate.issued', 'certificate.revoked' => [$this->text($d['code'] ?? null), $this->text($d['title'] ?? null)],
            'user.created' => [$this->text($d['username'] ?? null)],
            'user.roles_changed' => [$this->arrow($this->roles($d['before']['roles'] ?? null), $this->roles($d['after']['roles'] ?? null))],
            'user.deleted' => [$this->roles($d['roles'] ?? null)],
            'config.changed' => [$this->deadlineChanges($d)],
            'import.users' => [$this->count($d['created'] ?? null, 'usuario nuevo', 'usuarios nuevos')],
            'export.evidences' => [is_array($d['sections'] ?? null) ? implode(', ', array_filter($d['sections'], 'is_string')) : null],
            'export.attendees', 'export.activity', 'export.record', 'committee.exported' => [$this->count($d['rows'] ?? null, 'fila', 'filas')],
            'randomize.run' => [$this->count($d['count'] ?? null, 'evidencia', 'evidencias')],
            'message.sent' => [filled($d['audience'] ?? null) && is_string($d['audience']) ? 'Para '.$this->audience($d['audience']) : null],
            'notification.sent' => [$this->text($d['title'] ?? null), $this->channels($d['channels'] ?? null)],
            'notification.failed' => ['Correo no enviado'],
            'self_review.blocked' => [$this->attempted($d['attempted'] ?? null)],
            'activity.pruned' => [$this->pruned($d)],
            'request.edited', 'sheet.edited', 'bonus.edited', 'defaultlist.edited', 'attendee.edited', 'committee.edited' => [$this->changes($d)],
            default => [],
        };

        $parts = array_values(array_filter($parts, fn (?string $part): bool => filled($part)));

        return $parts === [] ? null : implode(' · ', $parts);
    }

    /** Page of the subject this person can open; null when it no longer exists or is out of their reach. */
    public function subjectUrl(ActivityLog $entry, User $viewer): ?string
    {
        if ($entry->subject_type === null || $entry->subject_id === null) {
            return null;
        }

        return match ($entry->subject_type) {
            'evidence' => $this->evidenceUrl($entry->subject_id, $viewer),
            'meeting_minutes' => MeetingMinutes::whereKey($entry->subject_id)->exists() ? $this->route('activity.minutes', ['id' => $entry->subject_id]) : null,
            'meeting_request' => MeetingRequest::whereKey($entry->subject_id)->exists() ? $this->route('activity.request', ['id' => $entry->subject_id]) : null,
            'signature_sheet' => ($requestId = SignatureSheet::whereKey($entry->subject_id)->value('meeting_request_id'))
                ? $this->route('activity.request', ['id' => $requestId])
                : null,
            'user' => $this->access->profileHref($viewer, (int) $entry->subject_id),
            'message_thread' => MessageThread::whereKey($entry->subject_id)->visibleTo($viewer)->exists()
                ? $this->route('messages.show', ['thread' => $entry->subject_id])
                : null,
            default => null,
        };
    }

    private function evidenceUrl(int $id, User $viewer): ?string
    {
        $evidence = Evidence::find($id);
        if ($evidence === null) {
            return null;
        }

        $head = $evidence->headEvidence();

        return $this->access->canViewEvidence($viewer, $head) ? $this->route('activity.evidence', ['id' => $head->id]) : null;
    }

    /** The new screens arrive with their own routes; until then there is nothing to link. */
    private function route(string $name, array $parameters): ?string
    {
        return Route::has($name) ? route($name, $parameters) : null;
    }

    private function text(mixed $value): ?string
    {
        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }

    private function hours(mixed $hours): ?string
    {
        return is_numeric($hours) ? number_format((float) $hours, 2, ',', '.').' h' : null;
    }

    private function count(mixed $value, string $one, string $many): ?string
    {
        $n = is_array($value) ? count($value) : (is_numeric($value) ? (int) $value : null);

        return $n === null ? null : $n.' '.($n === 1 ? $one : $many);
    }

    private function date(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('d/m/Y H:i');
        } catch (Throwable) {
            return $value;
        }
    }

    private function arrow(?string $before, ?string $after): ?string
    {
        return $before === null && $after === null ? null : ($before ?? '—').' → '.($after ?? '—');
    }

    /** «Estudiante, Coordinador» from a list of roles (or their labels). */
    private function roles(mixed $roles): ?string
    {
        if (is_string($roles)) {
            return $roles;
        }

        return is_array($roles) ? implode(', ', array_map(fn ($role): string => Roles::LABELS[$role] ?? (string) $role, $roles)) : null;
    }

    /** Names of the changed fields («Correo · Biografía»). */
    private function fieldNames(array $details): ?string
    {
        $fields = is_array($details['fields'] ?? null)
            ? $details['fields']
            : array_keys(array_diff_assoc(Arr::dot((array) ($details['after'] ?? [])), Arr::dot((array) ($details['before'] ?? []))));
        $fields = array_values(array_unique(array_map(fn ($field): string => explode('.', (string) $field)[0], $fields)));

        return $fields === [] ? null : implode(' · ', array_map(fn (string $field): string => self::FIELDS[$field] ?? $field, $fields));
    }

    /** «Comité Programa → Logística · Horas 1,50 → 2,00» from the before and after details. */
    private function changes(array $details): ?string
    {
        $before = is_array($details['before'] ?? null) ? $details['before'] : [];
        $after = is_array($details['after'] ?? null) ? $details['after'] : [];
        $changes = [];

        foreach (array_keys($before + $after) as $field) {
            if (in_array($field, ['committee_id'], true) || ($before[$field] ?? null) == ($after[$field] ?? null)) {
                continue;
            }
            $changes[] = (self::FIELDS[$field] ?? ucfirst(str_replace('_', ' ', (string) $field))).' '
                .$this->arrow($this->value($field, $before[$field] ?? null), $this->value($field, $after[$field] ?? null));
        }

        return $changes === [] ? null : implode(' · ', $changes);
    }

    private function value(string|int $field, mixed $value): ?string
    {
        return match (true) {
            $value === null => null,
            $field === 'hours' && is_numeric($value) => number_format((float) $value, 2, ',', '.'),
            in_array($field, self::DATES, true) => $this->date((string) $value),
            $field === 'status' && is_string($value) => self::STATUSES[$value] ?? $value,
            $field === 'roles' => $this->roles($value),
            is_bool($value) => $value ? 'Sí' : 'No',
            is_array($value) => (string) count($value),
            default => (string) $value,
        };
    }

    /** «+2 −1 asistentes» */
    private function attendeesDelta(array $details): ?string
    {
        $added = is_array($details['users_added'] ?? null) ? count($details['users_added']) : 0;
        $removed = is_array($details['users_removed'] ?? null) ? count($details['users_removed']) : 0;

        return $added + $removed === 0 ? null : '+'.$added.' −'.$removed.' asistentes';
    }

    /** «Subida 20/11 20:00 → 27/11 20:00», keyed by deadline or by its column. */
    private function deadlineChanges(array $details): ?string
    {
        $before = is_array($details['before'] ?? null) ? $details['before'] : [];
        $after = is_array($details['after'] ?? null) ? $details['after'] : [];
        $changes = [];

        $limits = ['max_evidences_hours' => 'Límite de evidencias', 'max_attendees_hours' => 'Límite de asistencia'];
        $hours = fn (mixed $value): string => is_numeric($value) ? str_replace('.', ',', (string) (float) $value).' h' : 'sin límite';

        foreach (array_keys($before + $after) as $key) {
            if (isset($limits[$key])) {
                $changes[] = $limits[$key].' '.$hours($before[$key] ?? null).' → '.$hours($after[$key] ?? null);

                continue;
            }
            $deadline = Deadline::tryFrom(str_replace('_timestamp', '', (string) $key));
            $format = fn (mixed $value): string => is_string($value) && $value !== '' ? Carbon::parse($value)->format('d/m H:i') : 'sin fecha';
            $changes[] = ($deadline?->shortLabel() ?? (string) $key).' '.$format($before[$key] ?? null).' → '.$format($after[$key] ?? null);
        }

        return $changes === [] ? null : implode(' · ', $changes);
    }

    private function channels(mixed $channels): ?string
    {
        if (! is_array($channels) || $channels === []) {
            return null;
        }

        return in_array('mail', $channels, true) ? 'bandeja y correo' : 'bandeja';
    }

    /** «Coordinación de Logística» from the key of an audience; the text itself if it is not a key. */
    private function audience(string $audience): string
    {
        try {
            return Audience::fromKey($audience)->label();
        } catch (InvalidArgumentException) {
            return $audience;
        }
    }

    /** «Intento de evidencia aceptada» */
    private function attempted(mixed $attempted): ?string
    {
        $label = is_string($attempted) ? ActivityAction::tryFrom($attempted)?->label() : null;

        return $label === null ? null : 'Intento de '.mb_strtolower(mb_substr($label, 0, 1)).mb_substr($label, 1);
    }

    /** Counts of activity:prune. */
    private function pruned(array $details): ?string
    {
        $entries = (int) ($details['failed_logins'] ?? 0) + (int) ($details['entries'] ?? 0);
        $notifications = (int) ($details['read_notifications'] ?? 0) + (int) ($details['orphan_notifications'] ?? 0);

        return implode(' · ', [
            $this->count($entries, 'anotación borrada', 'anotaciones borradas'),
            $this->count($notifications, 'aviso borrado', 'avisos borrados'),
            $this->count((int) ($details['network_cleared'] ?? 0), 'anotación sin datos de red', 'anotaciones sin datos de red'),
        ]);
    }
}
