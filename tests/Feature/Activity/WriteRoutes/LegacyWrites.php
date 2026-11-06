<?php

namespace Tests\Feature\Activity\WriteRoutes;

/**
 * Write routes of the screens that existed before the activity log. Each route is in exactly one of the four lists.
 */
final class LegacyWrites
{
    /**
     * Routes that annotate, with a happy request that the sweep sends.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function writes(): array
    {
        return [];
    }

    /** @return array<string, string> route => why it does not annotate */
    public static function excluded(): array
    {
        return [
            'upload.process' => 'Fichero temporal de FilePond',
            'upload.revert' => 'Fichero temporal de FilePond',
            'upload.remove' => 'Fichero temporal de FilePond',
            'xls.upload.process' => 'Fichero temporal de FilePond',
            'xls.upload.remove' => 'Fichero temporal de FilePond',
            'secretary.meeting.manage.minutes.create.step1_p' => 'No escribe',
            'secretary.meeting.manage.minutes.create.step2_p' => 'No escribe',
            'csp.report' => 'Sin sesión, solo log',
            'storage.local.upload' => 'Del framework',
            'ui.demo.upload' => 'Catálogo',
            'ui.demo.revert' => 'Catálogo',
            'ui.demo.remove' => 'Catálogo',
            'ui.demo.submit' => 'Catálogo',
            'suggestionsmailbox_p' => 'Se sustituye por los mensajes',
        ];
    }

    /** @return array<string, string> route => test class that checks its annotation */
    public static function coveredElsewhere(): array
    {
        return [];
    }

    /** @return array<string, string> route => batch that will annotate it */
    public static function pending(): array
    {
        return [
            'POST login' => 'A',
            'logout' => 'A',
            'password.reset_p' => 'A',
            'password.update_p' => 'A',
            'evidence.draft' => 'A',
            'evidence.publish' => 'A',
            'evidence.draft.edit' => 'A',
            'evidence.publish.edit' => 'A',
            'evidence.reedit' => 'A',
            'evidence.remove' => 'A',
            'coordinator.evidence.accept' => 'A',
            'coordinator.evidence.reject' => 'A',
            'secretary.meeting.manage.request.new' => 'A',
            'secretary.meeting.manage.request.save' => 'A',
            'secretary.meeting.manage.request.remove' => 'A',
            'secretary.meeting.manage.signaturesheet.new' => 'A',
            'secretary.meeting.manage.signaturesheet.save' => 'A',
            'secretary.meeting.manage.signaturesheet.remove' => 'A',
            'secretary.meeting.manage.minutes.create.step3_p' => 'A',
            'secretary.meeting.manage.minutes.save' => 'A',
            'secretary.meeting.manage.minutes.remove' => 'A',
            'secretary.bonus.new' => 'A',
            'secretary.bonus.save' => 'A',
            'secretary.bonus.remove' => 'A',
            'secretary.defaultlist.new' => 'A',
            'secretary.defaultlist.save' => 'A',
            'secretary.defaultlist.remove' => 'A',
            'attendee.store' => 'A',
            'attendee.update' => 'A',
            'attendee.destroy' => 'A',
            'registercoordinator.event.hide' => 'A',
            'registercoordinator.event.unhide' => 'A',
            'registercoordinator.attendee.export' => 'A',
            'registercoordinator.token.save' => 'A',
            'registercoordinator.event.load' => 'A',
            'registercoordinator.attendee.load' => 'A',
            'registercoordinator.event.store' => 'F',
            'registercoordinator.event.update' => 'F',
            'registercoordinator.event.destroy' => 'F',
            'president.committee.management.new' => 'A',
            'president.committee.management.save' => 'A',
            'president.committee.management.remove' => 'A',
            'lecture.committee.management.new' => 'A',
            'lecture.committee.management.save' => 'A',
            'lecture.committee.management.remove' => 'A',
            'president.user.management.save' => 'A',
            'lecture.user.management.save' => 'A',
            'management.user.new' => 'A',
            'management.user.delete.all' => 'A',
            'president.config.save' => 'A',
            'lecture.config.save' => 'A',
            'lecture.import.save' => 'A',
            'president.export.save' => 'A',
            'lecture.export.save' => 'A',
            'randomize.save' => 'A',
            'profile.upload.info' => 'A',
            'profile.upload.biography' => 'A',
            'profile.upload.pass' => 'A',
            'sign_p' => 'A',
        ];
    }
}
