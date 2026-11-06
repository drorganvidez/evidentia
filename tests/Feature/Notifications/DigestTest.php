<?php

namespace Tests\Feature\Notifications;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Evidence;
use App\Models\User;
use App\Notifications\Events\EvidenceSubmitted;
use App\Notifications\EvidenceDigest;
use App\Services\Notifications\NotificationPreferences;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * notifications:digest, one mail a day with the evidences received since the last one, for the coordinators and
 * secretaries who chose it instead of one mail per evidence.
 */
class DigestTest extends NotificationTestCase
{
    private function publish(Evidence $evidence, User $author): void
    {
        Activity::record(ActivityAction::EvidencePublished, $evidence, details: [
            'status' => 'PENDING', 'hours' => (float) $evidence->hours, 'committee_id' => (int) $evidence->committee_id,
        ], actor: $author, actingRole: 'STUDENT');
    }

    private function digest(): void
    {
        $this->artisan('notifications:digest')->assertSuccessful();
    }

    /** @return list<Email> the mails delivered to the person through the array mailer */
    private function mailsTo(User $user): array
    {
        return collect(Mail::mailer('array')->getSymfonyTransport()->messages())
            ->map(fn (SentMessage $message): Email => $message->getOriginalMessage())
            ->filter(fn (Email $mail): bool => collect($mail->getTo())->contains(fn (Address $to): bool => $to->getAddress() === $user->email))
            ->values()
            ->all();
    }

    public function test_with_the_digest_the_evidences_stay_in_the_inbox_and_arrive_together_once_a_day(): void
    {
        $this->travelTo(now()->setTime(9, 0));
        app(NotificationPreferences::class)->update($this->coordinator, ['evidence.submitted'], true);

        $this->publish($this->evidence($this->student, 'PENDING', title: 'Montaje del photocall'), $this->student);
        $this->publish($this->evidence($this->secretary, 'PENDING', title: 'Cartelería', hours: 2), $this->secretary);

        $this->assertSame(2, $this->coordinator->notifications()->where('type', 'evidence.submitted')->count());
        $this->assertSame([], $this->mailsTo($this->coordinator), 'No mail per evidence with the digest on');
        $this->assertCount(1, $this->mailsTo($this->secretary), 'One mail per evidence without it');

        $this->travelTo(now()->setTime(18, 0));
        $this->digest();
        $this->digest();

        $mails = $this->mailsTo($this->coordinator);
        $this->assertCount(1, $mails);
        $this->assertSame('Resumen del día · 2 evidencias recibidas', $mails[0]->getSubject());
        $html = (string) $mails[0]->getHtmlBody();
        $this->assertStringContainsString('Evidencias enviadas a tu comité desde el último resumen.', $html);
        $this->assertMatchesRegularExpression('/<li[^>]*>Montaje del photocall · Lucía Pérez Romero · 1,75 h<\/li>\s*<li[^>]*>Cartelería · Marta Ruiz Díaz · 2 h<\/li>/', $html);
        $this->assertStringContainsString('href="'.route('coordinator.evidence.list.pending').'"', $html);
        $this->assertStringContainsString('Revisar pendientes', $html);
        $this->assertSame(0, $this->coordinator->notifications()->where('type', 'evidence.digest')->count(), 'Only by mail');

        // the next day only brings what arrived after the last digest
        $this->travel(1)->days();
        $this->publish($this->evidence($this->student, 'PENDING', title: 'Recogida de material', hours: 0.5), $this->student);
        $this->digest();

        $mails = $this->mailsTo($this->coordinator);
        $this->assertCount(2, $mails);
        $this->assertSame('Resumen del día · 1 evidencia recibida', $mails[1]->getSubject());
        $this->assertStringContainsString('Recogida de material · Lucía Pérez Romero · 0,5 h', (string) $mails[1]->getHtmlBody());
        $this->assertStringNotContainsString('Cartelería', (string) $mails[1]->getHtmlBody());
    }

    public function test_without_new_evidences_or_without_the_digest_nothing_is_sent(): void
    {
        $this->publish($this->evidence($this->student, 'PENDING'), $this->student);
        app(NotificationPreferences::class)->update($this->secretary, ['evidence.submitted'], true);
        Notification::fake();

        $this->digest();

        Notification::assertNotSentTo($this->coordinator, EvidenceDigest::class);
        Notification::assertSentTo($this->secretary, EvidenceDigest::class, function (EvidenceDigest $notice): bool {
            $this->assertSame(Route::has('activity.index') ? route('activity.index') : route('home'), $notice->url($this->secretary));

            return true;
        });

        app(NotificationPreferences::class)->update($this->coordinator, ['evidence.submitted'], true);
        $this->travel(2)->days();
        $this->digest();
        Notification::assertNotSentTo($this->coordinator, EvidenceDigest::class);
    }

    public function test_the_digest_takes_the_mail_of_the_evidences_only_for_coordinators_and_secretaries(): void
    {
        $preferences = app(NotificationPreferences::class);
        $preferences->update($this->coordinator, ['evidence.submitted'], true);
        $preferences->update($this->president, ['evidence.submitted'], true);

        $this->assertFalse($preferences->mailEnabled($this->coordinator, EvidenceSubmitted::key()));
        $this->assertTrue($preferences->mailEnabled($this->president, EvidenceSubmitted::key()));
        $this->assertFalse($preferences->digest($this->president));
    }

    public function test_a_blocked_person_gets_no_digest(): void
    {
        $this->publish($this->evidence($this->student, 'PENDING'), $this->student);
        app(NotificationPreferences::class)->update($this->coordinator, ['evidence.submitted'], true);
        $this->coordinator->forceFill(['block' => true])->save();
        Notification::fake();

        $this->digest();

        Notification::assertNotSentTo($this->coordinator, EvidenceDigest::class);
    }
}
