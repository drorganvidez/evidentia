<?php

namespace App\Enums;

/**
 * The five deadlines of the course, stored as columns of the configuration row.
 */
enum Deadline: string
{
    case UploadEvidences = 'upload_evidences';
    case ValidateEvidences = 'validate_evidences';
    case Meetings = 'meetings';
    case Bonus = 'bonus';
    case Attendees = 'attendee';

    public function column(): string
    {
        return $this->value.'_timestamp';
    }

    public function label(): string
    {
        return match ($this) {
            self::UploadEvidences => 'Subida de evidencias',
            self::ValidateEvidences => 'Validación de evidencias',
            self::Meetings => 'Registro de reuniones',
            self::Bonus => 'Registro de bonos',
            self::Attendees => 'Importación de eventos y asistencias',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::UploadEvidences => 'Subida',
            self::ValidateEvidences => 'Validación',
            self::Meetings => 'Reuniones',
            self::Bonus => 'Bonos',
            self::Attendees => 'Asistencias',
        };
    }

    public function verb(): string
    {
        return match ($this) {
            self::UploadEvidences => 'para subir evidencias',
            self::ValidateEvidences => 'para validar evidencias',
            self::Meetings => 'para registrar actas',
            self::Bonus => 'para registrar bonos',
            self::Attendees => 'para registrar asistencias',
        };
    }

    /** Name of the list where the work of this deadline is done. */
    public function listRoute(): string
    {
        return match ($this) {
            self::UploadEvidences => 'evidence.list',
            self::ValidateEvidences => 'coordinator.evidence.list.pending',
            self::Meetings => 'secretary.meeting.manage.minutes.list',
            self::Bonus => 'secretary.bonus.list',
            self::Attendees => 'registercoordinator.attendee.list',
        };
    }
}
