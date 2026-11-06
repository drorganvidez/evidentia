<?php

namespace Tests\Feature\Notifications;

use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\Evidence;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\User;
use App\Notifications\BaseNotification;
use App\Notifications\Catalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

/**
 * Two committees with their people (a coordinator each, a secretary in Logística), a student, the president and a
 * lecturer, with the deadlines open.
 */
abstract class NotificationTestCase extends TestCase
{
    use RefreshDatabase;

    protected Committee $logistics;

    protected Committee $programme;

    protected User $student;

    protected User $coordinator;

    protected User $otherCoordinator;

    protected User $secretary;

    protected User $president;

    protected User $lecturer;

    /** @var array<string, Role> */
    private array $roles = [];

    protected function setUp(): void
    {
        parent::setUp();
        Catalog::flush();

        Configuration::forceCreate([
            'id' => 1,
            'upload_evidences_timestamp' => now()->addDays(10)->setTime(20, 0),
            'validate_evidences_timestamp' => now()->addDays(20)->setTime(20, 0),
            'meetings_timestamp' => now()->addDays(10)->setTime(20, 0),
            'bonus_timestamp' => now()->addMonth(),
            'attendee_timestamp' => now()->addMonth(),
            'secret' => 'salt',
        ]);
        $this->logistics = Committee::create(['name' => 'Logística']);
        $this->programme = Committee::create(['name' => 'Programa']);

        $this->student = $this->person('Lucía', 'Pérez Romero', ['STUDENT']);
        $this->coordinator = $this->person('Carlos', 'Navarro Ruiz', ['STUDENT', 'COORDINATOR']);
        Coordinator::forceCreate(['user_id' => $this->coordinator->id, 'committee_id' => $this->logistics->id]);
        $this->otherCoordinator = $this->person('Ana', 'Vega Sanz', ['STUDENT', 'COORDINATOR']);
        Coordinator::forceCreate(['user_id' => $this->otherCoordinator->id, 'committee_id' => $this->programme->id]);
        $this->secretary = $this->person('Marta', 'Ruiz Díaz', ['STUDENT', 'SECRETARY']);
        Secretary::forceCreate(['user_id' => $this->secretary->id, 'committee_id' => $this->logistics->id]);
        $this->president = $this->person('Elena', 'Moreno Jiménez', ['PRESIDENT']);
        $this->lecturer = $this->person('David', 'Romero Organvídez', ['LECTURE']);
    }

    protected function tearDown(): void
    {
        Catalog::flush();
        parent::tearDown();
    }

    /** @param  list<string>  $roles */
    protected function person(string $name, string $surname, array $roles): User
    {
        $user = User::factory()->create(['name' => $name, 'surname' => $surname]);
        foreach ($roles as $role) {
            $this->roles[$role] ??= Role::forceCreate(['rol' => $role, 'slug' => strtolower($role)]);
        }
        $user->roles()->attach(array_map(fn (string $role): int => $this->roles[$role]->id, $roles));

        return $user->load('roles');
    }

    protected function evidence(User $author, string $status, ?Committee $committee = null, string $title = 'Montaje del photocall', float $hours = 1.75): Evidence
    {
        return Evidence::create([
            'title' => $title, 'description' => '<p>'.$title.'.</p>', 'hours' => $hours, 'status' => $status,
            'user_id' => $author->id, 'committee_id' => ($committee ?? $this->logistics)->id, 'last' => true,
        ]);
    }

    /** Every line of the rendered mail, as plain text without the markup. */
    protected function mailText(BaseNotification $notification, User $notifiable): string
    {
        $mail = $notification->toMail($notifiable);
        $this->assertInstanceOf(MailMessage::class, $mail);

        return html_entity_decode(strip_tags((string) $mail->render()), ENT_QUOTES | ENT_HTML5);
    }
}
