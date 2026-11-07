<?php

namespace Tests\Feature\Notifications;

use App\Models\ActivityLog;
use App\Models\NotificationPreference;
use App\Services\Notifications\NotificationPreferences;
use Tests\Feature\Screens\PublicAndStudent\ScreenTestCase;

/**
 * Which notices also arrive by mail: the fixed ones can never be turned off, each person only sees the notices that
 * can reach them, only coordinators and secretaries get the digest and every change is annotated.
 */
class NotificationPreferencesPageTest extends ScreenTestCase
{
    public function test_the_coordinator_sees_the_three_levels_and_the_digest(): void
    {
        $html = $this->assertAppScreen($this->signedAs('coordinador')->get(route('notifications.preferences')));

        $this->assertStringContainsString('Siempre en la bandeja y por correo', $html);
        $this->assertStringContainsString('Evidencia rechazada', $html);
        $this->assertMatchesRegularExpression('/name="fixed_evidence_rejected"[^>]*role="switch"\s+checked\s+disabled/', $html);
        $this->assertStringContainsString('No se puede desactivar', $html);
        $this->assertMatchesRegularExpression('/name="mail\[\]"\s+value="evidence\.submitted"[^>]*checked/', $html, 'Important: mail on by default');
        $this->assertMatchesRegularExpression('/name="mail\[\]"\s+value="meeting\.convened"(?![^>]*checked)[^>]*>/', $html, 'Informative: mail off by default');
        $this->assertStringContainsString('Resumen diario', $html);
        $this->assertStringContainsString('name="digest"', $html);
        $this->assertStringContainsString('coordinador@e2e.test', $html);
        $this->assertNoAutofocus($html);
    }

    public function test_the_student_sees_only_what_can_reach_her_and_no_digest(): void
    {
        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('notifications.preferences')));

        $this->assertStringContainsString('Evidencia aceptada', $html);
        $this->assertStringNotContainsString('Evidencia recibida en tu comité', $html);
        $this->assertStringNotContainsString('name="digest"', $html);
        $this->assertStringNotContainsString('coordinador@e2e.test', $html, 'Nothing of someone else');
    }

    public function test_saving_keeps_only_the_deviations_and_annotates_the_change(): void
    {
        $coordinator = $this->person('coordinador');

        $this->signedAs('coordinador')
            ->post(route('notifications.preferences.save'), ['mail' => ['meeting.convened', 'evidence.accepted'], 'digest' => '1'])
            ->assertRedirect(route('notifications.preferences'))
            ->assertSessionHas('success', 'Preferencias guardadas.');

        $preference = NotificationPreference::where('user_id', $coordinator->id)->sole();
        $this->assertTrue($preference->digest);
        $this->assertEqualsCanonicalizing([
            'evidence.moved' => false, 'evidence.submitted' => false, 'meeting.convened' => true,
            'minutes.registered' => false, 'user.roles_changed' => false, 'evidence.coauthored' => false,
        ], array_filter($preference->mail, fn ($value, $key) => ! str_starts_with($key, 'message.'), ARRAY_FILTER_USE_BOTH));

        $log = ActivityLog::where('action', 'notification.preferences_changed')->sole();
        $this->assertSame($coordinator->id, $log->user_id);
        $this->assertNull($log->acting_role);
        $this->assertTrue($log->details['after']['digest']);

        $html = $this->assertAppScreen($this->get(route('notifications.preferences')));
        $this->assertMatchesRegularExpression('/name="mail\[\]"\s+value="meeting\.convened"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/name="digest"\s+value="1"[^>]*checked/', $html);
    }

    public function test_the_student_cannot_take_the_digest_nor_an_unknown_notice(): void
    {
        $student = $this->person('estudiante');

        $this->signedAs('estudiante')->post(route('notifications.preferences.save'), ['digest' => '1']);
        $this->assertFalse(app(NotificationPreferences::class)->digest($student->fresh()));

        $this->post(route('notifications.preferences.save'), ['mail' => ['evidence.submitted']])->assertSessionHasErrors('mail.0');
        $this->post(route('notifications.preferences.save'), ['mail' => ['error.reported']])->assertSessionHasErrors('mail.0');
    }

    public function test_the_profile_and_the_inbox_lead_here(): void
    {
        $html = $this->assertAppScreen($this->signedAs('estudiante')->get(route('profile.view')));
        $this->assertStringContainsString('href="'.route('notifications.preferences').'"', $html);

        $html = $this->assertAppScreen($this->get(route('notifications.index')));
        $this->assertStringContainsString('href="'.route('notifications.preferences').'"', $html);
    }
}
