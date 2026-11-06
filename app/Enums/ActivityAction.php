<?php

namespace App\Enums;

/**
 * The closed set of verbs of the activity log. A controller can only annotate one of these; the filters, the views and
 * the checks of the write routes share the same catalogue.
 */
enum ActivityAction: string
{
    // Accesos
    case Login = 'login';
    case LoginFailed = 'login_failed';
    case Logout = 'logout';
    case PasswordResetRequested = 'password_reset_requested';
    case PasswordReset = 'password_reset';

    // Perfil
    case ProfileEdited = 'profile.edited';
    case UserPasswordChanged = 'user.password_changed';

    // Evidencias
    case EvidenceCreated = 'evidence.created';
    case EvidencePublished = 'evidence.published';
    case EvidenceEdited = 'evidence.edited';
    case EvidenceReedited = 'evidence.reedited';
    case EvidenceDeleted = 'evidence.deleted';
    case ProofUploaded = 'proof.uploaded';
    case ProofDeleted = 'proof.deleted';
    case EvidenceAccepted = 'evidence.accepted';
    case EvidenceRejected = 'evidence.rejected';
    case EvidenceMoved = 'evidence.moved';

    // Reuniones
    case RequestCreated = 'request.created';
    case RequestEdited = 'request.edited';
    case RequestDeleted = 'request.deleted';
    case SheetCreated = 'sheet.created';
    case SheetEdited = 'sheet.edited';
    case SheetDeleted = 'sheet.deleted';
    case SignatureSigned = 'signature.signed';
    case MinutesCreated = 'minutes.created';
    case MinutesEdited = 'minutes.edited';
    case MinutesDeleted = 'minutes.deleted';

    // Bonos y listas
    case BonusCreated = 'bonus.created';
    case BonusEdited = 'bonus.edited';
    case BonusDeleted = 'bonus.deleted';
    case DefaultListCreated = 'defaultlist.created';
    case DefaultListEdited = 'defaultlist.edited';
    case DefaultListDeleted = 'defaultlist.deleted';

    // Asistencias
    case AttendeeCreated = 'attendee.created';
    case AttendeeEdited = 'attendee.edited';
    case AttendeeDeleted = 'attendee.deleted';
    case EventCreated = 'event.created';
    case EventEdited = 'event.edited';
    case EventDeleted = 'event.deleted';
    case EventHidden = 'event.hidden';
    case EventUnhidden = 'event.unhidden';
    case EventbriteTokenSaved = 'eventbrite.token_saved';
    case EventbriteEventsSynced = 'eventbrite.events_synced';
    case EventbriteAttendeesSynced = 'eventbrite.attendees_synced';

    // Gestión
    case UserCreated = 'user.created';
    case UserEdited = 'user.edited';
    case UserRolesChanged = 'user.roles_changed';
    case UserBlocked = 'user.blocked';
    case UserUnblocked = 'user.unblocked';
    case UserPasswordChangedByManager = 'user.password_changed_by_manager';
    case UserDeleted = 'user.deleted';
    case CommitteeCreated = 'committee.created';
    case CommitteeEdited = 'committee.edited';
    case CommitteeDeleted = 'committee.deleted';
    case ConfigChanged = 'config.changed';
    case ImportUsers = 'import.users';
    case ExportEvidences = 'export.evidences';
    case ExportAttendees = 'export.attendees';
    case ExportActivity = 'export.activity';
    case RandomizeRun = 'randomize.run';

    // Mensajes
    case MessageSent = 'message.sent';
    case MessageReplied = 'message.replied';
    case MessageResolved = 'message.resolved';
    case MessageReopened = 'message.reopened';

    // Avisos
    case NotificationSent = 'notification.sent';
    case NotificationFailed = 'notification.failed';
    case NotificationPreferencesChanged = 'notification.preferences_changed';

    // Control
    case SelfReviewBlocked = 'self_review.blocked';
    case ActivityPruned = 'activity.pruned';

    private const GROUPS = [
        'access' => 'Accesos',
        'profile' => 'Perfil',
        'evidence' => 'Evidencias',
        'meetings' => 'Reuniones',
        'bonus' => 'Bonos y listas',
        'attendance' => 'Asistencias',
        'management' => 'Gestión',
        'messages' => 'Mensajes',
        'notifications' => 'Avisos',
        'control' => 'Control',
    ];

    /** verb => [label, tone, group, deadline that sets after_deadline] */
    private const META = [
        'login' => ['Inicio de sesión', 'notice', 'access', null],
        'login_failed' => ['Acceso fallido', 'rejected', 'access', null],
        'logout' => ['Cierre de sesión', 'notice', 'access', null],
        'password_reset_requested' => ['Restablecimiento solicitado', 'notice', 'access', null],
        'password_reset' => ['Contraseña restablecida', 'notice', 'access', null],

        'profile.edited' => ['Perfil editado', 'draft', 'profile', null],
        'user.password_changed' => ['Contraseña cambiada', 'notice', 'profile', null],

        'evidence.created' => ['Evidencia creada', 'draft', 'evidence', Deadline::UploadEvidences],
        'evidence.published' => ['Evidencia enviada', 'pending', 'evidence', Deadline::UploadEvidences],
        'evidence.edited' => ['Evidencia editada', 'draft', 'evidence', Deadline::UploadEvidences],
        'evidence.reedited' => ['Evidencia reabierta', 'draft', 'evidence', Deadline::UploadEvidences],
        'evidence.deleted' => ['Evidencia eliminada', 'rejected', 'evidence', Deadline::UploadEvidences],
        'proof.uploaded' => ['Prueba adjuntada', 'draft', 'evidence', Deadline::UploadEvidences],
        'proof.deleted' => ['Prueba eliminada', 'draft', 'evidence', Deadline::UploadEvidences],
        'evidence.accepted' => ['Evidencia aceptada', 'accepted', 'evidence', Deadline::ValidateEvidences],
        'evidence.rejected' => ['Evidencia rechazada', 'rejected', 'evidence', Deadline::ValidateEvidences],
        // el plazo depende del estado de la evidencia movida y lo pasa EvidenceMover
        'evidence.moved' => ['Evidencia movida de comité', 'notice', 'evidence', null],

        // convocatorias y hojas de firmas no tienen plazo
        'request.created' => ['Convocatoria creada', 'pending', 'meetings', null],
        'request.edited' => ['Convocatoria editada', 'draft', 'meetings', null],
        'request.deleted' => ['Convocatoria eliminada', 'rejected', 'meetings', null],
        'sheet.created' => ['Hoja de firmas creada', 'pending', 'meetings', null],
        'sheet.edited' => ['Hoja de firmas editada', 'draft', 'meetings', null],
        'sheet.deleted' => ['Hoja de firmas eliminada', 'rejected', 'meetings', null],
        'signature.signed' => ['Firma registrada', 'accepted', 'meetings', null],
        'minutes.created' => ['Acta registrada', 'accepted', 'meetings', Deadline::Meetings],
        'minutes.edited' => ['Acta editada', 'draft', 'meetings', Deadline::Meetings],
        'minutes.deleted' => ['Acta eliminada', 'rejected', 'meetings', Deadline::Meetings],

        'bonus.created' => ['Bono creado', 'accepted', 'bonus', Deadline::Bonus],
        'bonus.edited' => ['Bono editado', 'draft', 'bonus', Deadline::Bonus],
        'bonus.deleted' => ['Bono eliminado', 'rejected', 'bonus', Deadline::Bonus],
        'defaultlist.created' => ['Lista creada', 'draft', 'bonus', null],
        'defaultlist.edited' => ['Lista editada', 'draft', 'bonus', null],
        'defaultlist.deleted' => ['Lista eliminada', 'rejected', 'bonus', null],

        'attendee.created' => ['Asistencia registrada', 'accepted', 'attendance', Deadline::Attendees],
        'attendee.edited' => ['Asistencia editada', 'draft', 'attendance', Deadline::Attendees],
        'attendee.deleted' => ['Asistencia eliminada', 'rejected', 'attendance', Deadline::Attendees],
        'event.created' => ['Evento creado a mano', 'accepted', 'attendance', Deadline::Attendees],
        'event.edited' => ['Evento editado', 'draft', 'attendance', Deadline::Attendees],
        'event.deleted' => ['Evento eliminado', 'rejected', 'attendance', Deadline::Attendees],
        'event.hidden' => ['Evento ocultado', 'draft', 'attendance', Deadline::Attendees],
        'event.unhidden' => ['Evento visible', 'draft', 'attendance', Deadline::Attendees],
        'eventbrite.token_saved' => ['Token de Eventbrite guardado', 'notice', 'attendance', Deadline::Attendees],
        'eventbrite.events_synced' => ['Eventos sincronizados', 'notice', 'attendance', Deadline::Attendees],
        'eventbrite.attendees_synced' => ['Asistencias sincronizadas', 'notice', 'attendance', Deadline::Attendees],

        'user.created' => ['Usuario creado', 'accepted', 'management', null],
        'user.edited' => ['Usuario editado', 'draft', 'management', null],
        'user.roles_changed' => ['Roles cambiados', 'notice', 'management', null],
        'user.blocked' => ['Usuario bloqueado', 'rejected', 'management', null],
        'user.unblocked' => ['Usuario desbloqueado', 'accepted', 'management', null],
        'user.password_changed_by_manager' => ['Contraseña cambiada por gestión', 'notice', 'management', null],
        'user.deleted' => ['Usuario eliminado', 'rejected', 'management', null],
        'committee.created' => ['Comité creado', 'accepted', 'management', null],
        'committee.edited' => ['Comité editado', 'draft', 'management', null],
        'committee.deleted' => ['Comité eliminado', 'rejected', 'management', null],
        'config.changed' => ['Configuración del curso cambiada', 'notice', 'management', null],
        'import.users' => ['Importación de alumnos', 'notice', 'management', null],
        'export.evidences' => ['Exportación de evidencias', 'notice', 'management', null],
        'export.attendees' => ['Exportación de asistencias', 'notice', 'management', null],
        'export.activity' => ['Exportación del registro', 'notice', 'management', null],
        'randomize.run' => ['Aleatorización ejecutada', 'notice', 'management', null],

        'message.sent' => ['Mensaje enviado', 'pending', 'messages', null],
        'message.replied' => ['Respuesta', 'pending', 'messages', null],
        'message.resolved' => ['Hilo resuelto', 'accepted', 'messages', null],
        'message.reopened' => ['Hilo reabierto', 'notice', 'messages', null],

        'notification.sent' => ['Aviso enviado', 'notice', 'notifications', null],
        'notification.failed' => ['Aviso fallido', 'rejected', 'notifications', null],
        'notification.preferences_changed' => ['Preferencias de avisos cambiadas', 'draft', 'notifications', null],

        'self_review.blocked' => ['Autorrevisión bloqueada', 'rejected', 'control', null],
        'activity.pruned' => ['Purga del registro', 'draft', 'control', null],
    ];

    public function label(): string
    {
        return self::META[$this->value][0];
    }

    /** accepted | rejected | pending | notice | draft */
    public function tone(): string
    {
        return self::META[$this->value][1];
    }

    public function group(): string
    {
        return self::META[$this->value][2];
    }

    /** Deadline whose closing flags the annotation as after_deadline; null when the verb has none. */
    public function deadline(): ?Deadline
    {
        return self::META[$this->value][3];
    }

    /** Accesses and sent notices: the first view of the log hides them. */
    public function isNoisy(): bool
    {
        return $this->group() === 'access' || $this === self::NotificationSent;
    }

    /** @return array<string, string> */
    public static function groups(): array
    {
        return self::GROUPS;
    }

    /** @return list<string> */
    public static function inGroup(string $group): array
    {
        return array_values(array_map(
            fn (self $action): string => $action->value,
            array_filter(self::cases(), fn (self $action): bool => $action->group() === $group),
        ));
    }

    /** @return list<string> */
    public static function noisy(): array
    {
        return array_values(array_map(
            fn (self $action): string => $action->value,
            array_filter(self::cases(), fn (self $action): bool => $action->isNoisy()),
        ));
    }

    /**
     * Options of the type filter: each group as a whole (value group:<key>) followed by its verbs.
     *
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::GROUPS as $group => $label) {
            $options[] = ['value' => 'group:'.$group, 'label' => $label.' (todos)'];
            foreach (self::inGroup($group) as $verb) {
                $options[] = ['value' => $verb, 'label' => self::from($verb)->label()];
            }
        }

        return $options;
    }
}
