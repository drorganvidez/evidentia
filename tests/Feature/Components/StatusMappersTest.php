<?php

namespace Tests\Feature\Components;

class StatusMappersTest extends ComponentTestCase
{
    public function test_every_evidence_status_maps_to_a_tone_and_a_spanish_text(): void
    {
        $expected = [
            'DRAFT' => ['draft', 'En borrador'],
            'PENDING' => ['pending', 'Pendiente de revisión'],
            'ACCEPTED' => ['accepted', 'Aceptada'],
            'REJECTED' => ['rejected', 'Rechazada'],
            'BIN' => ['draft', 'Eliminada'],
            'OTRO' => ['draft', 'Desconocido'],
        ];

        foreach ($expected as $status => [$tone, $text]) {
            $html = $this->render('<x-ui.evidence-status :status="$status" />', ['status' => $status]);

            $this->assertStringContainsString('bg-'.$tone.'-soft text-'.$tone.'-ink', $html, $status);
            $this->assertStringContainsString('[--orb:var(--'.$tone.')]', $html, $status);
            $this->assertMatchesRegularExpression('/>\s*'.preg_quote($text, '/').'\s*<\/span>/', $html, $status);
            $this->assertStringNotContainsString($status, $html, 'The stored value never shows');
            $this->assertCspSafe($html);
        }
    }

    public function test_only_a_pending_evidence_pulses(): void
    {
        $this->assertStringContainsString('animate-pulse-orb', $this->render('<x-ui.evidence-status status="PENDING" />'));

        foreach (['DRAFT', 'ACCEPTED', 'REJECTED', 'BIN', null] as $status) {
            $this->assertStringNotContainsString('animate-pulse-orb', $this->render('<x-ui.evidence-status :status="$status" />', ['status' => $status]));
        }
    }

    public function test_the_reason_of_a_rejection_goes_to_the_title_and_the_size_and_classes_pass_through(): void
    {
        $rejected = $this->render('<x-ui.evidence-status status="REJECTED" :reason="$reason" size="sm" class="ml-2" />', ['reason' => 'Faltan <pruebas>']);

        $this->assertStringContainsString('title="Faltan &lt;pruebas&gt;"', $rejected);
        $this->assertStringContainsString('px-2 py-0.5 text-2xs', $rejected);
        $this->assertStringContainsString('ml-2', $rejected);
        $this->assertStringNotContainsString('<pruebas>', $rejected);

        $this->assertStringNotContainsString('title=', $this->render('<x-ui.evidence-status status="REJECTED" />'));
        $this->assertStringNotContainsString('title=', $this->render('<x-ui.evidence-status status="PENDING" reason="Ignorado" />'));
        $this->assertStringContainsString('py-2.5 pr-4.5 pl-3.5 text-base', $this->render('<x-ui.evidence-status status="ACCEPTED" size="lg" />'));
    }

    public function test_every_attendee_status_maps_to_a_tone_and_a_text(): void
    {
        $expected = [
            'Attending' => ['pending', 'Pendiente de asistir'],
            'Checked In' => ['accepted', 'Asistido'],
            'Guests Attended' => ['accepted', 'Asistido (invitado)'],
            'Guests Attending' => ['pending', 'Pendiente de asistir (invitado)'],
            'Not Attending (Refunded/Canceled)' => ['rejected', 'Asistencia cancelada'],
            'Not Attending' => ['rejected', 'No asistido'],
        ];

        foreach ($expected as $status => [$tone, $text]) {
            $html = $this->render('<x-ui.attendee-status :status="$status" />', ['status' => $status]);

            $this->assertStringContainsString('bg-'.$tone.'-soft text-'.$tone.'-ink', $html, $status);
            $this->assertMatchesRegularExpression('/>\s*'.preg_quote($text, '/').'\s*<\/span>/', $html, $status);
            $this->assertStringNotContainsString('animate-pulse-orb', $html);
        }
    }

    public function test_every_event_status_maps_to_a_tone_and_a_text(): void
    {
        $expected = [
            'draft' => ['draft', 'En borrador'],
            'live' => ['accepted', 'Publicado'],
            'started' => ['notice', 'En curso'],
            'ended' => ['notice', 'Finalizado'],
            'completed' => ['draft', 'Completado'],
            'canceled' => ['rejected', 'Cancelado'],
        ];

        foreach ($expected as $status => [$tone, $text]) {
            $html = $this->render('<x-ui.event-status :status="$status" />', ['status' => $status]);

            $this->assertStringContainsString('bg-'.$tone.'-soft text-'.$tone.'-ink', $html, $status);
            $this->assertMatchesRegularExpression('/>\s*'.preg_quote($text, '/').'\s*<\/span>/', $html, $status);
        }
    }

    public function test_an_unknown_attendee_or_event_status_is_printed_escaped_in_the_draft_tone(): void
    {
        foreach (['attendee-status', 'event-status'] as $component) {
            $html = $this->render('<x-ui.'.$component.' :status="$status" size="sm" />', ['status' => '<b>Transferred</b>']);

            $this->assertStringContainsString('bg-draft-soft text-draft-ink', $html);
            $this->assertStringContainsString('&lt;b&gt;Transferred&lt;/b&gt;', $html);
            $this->assertStringNotContainsString('<b>', $html);
            $this->assertStringContainsString('px-2 py-0.5 text-2xs', $html);
            $this->assertCspSafe($html);

            $empty = $this->render('<x-ui.'.$component.' :status="$status" />', ['status' => null]);

            $this->assertMatchesRegularExpression('/>\s*Desconocido\s*<\/span>/', $empty);
        }
    }
}
