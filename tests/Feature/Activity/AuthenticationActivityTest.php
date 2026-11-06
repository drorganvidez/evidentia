<?php

namespace Tests\Feature\Activity;

use App\Models\ActivityLog;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Sign-ins go through POST /login for real: actingAs() only fires Authenticated, so the rest of the suite records no
 * access at all.
 */
class AuthenticationActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        Configuration::forceCreate([]);
        $this->student = User::factory()->create(['email' => 'ana@us.es', 'password' => Hash::make('secret123'), 'username' => 'anagar']);
        $this->student->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
    }

    public function test_a_sign_in_through_the_form_is_recorded_once(): void
    {
        $this->post(route('login'), ['email' => 'ana@us.es', 'password' => 'secret123', 'remember' => 'on'])
            ->assertRedirect(route('home'));

        $log = ActivityLog::sole();
        $this->assertSame('login', $log->action);
        $this->assertSame($this->student->id, $log->user_id);
        $this->assertSame('anagar', $log->user_username);
        $this->assertNull($log->acting_role);
        $this->assertSame('user', $log->subject_type);
        $this->assertSame(['remember' => true, 'via' => 'form', 'blocked' => false], $log->details);
        $this->assertSame('127.0.0.1', $log->ip);
    }

    public function test_a_blocked_person_signing_in_is_recorded_as_blocked(): void
    {
        $this->student->update(['block' => true]);

        $this->post(route('login'), ['email' => 'ana@us.es', 'password' => 'secret123']);

        $this->assertSame(['remember' => false, 'via' => 'form', 'blocked' => true], ActivityLog::where('action', 'login')->sole()->details);
    }

    public function test_wrong_credentials_are_recorded_without_the_credentials(): void
    {
        $this->post(route('login'), ['email' => 'ana@us.es', 'password' => 'guessed-password']);

        $log = ActivityLog::sole();
        $this->assertSame('login_failed', $log->action);
        $this->assertNull($log->user_id);
        $this->assertSame($this->student->id, $log->target_user_id);
        $this->assertSame(['context' => 'login', 'known' => true], $log->details);
        $this->assertStringNotContainsString('guessed-password', json_encode($log->getAttributes()));
        $this->assertStringNotContainsString('ana@us.es', json_encode($log->getAttributes()));
    }

    public function test_an_unknown_email_is_recorded_without_a_target_and_without_the_email(): void
    {
        $this->post(route('login'), ['email' => 'nadie@us.es', 'password' => 'secret123']);

        $log = ActivityLog::sole();
        $this->assertSame('login_failed', $log->action);
        $this->assertNull($log->target_user_id);
        $this->assertSame(['context' => 'login', 'known' => false], $log->details);
        $this->assertStringNotContainsString('nadie@us.es', json_encode($log->getAttributes()));
    }

    public function test_the_sixth_attempt_is_recorded_as_a_lockout(): void
    {
        foreach (range(1, 6) as $attempt) {
            $this->post(route('login'), ['email' => 'ana@us.es', 'password' => "wrong-$attempt"]);
        }

        $this->assertSame(6, ActivityLog::where('action', 'login_failed')->count());
        $lockout = ActivityLog::latest('id')->first();
        $this->assertSame(['context' => 'login', 'lockout' => true], $lockout->details);
        $this->assertSame($this->student->id, $lockout->target_user_id);
    }

    public function test_a_sign_out_is_recorded_once(): void
    {
        $this->actingAs($this->student)->post(route('logout'))->assertRedirect();

        $log = ActivityLog::sole();
        $this->assertSame('logout', $log->action);
        $this->assertSame($this->student->id, $log->user_id);
        $this->assertNull($log->acting_role);
    }

    public function test_acting_as_someone_records_nothing(): void
    {
        $this->actingAs($this->student)->get(route('home'))->assertOk();

        $this->assertSame(0, ActivityLog::count());
    }

    public function test_the_public_signature_records_only_the_signature(): void
    {
        $sheet = $this->sheet();

        $this->post(route('sign_p'), ['signature_sheet' => '7391', 'email' => 'ana@us.es', 'password' => 'secret123'])
            ->assertRedirect(route('sign.finish'));

        $log = ActivityLog::sole();
        $this->assertSame('signature.signed', $log->action);
        $this->assertSame($this->student->id, $log->user_id);
        $this->assertSame($this->student->id, $log->target_user_id);
        $this->assertNull($log->acting_role);
        $this->assertSame($sheet->id, $log->subject_id);
        $this->assertSame(['meeting_request_id' => null], $log->details);
        $this->assertGuest();
    }

    public function test_signing_twice_is_recorded_once(): void
    {
        $this->sheet();

        $this->post(route('sign_p'), ['signature_sheet' => '7391', 'email' => 'ana@us.es', 'password' => 'secret123']);
        $this->post(route('sign_p'), ['signature_sheet' => '7391', 'email' => 'ana@us.es', 'password' => 'secret123']);

        $this->assertSame(1, ActivityLog::count());
    }

    public function test_the_limit_of_the_public_signature_is_recorded_as_a_lockout(): void
    {
        $this->sheet();

        foreach (range(1, 6) as $attempt) {
            $this->post(route('sign_p'), ['signature_sheet' => '7391', 'email' => 'ana@us.es', 'password' => "wrong-$attempt"]);
        }

        $this->assertSame(6, ActivityLog::where('action', 'login_failed')->count());
        $lockout = ActivityLog::latest('id')->first();
        $this->assertSame(['context' => 'sign', 'lockout' => true], $lockout->details);
        $this->assertSame($this->student->id, $lockout->target_user_id);
    }

    public function test_a_secretary_signing_their_own_sheet_is_recorded_as_blocked(): void
    {
        $sheet = $this->sheet();
        $secretary = $sheet->secretary->user;
        $secretary->update(['password' => Hash::make('secret123')]);

        $this->post(route('sign_p'), ['signature_sheet' => '7391', 'email' => $secretary->email, 'password' => 'secret123'])
            ->assertRedirect(route('sign.finish'))
            ->assertSessionHas('sign_message', 'Eres el secretario de esta reunión, no es necesario que firmes en tu propia hoja de firmas.');

        $this->assertFalse($sheet->users()->whereKey($secretary->id)->exists());
        $log = ActivityLog::sole();
        $this->assertSame('self_review.blocked', $log->action);
        $this->assertSame($secretary->id, $log->user_id);
        $this->assertSame(['attempted' => 'signature.signed'], $log->details);
        $this->assertSame(['self_review'], $log->flags);
        $this->assertGuest();
    }

    public function test_wrong_credentials_on_the_public_signature_are_recorded_in_its_context(): void
    {
        $this->sheet();

        $this->post(route('sign_p'), ['signature_sheet' => '7391', 'email' => 'ana@us.es', 'password' => 'wrong']);

        $log = ActivityLog::sole();
        $this->assertSame('login_failed', $log->action);
        $this->assertSame(['context' => 'sign', 'known' => true], $log->details);
    }

    public function test_requesting_a_new_password_is_recorded_with_the_mail_result(): void
    {
        Mail::fake();

        $this->post(route('password.reset_p'), ['email' => 'ana@us.es'])->assertRedirect(route('login'));
        $this->post(route('password.reset_p'), ['email' => 'nadie@us.es'])->assertRedirect(route('login'));

        $log = ActivityLog::sole();
        $this->assertSame('password_reset_requested', $log->action);
        $this->assertNull($log->user_id);
        $this->assertSame($this->student->id, $log->target_user_id);
        $this->assertSame(['mail_sent' => true], $log->details);
    }

    public function test_changing_the_password_through_the_link_is_recorded_once(): void
    {
        $token = Token::create(['token' => str_repeat('a', 60), 'used' => false, 'valid_until_timestamp' => now()->addDay(), 'user_id' => $this->student->id]);

        $this->post(route('password.update_p', ['token' => $token->token]), ['password' => 'nueva-clave', 'password_confirmation' => 'nueva-clave'])
            ->assertSessionHas('success');

        $log = ActivityLog::sole();
        $this->assertSame('password_reset', $log->action);
        $this->assertSame($this->student->id, $log->user_id);
        $this->assertNull($log->acting_role);
        $this->assertNull($log->details);
    }

    private function sheet(): SignatureSheet
    {
        $secretary = User::factory()->create();
        $secretary->roles()->attach(Role::forceCreate(['rol' => 'SECRETARY', 'slug' => 'secretary']));

        return SignatureSheet::create([
            'title' => 'Hoja de firmas',
            'random_identifier' => '7391',
            'secretary_id' => Secretary::forceCreate([
                'user_id' => $secretary->id,
                'committee_id' => Committee::create(['name' => 'Logística'])->id,
            ])->id,
        ]);
    }
}
