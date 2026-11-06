<?php

namespace Tests\Feature\Notifications;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Configuration;
use App\Models\Evidence;
use App\Models\User;
use App\Notifications\Events\EvidenceAccepted;
use App\Notifications\Events\EvidenceMoved;
use App\Notifications\Events\EvidenceRejected;
use App\Notifications\Events\EvidenceSubmitted;
use App\Services\EvidenceMover;
use App\Services\EvidenceReview;
use App\Services\Notifications\NotificationPreferences;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;

/**
 * The notices of the evidences: sent, accepted, rejected and moved. Each one reaches its people exactly once and
 * never whoever did the action.
 */
class EvidenceNotificationsTest extends NotificationTestCase
{
    private function publish(Evidence $evidence, User $author, string $status = 'PENDING'): void
    {
        Activity::record(ActivityAction::EvidencePublished, $evidence, details: [
            'status' => $status, 'hours' => (float) $evidence->hours, 'committee_id' => (int) $evidence->committee_id,
        ], actor: $author, actingRole: 'STUDENT');
    }

    private function expectedLink(string $route, array $parameters, string $fallback, array $fallbackParameters): string
    {
        return Route::has($route) ? route($route, $parameters) : route($fallback, $fallbackParameters);
    }

    public function test_a_sent_evidence_reaches_the_coordinators_and_secretaries_of_its_committee(): void
    {
        Notification::fake();
        $evidence = $this->evidence($this->student, 'PENDING');

        $this->publish($evidence, $this->student);

        Notification::assertSentToTimes($this->coordinator, EvidenceSubmitted::class, 1);
        Notification::assertSentToTimes($this->secretary, EvidenceSubmitted::class, 1);
        foreach ([$this->student, $this->otherCoordinator, $this->president, $this->lecturer] as $nobody) {
            Notification::assertNotSentTo($nobody, EvidenceSubmitted::class);
        }
    }

    public function test_the_texts_and_links_of_a_sent_evidence(): void
    {
        Notification::fake();
        $evidence = $this->evidence($this->student, 'PENDING');
        $this->publish($evidence, $this->student);

        Notification::assertSentTo($this->coordinator, EvidenceSubmitted::class, function (EvidenceSubmitted $notice) use ($evidence): bool {
            $data = $notice->toArray($this->coordinator);
            $this->assertSame('Evidencia recibida en Logística', $data['title']);
            $this->assertSame('Pérez Romero, Lucía ha enviado «Montaje del photocall» (1,75 h)', $data['text']);
            $this->assertSame('/coordinator/evidence/view/'.$evidence->id, $data['url']);
            $this->assertSame('evidence.submitted', $data['key']);
            $this->assertSame('Montaje del photocall · Lucía Pérez Romero · 1,75 h', $data['digest']);
            $this->assertSame('Evidencia recibida en Logística · Montaje del photocall', $notice->mailSubject($this->coordinator));

            $mail = $this->mailText($notice, $this->coordinator);
            $this->assertStringContainsString('Lucía Pérez Romero ha enviado la evidencia «Montaje del photocall» (1,75 h) al comité Logística.', $mail);
            $this->assertStringContainsString('El plazo de validación cierra el '.now()->addDays(20)->format('d/m/Y').' a las 20:00.', $mail);
            $this->assertStringNotContainsString('no tiene otro coordinador', $mail);
            $this->assertStringContainsString('Revisar la evidencia', $mail);

            return true;
        });
        Notification::assertSentTo($this->secretary, EvidenceSubmitted::class, function (EvidenceSubmitted $notice) use ($evidence): bool {
            $this->assertSame($this->expectedLink('activity.evidence', ['id' => $evidence->id], 'home', []), $notice->url($this->secretary));

            return true;
        });
    }

    public function test_the_evidence_of_the_only_coordinator_also_reaches_the_management(): void
    {
        Notification::fake();
        $evidence = $this->evidence($this->coordinator, 'PENDING');

        $this->publish($evidence, $this->coordinator);

        Notification::assertNotSentTo($this->coordinator, EvidenceSubmitted::class);
        Notification::assertSentToTimes($this->secretary, EvidenceSubmitted::class, 1);
        Notification::assertSentToTimes($this->president, EvidenceSubmitted::class, 1);
        Notification::assertSentToTimes($this->lecturer, EvidenceSubmitted::class, 1);
        Notification::assertSentTo($this->president, EvidenceSubmitted::class, function (EvidenceSubmitted $notice) use ($evidence): bool {
            $this->assertStringContainsString('Este comité no tiene otro coordinador que pueda revisarla.', $this->mailText($notice, $this->president));
            $this->assertSame(
                $this->expectedLink('activity.evidence', ['id' => $evidence->id], 'profiles.view.evidence', ['id_user' => $this->coordinator->id, 'id_evidence' => $evidence->id]),
                $notice->url($this->president),
            );

            return true;
        });
    }

    public function test_an_evidence_that_needs_no_review_notifies_nobody(): void
    {
        Notification::fake();

        $this->publish($this->evidence($this->president, 'ACCEPTED'), $this->president, 'ACCEPTED');
        Activity::record(ActivityAction::EvidenceCreated, $this->evidence($this->student, 'DRAFT'), details: ['status' => 'DRAFT'], actor: $this->student);

        Notification::assertNothingSent();
    }

    public function test_a_rejection_reaches_the_author_with_the_reason_and_the_time_left(): void
    {
        Notification::fake();
        $evidence = $this->evidence($this->student, 'PENDING');
        $this->actingAs($this->coordinator);

        app(EvidenceReview::class)->reject($evidence, 'Faltan las fotos del montaje terminado.', 'COORDINATOR');

        Notification::assertSentToTimes($this->student, EvidenceRejected::class, 1);
        Notification::assertNotSentTo($this->coordinator, EvidenceRejected::class);
        Notification::assertSentTo($this->student, EvidenceRejected::class, function (EvidenceRejected $notice) use ($evidence): bool {
            $data = $notice->toArray($this->student);
            $this->assertSame('Evidencia rechazada', $data['title']);
            $this->assertSame('Montaje del photocall · Faltan las fotos del montaje terminado.', $data['text']);
            $this->assertSame('rejected', $data['tone']);
            $this->assertSame(
                parse_url($this->expectedLink('activity.evidence', ['id' => $evidence->id], 'evidence.view', ['id' => $evidence->id]), PHP_URL_PATH),
                $data['url'],
            );

            $mail = $this->mailText($notice, $this->student);
            $this->assertStringContainsString('Carlos Navarro Ruiz ha rechazado tu evidencia «Montaje del photocall» del comité Logística.', $mail);
            $this->assertStringContainsString('Faltan las fotos del montaje terminado.', $mail);
            $this->assertStringContainsString('Tienes hasta el '.now()->addDays(10)->format('d/m/Y').' a las 20:00 para corregirla y volver a enviarla (quedan 10 días).', $mail);

            return true;
        });
    }

    public function test_a_rejection_always_goes_by_mail_and_says_when_the_upload_is_closed(): void
    {
        Notification::fake();
        Configuration::query()->update(['upload_evidences_timestamp' => now()->subDay()]);
        app(NotificationPreferences::class)->update($this->student, [], false);
        $evidence = $this->evidence($this->student, 'PENDING');
        $this->actingAs($this->coordinator);

        app(EvidenceReview::class)->reject($evidence, 'Sin fotos.', 'COORDINATOR');

        Notification::assertSentTo($this->student, EvidenceRejected::class, function (EvidenceRejected $notice, array $channels): bool {
            $this->assertSame(['database', 'mail'], $channels);
            $this->assertStringContainsString('El plazo de subida está cerrado. Si crees que es un error, escribe a tu coordinador desde la evidencia.', $this->mailText($notice, $this->student));

            return true;
        });
    }

    public function test_an_acceptance_reaches_the_author_unless_the_mail_is_turned_off(): void
    {
        Notification::fake();
        $evidence = $this->evidence($this->student, 'PENDING');

        $this->actingAs($this->coordinator)->post(route('coordinator.evidence.accept', ['id' => $evidence->id]))->assertRedirect();

        Notification::assertSentToTimes($this->student, EvidenceAccepted::class, 1);
        Notification::assertSentTo($this->student, EvidenceAccepted::class, function (EvidenceAccepted $notice, array $channels): bool {
            $this->assertSame(['database', 'mail'], $channels);
            $this->assertSame('Montaje del photocall suma 1,75 h', $notice->text($this->student));
            $mail = $this->mailText($notice, $this->student);
            $this->assertStringContainsString('Carlos Navarro Ruiz ha aceptado tu evidencia «Montaje del photocall» del comité Logística.', $mail);
            $this->assertStringContainsString('Suma 1,75 h a tu cómputo.', $mail);

            return true;
        });

        app(NotificationPreferences::class)->update($this->student, [], false);
        $second = $this->evidence($this->student, 'PENDING', title: 'Cartelería');
        $this->post(route('coordinator.evidence.accept', ['id' => $second->id]));

        Notification::assertSentTo($this->student, EvidenceAccepted::class, fn (EvidenceAccepted $notice, array $channels): bool => $notice->activity->subject_id === $second->id && $channels === ['database']);
    }

    public function test_a_move_by_the_coordinator_reaches_the_author_and_the_coordinators_of_the_new_committee(): void
    {
        Notification::fake();
        $evidence = $this->evidence($this->student, 'PENDING');
        $this->actingAs($this->coordinator);

        app(EvidenceMover::class)->move($evidence, $this->programme, 'COORDINATOR');

        Notification::assertSentToTimes($this->student, EvidenceMoved::class, 1);
        Notification::assertSentToTimes($this->otherCoordinator, EvidenceMoved::class, 1);
        Notification::assertNotSentTo($this->coordinator, EvidenceMoved::class);
        Notification::assertSentTo($this->student, EvidenceMoved::class, function (EvidenceMoved $notice): bool {
            $this->assertSame('Evidencia movida a Programa', $notice->title($this->student));
            $this->assertSame('Montaje del photocall pasa de Logística a Programa', $notice->text($this->student));
            $this->assertStringContainsString('Carlos Navarro Ruiz ha movido tu evidencia «Montaje del photocall» del comité Logística al comité Programa.', $this->mailText($notice, $this->student));

            return true;
        });
        Notification::assertSentTo($this->otherCoordinator, EvidenceMoved::class, function (EvidenceMoved $notice) use ($evidence): bool {
            $this->assertSame('Evidencia recibida en Programa', $notice->title($this->otherCoordinator));
            $this->assertSame('Lucía Pérez Romero · Montaje del photocall, movida desde Logística', $notice->text($this->otherCoordinator));
            $this->assertSame(route('coordinator.evidence.view', ['id' => $evidence->id]), $notice->url($this->otherCoordinator));
            $this->assertSame('Revisar la evidencia', $notice->mailAction($this->otherCoordinator));

            return true;
        });
    }

    public function test_a_draft_moved_by_its_author_notifies_nobody(): void
    {
        Notification::fake();
        $this->actingAs($this->student);

        app(EvidenceMover::class)->move($this->evidence($this->student, 'DRAFT'), $this->programme, 'STUDENT');
        app(EvidenceMover::class)->move($this->evidence($this->student, 'PENDING', title: 'Otra'), $this->programme, 'STUDENT');

        Notification::assertNotSentTo($this->student, EvidenceMoved::class);
        Notification::assertSentToTimes($this->otherCoordinator, EvidenceMoved::class, 1);
    }

    public function test_the_inbox_keeps_the_key_and_the_relative_link(): void
    {
        $evidence = $this->evidence($this->student, 'PENDING');
        $this->publish($evidence, $this->student);

        $row = DB::table('notifications')->where('notifiable_id', $this->coordinator->id)->sole();
        $this->assertSame('evidence.submitted', $row->type);
        $this->assertSame('user', $row->notifiable_type);
        $this->assertSame('/coordinator/evidence/view/'.$evidence->id, json_decode($row->data, true)['url']);
    }
}
