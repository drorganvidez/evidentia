<?php

namespace Tests\Feature\Notifications;

use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Role;
use App\Notifications\Events\MeetingConvened;
use App\Notifications\Events\MinutesRegistered;
use App\Notifications\Events\RoleChanged;
use App\Services\Notifications\NotificationPreferences;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/** The notices of the minutes, the meeting requests and the roles. */
class MeetingAndRoleNotificationsTest extends NotificationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        Notification::fake();
    }

    private function minutesForm(array $users, string $date): array
    {
        return [
            'title' => 'Reunión de montaje', 'type' => 1, 'modality' => 1, 'hours' => 2, 'minutes' => 30,
            'place' => 'Aula A3.10', 'date' => $date, 'time' => '16:30', 'users' => $users,
            'points_json' => json_encode([['title' => 'Reparto', 'duration' => 10, 'description' => 'Equipos', 'agreements' => [['description' => 'Cada equipo nombra un responsable.']]]]),
        ];
    }

    private function request(): MeetingRequest
    {
        return MeetingRequest::forceCreate([
            'title' => 'Convocatoria de logística', 'place' => 'Aula A3.10', 'datetime' => now()->subDay(),
            'type' => '1', 'modality' => '1', 'committee_id' => $this->logistics->id, 'secretary_id' => $this->secretary->secretary->id,
        ]);
    }

    public function test_new_minutes_reach_their_attendees_but_not_the_secretary(): void
    {
        $date = now()->format('Y-m-d');

        $this->actingAs($this->secretary)
            ->post(route('secretary.meeting.manage.minutes.create.step3_p'), ['meeting_request' => $this->request()->id]
                + $this->minutesForm([$this->student->id, $this->coordinator->id, $this->secretary->id], $date))
            ->assertRedirect();

        Notification::assertSentToTimes($this->student, MinutesRegistered::class, 1);
        Notification::assertSentToTimes($this->coordinator, MinutesRegistered::class, 1);
        Notification::assertNotSentTo($this->secretary, MinutesRegistered::class);
        Notification::assertNotSentTo($this->otherCoordinator, MinutesRegistered::class);

        $minutes = MeetingMinutes::sole();
        Notification::assertSentTo($this->student, MinutesRegistered::class, function (MinutesRegistered $notice) use ($minutes, $date): bool {
            $day = Carbon::parse($date)->format('d/m/Y');
            $this->assertSame('Te han incluido en un acta', $notice->title($this->student));
            $this->assertSame('Reunión de montaje · 2,5 h · '.$day, $notice->text($this->student));
            $this->assertSame('Acta registrada · Reunión de montaje', $notice->mailSubject($this->student));
            $this->assertSame(
                Route::has('activity.minutes') ? route('activity.minutes', ['id' => $minutes->id]) : route('meeting.list'),
                $notice->url($this->student),
            );
            $mail = $this->mailText($notice, $this->student);
            $this->assertStringContainsString('Marta Ruiz Díaz ha registrado el acta de «Reunión de montaje» del '.$day.' en el comité Logística.', $mail);
            $this->assertStringContainsString('Se te computan 2,5 h de reunión.', $mail);

            return true;
        });
    }

    public function test_edited_minutes_only_reach_the_people_added(): void
    {
        $meeting = Meeting::forceCreate([
            'title' => 'Reunión de logística', 'datetime' => now(), 'place' => 'Aula A3.10', 'type' => '1', 'modality' => '1',
            'hours' => 2, 'committee_id' => $this->logistics->id,
        ]);
        $meeting->users()->attach([$this->student->id, $this->secretary->id]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $this->secretary->secretary->id]);

        $this->actingAs($this->secretary)
            ->post(route('secretary.meeting.manage.minutes.save'), ['_id' => $minutes->id]
                + $this->minutesForm([$this->student->id, $this->secretary->id, $this->coordinator->id], now()->format('Y-m-d')))
            ->assertRedirect();

        Notification::assertSentToTimes($this->coordinator, MinutesRegistered::class, 1);
        Notification::assertNotSentTo($this->student, MinutesRegistered::class);
        Notification::assertNotSentTo($this->secretary, MinutesRegistered::class);
    }

    public function test_a_meeting_request_reaches_the_committee_and_its_authors_only_in_the_inbox(): void
    {
        $this->evidence($this->student, 'PENDING');
        $this->evidence($this->otherCoordinator, 'DRAFT', title: 'Borrador');
        $this->evidence($this->otherCoordinator, 'ACCEPTED', $this->programme, 'Otra');

        $this->actingAs($this->secretary)->post(route('secretary.meeting.manage.request.new'), [
            'title' => 'Reunión de montaje', 'place' => 'Aula A3.10', 'date' => now()->addWeek()->format('Y-m-d'),
            'time' => '18:00', 'type' => 1, 'modality' => 1, 'points_list' => json_encode(['Reparto de tareas', 'Turnos']),
        ])->assertRedirect();

        Notification::assertSentToTimes($this->coordinator, MeetingConvened::class, 1);
        Notification::assertSentToTimes($this->student, MeetingConvened::class, 1);
        Notification::assertNotSentTo($this->secretary, MeetingConvened::class);
        Notification::assertNotSentTo($this->otherCoordinator, MeetingConvened::class);
        Notification::assertNotSentTo($this->president, MeetingConvened::class);

        $request = MeetingRequest::where('title', 'Reunión de montaje')->sole();
        $day = now()->addWeek()->format('d/m/Y');
        Notification::assertSentTo($this->student, MeetingConvened::class, function (MeetingConvened $notice, array $channels) use ($request, $day): bool {
            $this->assertSame(['database'], $channels);
            $this->assertSame('Convocatoria de Logística', $notice->title($this->student));
            $this->assertSame('Reunión de montaje · '.$day.' 18:00 · Aula A3.10', $notice->text($this->student));
            $this->assertSame('Convocatoria · Reunión de montaje · '.now()->addWeek()->format('d/m').' 18:00', $notice->mailSubject($this->student));
            $this->assertSame(
                Route::has('activity.request') ? route('activity.request', ['id' => $request->id]) : route('download.request', ['id' => $request->id]),
                $notice->url($this->student),
            );
            $mail = $this->mailText($notice, $this->student);
            $this->assertStringContainsString('Marta Ruiz Díaz convoca una reunión del comité Logística el '.$day.' a las 18:00 en Aula A3.10.', $mail);
            $this->assertStringContainsString('Reparto de tareas', $mail);
            $this->assertStringContainsString('Turnos', $mail);

            return true;
        });

        // informative: the mail only for whoever turns it on
        app(NotificationPreferences::class)->update($this->coordinator, ['meeting.convened', 'evidence.submitted'], false);
        $this->actingAs($this->secretary)->post(route('secretary.meeting.manage.request.new'), [
            'title' => 'Segunda reunión', 'place' => 'Aula A3.11', 'date' => now()->addWeeks(2)->format('Y-m-d'),
            'time' => '18:00', 'type' => 1, 'modality' => 1, 'points_list' => json_encode(['Turnos']),
        ]);
        Notification::assertSentTo($this->coordinator, MeetingConvened::class, fn (MeetingConvened $notice, array $channels): bool => $notice->activity->subject_title === 'Segunda reunión' && $channels === ['database', 'mail']);
    }

    public function test_a_change_of_roles_reaches_the_person_with_the_roles_before_and_after(): void
    {
        $this->actingAs($this->lecturer)->post(route('lecture.user.management.save'), [
            'user_id' => $this->student->id, 'pass' => 'on', 'committee' => $this->logistics->id,
            'roles' => Role::whereIn('rol', ['STUDENT', 'SECRETARY'])->pluck('id')->all(),
            'username' => $this->student->username, 'name' => $this->student->name, 'surname' => $this->student->surname,
            'email' => $this->student->email,
        ])->assertRedirect();

        Notification::assertSentToTimes($this->student, RoleChanged::class, 1);
        Notification::assertNotSentTo($this->lecturer, RoleChanged::class);
        Notification::assertSentTo($this->student, RoleChanged::class, function (RoleChanged $notice): bool {
            $this->assertSame('Tus roles han cambiado', $notice->title($this->student));
            $this->assertSame('Ahora estudiante y secretario en el comité Logística', $notice->text($this->student));
            $this->assertSame(route('home'), $notice->url($this->student));
            $mail = $this->mailText($notice, $this->student);
            $this->assertStringContainsString('David Romero Organvídez ha cambiado tus roles.', $mail);
            $this->assertStringContainsString('Antes estudiante.', $mail);
            $this->assertStringContainsString('Ahora estudiante y secretario en el comité Logística.', $mail);

            return true;
        });
    }
}
