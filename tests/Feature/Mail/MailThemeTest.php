<?php

namespace Tests\Feature\Mail;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Tests\Feature\Mail\Fixtures\ThemedNotice;
use Tests\TestCase;

/**
 * The theme of the mails, checked on a real delivery through the array mailer: Spanish, the mark embedded, the
 * palette inlined, nothing remote and no text of a person turned into a link.
 */
class MailThemeTest extends TestCase
{
    use RefreshDatabase;

    private function deliver(ThemedNotice $notice): Email
    {
        $user = User::factory()->create(['name' => 'Lucía', 'surname' => 'Pérez Romero']);
        $user->notify($notice);

        $mails = collect(Mail::mailer('array')->getSymfonyTransport()->messages())
            ->map(fn (SentMessage $message) => $message->getOriginalMessage())
            ->all();
        $this->assertCount(1, $mails);

        return $mails[0];
    }

    public function test_a_notice_arrives_with_the_theme_in_spanish_and_the_mark_embedded(): void
    {
        $mail = $this->deliver(new ThemedNotice('Montaje del photocall', 'Faltan las fotos.', ['Reparto de tareas', 'Turnos']));
        $html = (string) $mail->getHtmlBody();
        $text = (string) $mail->getTextBody();

        $this->assertSame('Evidencia rechazada · Montaje del photocall', $mail->getSubject());
        $this->assertStringContainsString('Hola, Lucía', $html);
        $this->assertStringContainsString('ha rechazado tu evidencia «Montaje del photocall» del comité Logística.', $html);
        $this->assertStringContainsString('Faltan las fotos.', $html);
        $this->assertMatchesRegularExpression('/<li[^>]*>Reparto de tareas<\/li>\s*<li[^>]*>Turnos<\/li>/', $html);
        $this->assertStringContainsString('Ver la evidencia', $html);
        $this->assertStringContainsString('Si el botón no funciona, copia este enlace en el navegador', $html);
        $this->assertStringContainsString('Evidentia · Universidad de Sevilla · ETSII', $html);
        $this->assertStringContainsString('Correo automático. No respondas a este mensaje, entra en la aplicación.', $html);
        $this->assertStringContainsString('#624ad3', $html, 'The button carries the brand colour inlined');
        $this->assertStringContainsString('#eaebff', $html, 'The panel carries its colour inlined');
        foreach (['Laravel', 'All rights reserved', 'Regards', 'Hello', 'trouble clicking'] as $english) {
            $this->assertStringNotContainsString($english, $html);
            $this->assertStringNotContainsString($english, $text);
        }

        // the only image is the mark, embedded; no stylesheet, script or image comes from elsewhere
        $this->assertMatchesRegularExpression('/<img src="cid:[^"]+"[^>]*width="48"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<(img|script|link)[^>]+(src|href)="(https?:)?\/\//i', $html);
        $inline = array_filter($mail->getAttachments(), fn ($part) => $part->getDisposition() === 'inline');
        $this->assertCount(1, $inline);

        $this->assertStringContainsString('Faltan las fotos.', $text);
        $this->assertStringContainsString('Evidentia · Universidad de Sevilla · ETSII', $text);
        $this->assertStringNotContainsString('cid:', $text);
    }

    public function test_a_text_written_by_someone_never_becomes_a_link(): void
    {
        $mail = $this->deliver(new ThemedNotice('[premio](http://x.test)', 'Mira [aquí](http://y.test)', ['[lista](http://z.test)']));
        $html = (string) $mail->getHtmlBody();

        foreach (['x', 'y', 'z'] as $host) {
            $this->assertStringNotContainsString('href="http://'.$host.'.test"', $html);
        }
        $this->assertStringContainsString('[premio](http://x.test)', $html);
        $this->assertStringContainsString('Mira [aquí](http://y.test)', $html);
    }

    public function test_the_smtp_mailer_gives_up_after_ten_seconds(): void
    {
        $this->assertSame(10, config('mail.mailers.smtp.timeout'));
    }

    public function test_the_preview_without_a_message_keeps_the_name_in_text(): void
    {
        $user = User::factory()->create(['name' => 'Lucía']);
        $html = (string) (new ThemedNotice('Montaje del photocall'))->toMail($user)->render();

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('Evidentia', $html);
        $this->assertStringNotContainsString('class="panel"', $html, 'No empty panel without a quoted text');
    }
}
