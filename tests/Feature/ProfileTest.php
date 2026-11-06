<?php

namespace Tests\Feature;

use App\Mail\SuggestionsMailbox;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['email' => 'ana@us.es', 'password' => Hash::make('secret123')]);
        $this->user->roles()->attach(Role::forceCreate(['rol' => 'STUDENT', 'slug' => 'student']));
        $this->actingAs($this->user);
    }

    public function test_users_update_their_personal_data(): void
    {
        $this->post(route('profile.upload.info'), ['name' => 'Ana María', 'surname' => 'García Núñez', 'email' => 'anamaria@us.es'])
            ->assertRedirect(route('profile.view'));

        $this->user->refresh();
        $this->assertSame(['Ana María', 'García Núñez', 'anamaria@us.es'], [$this->user->name, $this->user->surname, $this->user->email]);
        $this->assertSame(['ANAMARIA', 'GARCIANUNEZ'], [$this->user->clean_name, $this->user->clean_surname]);
    }

    public function test_the_email_must_be_valid_and_free(): void
    {
        User::factory()->create(['email' => 'taken@us.es']);

        $this->post(route('profile.upload.info'), ['name' => 'Ana', 'surname' => 'García', 'email' => 'not-an-email'])
            ->assertSessionHasErrors('email');
        $this->post(route('profile.upload.info'), ['name' => 'Ana', 'surname' => 'García', 'email' => 'taken@us.es'])
            ->assertSessionHasErrors('email');

        $this->assertSame('ana@us.es', $this->user->fresh()->email);
    }

    public function test_users_write_their_biography(): void
    {
        $this->post(route('profile.upload.biography'), ['biography' => '<p>He colaborado en logística.</p>'])
            ->assertRedirect(route('profile.view'));
        $this->post(route('profile.upload.biography'), ['biography' => 'Corto'])
            ->assertSessionHasErrors('biography');

        $this->assertSame('<p>He colaborado en logística.</p>', $this->user->fresh()->biography);
        // The participation level no longer exists: the profile neither asks nor shows it
        $this->get(route('profile.view'))->assertOk()->assertDontSee('Nivel de implicación')->assertDontSee('Técnico-organizativo');
    }

    public function test_changing_the_password_needs_the_current_one(): void
    {
        $this->post(route('profile.upload.pass'), ['current_password' => 'wrong', 'password' => 'new-secret', 'password_confirmation' => 'new-secret'])
            ->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('secret123', $this->user->fresh()->password));

        $this->post(route('profile.upload.pass'), ['current_password' => 'secret123', 'password' => 'new-secret', 'password_confirmation' => 'new-secret'])
            ->assertRedirect(route('profile.view'));
        $this->assertTrue(Hash::check('new-secret', $this->user->fresh()->password));
    }

    public function test_users_send_suggestions(): void
    {
        Mail::fake();

        $this->post(route('suggestionsmailbox_p'), ['subject' => 'Mejora', 'comment' => '<p>Estaría bien poder exportar mis horas.</p>'])
            ->assertRedirect(route('suggestionsmailbox'));
        $this->post(route('suggestionsmailbox_p'), ['subject' => '', 'comment' => 'corto'])
            ->assertSessionHasErrors(['subject', 'comment']);

        Mail::assertSent(SuggestionsMailbox::class, 1);
    }
}
