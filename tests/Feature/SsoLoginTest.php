<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Configuration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsoLoginTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Configuration::forceCreate([]);
        $this->student = User::factory()->create([
            'name' => 'Ana',
            'surname' => 'García',
            'username' => 'alumnouso',
            'email' => 'alumno@alum.us.es',
            'password' => Hash::make('secret123'),
        ]);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
    }

    public function test_the_us_sso_button_is_hidden_by_default_when_sso_is_not_configured(): void
    {
        config([
            'services.us_sso.enabled' => false,
            'services.us_sso.mock_enabled' => false,
        ]);

        $response = $this->get(route('login'))->assertOk();
        $html = (string) preg_replace('/\s+/', ' ', (string) $response->getContent());

        $this->assertStringNotContainsString('Entrar con la cuenta de la US', $html);
        $this->assertStringNotContainsString(route('sso.redirect'), $html);

        $this->get(route('sso.redirect'))->assertNotFound();
        $this->get(route('sso.callback'))->assertNotFound();
        $this->get(route('sso.mock'))->assertNotFound();
    }

    public function test_the_us_sso_button_appears_on_the_login_page_when_sso_is_configured(): void
    {
        config([
            'services.us_sso.enabled' => true,
            'services.us_sso.base_url' => 'https://sso.us.es/cas',
            'services.us_sso.mock_enabled' => false,
        ]);

        $response = $this->get(route('login'))->assertOk();
        $html = (string) preg_replace('/\s+/', ' ', (string) $response->getContent());

        $this->assertStringContainsString('Entrar con la cuenta de la US', $html);
        $this->assertStringContainsString('href="'.route('sso.redirect').'"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('name="password"', $html);
    }

    public function test_redirect_sends_the_browser_to_the_configured_cas_login_url(): void
    {
        config([
            'services.us_sso.enabled' => true,
            'services.us_sso.base_url' => 'https://sso.us.es/cas',
            'services.us_sso.login_path' => '/login',
            'services.us_sso.mock_enabled' => false,
        ]);

        $expected = 'https://sso.us.es/cas/login?'.http_build_query(['service' => route('sso.callback')]);

        $this->get(route('sso.redirect'))->assertRedirect($expected);
    }

    public function test_an_imported_user_signs_in_through_cas_ticket_and_leaves_the_same_activity_log_as_password_login(): void
    {
        config([
            'services.us_sso.enabled' => true,
            'services.us_sso.base_url' => 'https://sso.us.es/cas',
            'services.us_sso.validate_path' => '/p3/serviceValidate',
            'services.us_sso.mock_enabled' => false,
        ]);

        Http::fake([
            'https://sso.us.es/cas/p3/serviceValidate*' => Http::response(
                <<<'XML'
                <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                    <cas:authenticationSuccess>
                        <cas:user>ALUMNOUSO</cas:user>
                    </cas:authenticationSuccess>
                </cas:serviceResponse>
                XML,
                200,
                ['Content-Type' => 'application/xml']
            ),
        ]);

        $this->get(route('sso.callback', ['ticket' => 'ST-12345-us']))
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($this->student);

        $log = ActivityLog::sole();
        $this->assertSame('login', $log->action);
        $this->assertSame($this->student->id, $log->user_id);
        $this->assertSame('alumnouso', $log->user_username);
        $this->assertNull($log->acting_role);
        $this->assertSame('user', $log->subject_type);
        $this->assertSame(['remember' => false, 'via' => 'form', 'blocked' => false], $log->details);
        $this->assertSame('127.0.0.1', $log->ip);
    }

    public function test_an_unimported_uvus_is_rejected_with_a_clear_message_and_records_a_failed_login(): void
    {
        config([
            'services.us_sso.enabled' => true,
            'services.us_sso.base_url' => 'https://sso.us.es/cas',
            'services.us_sso.validate_path' => '/p3/serviceValidate',
            'services.us_sso.mock_enabled' => false,
        ]);

        Http::fake([
            'https://sso.us.es/cas/p3/serviceValidate*' => Http::response(
                <<<'XML'
                <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                    <cas:authenticationSuccess>
                        <cas:user>noimportado</cas:user>
                    </cas:authenticationSuccess>
                </cas:serviceResponse>
                XML,
                200,
                ['Content-Type' => 'application/xml']
            ),
        ]);

        $this->get(route('sso.callback', ['ticket' => 'ST-99999-us']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');

        $this->assertGuest();

        $html = (string) $this->get(route('login'))->assertOk()->getContent();
        $this->assertStringContainsString('Tu cuenta UVUS («noimportado») no está importada o autorizada en Evidentia.', $html);

        $log = ActivityLog::sole();
        $this->assertSame('login_failed', $log->action);
        $this->assertNull($log->user_id);
        $this->assertNull($log->target_user_id);
        $this->assertSame(['context' => 'login', 'known' => false], $log->details);
    }

    public function test_an_invalid_cas_ticket_denies_access_and_records_a_failed_login(): void
    {
        config([
            'services.us_sso.enabled' => true,
            'services.us_sso.base_url' => 'https://sso.us.es/cas',
            'services.us_sso.validate_path' => '/p3/serviceValidate',
            'services.us_sso.mock_enabled' => false,
        ]);

        Http::fake([
            'https://sso.us.es/cas/p3/serviceValidate*' => Http::response(
                <<<'XML'
                <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
                    <cas:authenticationFailure code="INVALID_TICKET">
                        Ticket ST-bad not recognized
                    </cas:authenticationFailure>
                </cas:serviceResponse>
                XML,
                200,
                ['Content-Type' => 'application/xml']
            ),
        ]);

        $this->get(route('sso.callback', ['ticket' => 'ST-bad']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');

        $this->assertGuest();
        $this->assertSame('login_failed', ActivityLog::sole()->action);
    }

    public function test_local_mock_mode_simulates_both_successful_and_rejected_logins_and_is_blocked_in_production(): void
    {
        config([
            'services.us_sso.enabled' => false,
            'services.us_sso.mock_enabled' => true,
        ]);

        $loginHtml = (string) preg_replace('/\s+/', ' ', (string) $this->get(route('login'))->assertOk()->getContent());
        $this->assertStringContainsString('Entrar con la cuenta de la US', $loginHtml);

        $this->get(route('sso.redirect'))->assertRedirect(route('sso.mock'));

        $mockHtml = (string) $this->get(route('sso.mock'))->assertOk()->getContent();
        $this->assertStringContainsString('Simulador SSO de la Universidad de Sevilla', $mockHtml);
        $this->assertStringContainsString('action="'.route('sso.callback').'"', $mockHtml);
        $this->assertStringContainsString('name="uvus"', $mockHtml);

        $this->get(route('sso.callback', ['uvus' => 'intruso']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('sso');
        $this->assertGuest();

        $this->get(route('sso.callback', ['uvus' => 'alumnouso']))
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($this->student);

        auth()->logout();
        $this->app['env'] = 'production';

        $this->get(route('sso.mock'))->assertNotFound();
    }
}
