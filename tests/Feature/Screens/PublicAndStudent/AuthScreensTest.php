<?php

namespace Tests\Feature\Screens\PublicAndStudent;

use App\Models\Token;
use Illuminate\Support\Str;

class AuthScreensTest extends ScreenTestCase
{
    public function test_the_login_shows_the_figures_of_the_edition_and_posts_the_same_fields(): void
    {
        $html = $this->assertPublicScreen($this->get(route('login')));

        $this->assertStringContainsString('action="'.route('login').'"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('name="remember"', $html);
        $this->assertStringContainsString('href="'.route('password.custom_reset').'"', $html);
        $this->assertStringContainsString('aria-label="2 h acreditadas"', $html);
        $this->assertStringContainsString('aria-label="1 evidencia aceptada"', $html);
        $this->assertStringContainsString('aria-label="5 estudiantes"', $html);
        $this->assertNoAutofocus($html);
    }

    public function test_a_failed_login_says_so_in_spanish(): void
    {
        $this->from(route('login'))->post(route('login'), ['email' => 'estudiante@e2e.test', 'password' => 'otra']);

        $html = $this->assertPublicScreen($this->get(route('login')));

        $this->assertStringContainsString('El correo o la contraseña no son correctos', $html);
        $this->assertStringContainsString('value="estudiante@e2e.test"', $html);
        $this->assertStringNotContainsString(e(__('auth.failed')), $html);
    }

    public function test_the_password_reset_asks_for_the_email(): void
    {
        $html = $this->assertPublicScreen($this->get(route('password.custom_reset')));

        $this->assertStringContainsString('action="'.route('password.reset_p').'"', $html);
        $this->assertStringContainsString('name="email"', $html);
    }

    public function test_the_new_password_keeps_the_token_in_the_action(): void
    {
        $value = Str::random(255);
        Token::create([
            'token' => $value, 'used' => 0, 'valid_until_timestamp' => now()->addDay(),
            'user_id' => $this->person('estudiante')->id,
        ]);

        $html = $this->assertPublicScreen($this->get(route('password.custom_update', ['token' => $value])));

        $this->assertStringContainsString('action="'.route('password.update_p', ['token' => $value]).'"', $html);
        $this->assertStringContainsString('name="password"', $html);
        $this->assertStringContainsString('name="password_confirmation"', $html);
    }
}
