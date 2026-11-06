<?php

namespace Tests\Feature\Screens\PublicAndStudent;

/** The public signature of a meeting, its end and the notice of a blocked account. */
class SignScreensTest extends ScreenTestCase
{
    public function test_the_public_signature_shows_the_call_and_posts_the_same_fields(): void
    {
        $html = $this->assertPublicScreen($this->get(route('sign', ['random_identifier' => '7391'])));

        $this->assertStringContainsString('Convocatoria de logística', $html);
        $this->assertStringContainsString('Aula A3.10', $html);
        $this->assertStringContainsString('action="'.route('sign_p').'"', $html);
        $this->assertStringContainsString('name="signature_sheet" value="7391"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertNoAutofocus($html);
    }

    public function test_the_end_of_a_signature_shows_its_result(): void
    {
        $this->post(route('sign_p'), ['signature_sheet' => '7391', 'email' => 'companero@e2e.test', 'password' => 'evidentia'])
            ->assertRedirect(route('sign.finish'));

        $html = $this->assertPublicScreen($this->get(route('sign.finish')));

        $this->assertStringContainsString('Firma registrada', $html);
        $this->assertStringContainsString('Tu firma ha sido validada y registrada correctamente.', $html);
    }

    public function test_a_blocked_account_can_only_sign_out(): void
    {
        $student = $this->person('estudiante');
        $student->forceFill(['block' => true])->save();

        $html = $this->assertPublicScreen($this->actingAs($student)->get(route('block')));

        $this->assertStringContainsString('Lucía, tu cuenta está bloqueada', $html);
        $this->assertStringContainsString('action="'.route('logout').'"', $html);
    }
}
