<?php

namespace Tests\Feature;

use App\Mail\PasswordReset;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->user = User::factory()->create(['email' => 'ana@us.es', 'password' => Hash::make('original-pass')]);
    }

    public function test_a_valid_link_changes_the_password_once(): void
    {
        $token = $this->token(used: false, validUntil: now()->addDay());

        $this->changePassword($token->token, 'Nueva-Pass-26')
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');
        $this->assertTrue(Hash::check('Nueva-Pass-26', $this->user->fresh()->password));

        $this->changePassword($token->token, 'Otra-Clave-26')->assertSessionHas('error');
        $this->assertTrue(Hash::check('Nueva-Pass-26', $this->user->fresh()->password));
    }

    public function test_an_expired_link_does_not_change_the_password(): void
    {
        $token = $this->token(used: false, validUntil: now()->subDay());

        $this->changePassword($token->token, 'Atacante-Clave-26')->assertSessionHas('error');

        $this->assertTrue(Hash::check('original-pass', $this->user->fresh()->password));
    }

    public function test_requesting_a_new_link_invalidates_the_old_one(): void
    {
        $this->post(route('password.reset_p'), ['email' => 'ana@us.es']);
        $old = Token::sole()->token;
        $this->post(route('password.reset_p'), ['email' => 'ana@us.es']);

        $this->changePassword($old, 'Atacante-Clave-26')->assertSessionHas('error');

        $this->assertTrue(Hash::check('original-pass', $this->user->fresh()->password));
    }

    public function test_the_mail_links_to_the_route_of_the_token_in_the_theme_of_the_app(): void
    {
        $this->post(route('password.reset_p'), ['email' => 'ana@us.es']);
        $token = Token::sole();

        Mail::assertSent(PasswordReset::class, function (PasswordReset $mail) use ($token): bool {
            $mail->assertHasSubject('Restablecimiento de contraseña');
            $mail->assertSeeInHtml('href="'.route('password.custom_update', $token->token).'"', false);
            $mail->assertSeeInHtml('Elegir una contraseña nueva');
            $mail->assertSeeInHtml('Evidentia · Universidad de Sevilla · ETSII');
            $mail->assertSeeInText('El enlace caduca en 24 horas.');
            $mail->assertDontSeeInHtml('googleapis', false);
            $mail->assertDontSeeInHtml('adminlte', false);

            return $mail->hasTo('ana@us.es');
        });
    }

    private function token(bool $used, $validUntil): Token
    {
        return Token::create([
            'token' => Str::random(255),
            'used' => $used,
            'valid_until_timestamp' => $validUntil,
            'user_id' => $this->user->id,
        ]);
    }

    private function changePassword(string $token, string $password)
    {
        return $this->post(route('password.update_p', ['token' => $token]), [
            'password' => $password,
            'password_confirmation' => $password,
        ]);
    }
}
