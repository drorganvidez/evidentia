<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\Attendee;
use App\Models\Bonus;
use App\Models\Committee;
use App\Models\Configuration;
use App\Models\Coordinator;
use App\Models\DefaultList;
use App\Models\Diary;
use App\Models\DiaryPoint;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Models\Point;
use App\Models\ReasonRejection;
use App\Models\Role;
use App\Models\Secretary;
use App\Models\SignatureSheet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data of the end-to-end tests: one person per role (password «evidentia», email <role>@e2e.test), a committee
 * with its coordinator and secretary, evidences in every state, a meeting with its minutes, an event with
 * attendances, a bonus and a default list, with every deadline open. Logística has a second coordinator, whose
 * evidence to it waits in the queue of the presidency with one sent to the committee of the presidency, which the
 * migrations create.
 *
 *     php artisan migrate:fresh --seed --seeder=E2eSeeder --force
 */
class E2eSeeder extends Seeder
{
    public const PASSWORD = 'evidentia';

    public function run(): void
    {
        Configuration::query()->delete();
        Configuration::forceCreate([
            'id' => 1,
            'upload_evidences_timestamp' => now()->addMonth(),
            'validate_evidences_timestamp' => now()->addMonths(2),
            'meetings_timestamp' => now()->addMonth(),
            'bonus_timestamp' => now()->addMonth(),
            'attendee_timestamp' => now()->addMonth(),
            'secret' => 'e2e-secret',
        ]);

        $roles = [];
        foreach (['LECTURE' => 'Profesor', 'PRESIDENT' => 'Presidente', 'REGISTER_COORDINATOR' => 'Coordinador de registro',
            'COORDINATOR' => 'Coordinador', 'SECRETARY' => 'Secretario', 'STUDENT' => 'Estudiante'] as $rol => $slug) {
            $roles[$rol] = Role::where('rol', $rol)->first() ?? Role::forceCreate(['rol' => $rol, 'slug' => $slug]);
        }

        $person = function (string $key, string $name, string $surname, array $with) use ($roles): User {
            $user = User::forceCreate([
                'name' => $name, 'surname' => $surname, 'username' => $key, 'email' => "$key@e2e.test",
                'password' => Hash::make(self::PASSWORD), 'clean_name' => strtolower($name), 'clean_surname' => strtolower($surname),
            ]);
            $user->roles()->attach(array_map(fn (string $rol) => $roles[$rol]->id, $with));

            return $user;
        };

        $student = $person('estudiante', 'Lucía', 'Pérez Romero', ['STUDENT']);
        $classmate = $person('companero', 'Juan', 'García López', ['STUDENT']);
        $coordinator = $person('coordinador', 'Carlos', 'Navarro Ruiz', ['STUDENT', 'COORDINATOR']);
        $cocoordinator = $person('coordinadora', 'Inés', 'Castro Vidal', ['STUDENT', 'COORDINATOR']);
        $secretary = $person('secretario', 'Marta', 'Ruiz Díaz', ['STUDENT', 'SECRETARY']);
        $register = $person('registro', 'Pablo', 'Torres Gómez', ['STUDENT', 'REGISTER_COORDINATOR']);
        $person('presidente', 'Elena', 'Moreno Jiménez', ['PRESIDENT']);
        $person('profesor', 'David', 'Romero Organvídez', ['LECTURE']);

        $logistics = Committee::create(['name' => 'Logística']);
        Committee::create(['name' => 'Comunicación']);
        Coordinator::forceCreate(['user_id' => $coordinator->id, 'committee_id' => $logistics->id]);
        Coordinator::forceCreate(['user_id' => $cocoordinator->id, 'committee_id' => $logistics->id]);
        $secretaryProfile = Secretary::forceCreate(['user_id' => $secretary->id, 'committee_id' => $logistics->id]);

        $evidence = fn (User $author, string $title, string $status, float $hours) => Evidence::create([
            'title' => $title, 'description' => "<p>$title durante las jornadas.</p>", 'hours' => $hours,
            'status' => $status, 'user_id' => $author->id, 'committee_id' => $logistics->id,
        ]);
        $evidence($student, 'Montaje del photocall', 'PENDING', 1.75);
        $evidence($student, 'Cartelería del vestíbulo', 'ACCEPTED', 2);
        $rejected = $evidence($student, 'Recogida de material', 'REJECTED', 0.5);
        ReasonRejection::forceCreate(['evidence_id' => $rejected->id, 'reason' => 'Faltan las fotos del montaje terminado.']);
        $evidence($student, 'Borrador de la memoria', 'DRAFT', 1);
        $evidence($classmate, 'Atención en el registro', 'PENDING', 3);
        // the presidency reviews them (App\Services\ReviewQueue)
        $evidence($cocoordinator, 'Inventario del almacén', 'PENDING', 1.5);
        Evidence::create([
            'title' => 'Acreditación de ponentes', 'description' => '<p>Acreditación de ponentes durante las jornadas.</p>',
            'hours' => 2.5, 'status' => 'PENDING', 'user_id' => $secretary->id, 'committee_id' => Committee::presidency()?->id,
        ]);

        $request = MeetingRequest::forceCreate([
            'title' => 'Convocatoria de logística', 'place' => 'Aula A3.10', 'datetime' => now()->addWeek(),
            'type' => '1', 'modality' => '1', 'committee_id' => $logistics->id, 'secretary_id' => $secretaryProfile->id,
        ]);
        DiaryPoint::create(['diary_id' => Diary::create(['meeting_request_id' => $request->id])->id, 'point' => 'Reparto de tareas']);
        $sheet = SignatureSheet::create([
            'title' => 'Hoja de firmas de logística', 'random_identifier' => '7391', 'meeting_request_id' => $request->id,
            'secretary_id' => $secretaryProfile->id,
        ]);
        $sheet->users()->attach($student);

        $meeting = Meeting::forceCreate([
            'title' => 'Reunión de logística', 'datetime' => now()->subDays(3), 'place' => 'Aula A3.10',
            'type' => '1', 'modality' => '1', 'hours' => 2, 'committee_id' => $logistics->id,
        ]);
        $meeting->users()->attach([$student->id, $secretary->id]);
        $minutes = MeetingMinutes::create(['meeting_id' => $meeting->id, 'secretary_id' => $secretaryProfile->id]);
        $point = Point::create(['meeting_minutes_id' => $minutes->id, 'title' => 'Reparto de tareas', 'duration' => 20, 'description' => 'Equipos de montaje']);
        Agreement::create(['point_id' => $point->id, 'description' => 'Cada equipo nombra un responsable.', 'identificator' => 'ISD-1']);

        $bonus = Bonus::create(['reason' => 'Montaje del escenario', 'hours' => 3]);
        $bonus->committee()->associate($logistics)->save();
        $bonus->users()->attach($student);
        $list = DefaultList::create(['name' => 'Equipo de montaje', 'secretary_id' => $secretaryProfile->id]);
        $list->users()->attach([$student->id, $classmate->id]);

        $event = Event::forceCreate([
            'name' => 'Taller de Git', 'start_datetime' => now()->subDays(8)->setTime(8, 30),
            'end_datetime' => now()->subDays(8)->setTime(10, 0), 'hours' => 1.5, 'status' => 'completed',
        ]);
        Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $student->id, 'status' => 'Checked In']);
        Attendee::forceCreate(['event_id' => $event->id, 'user_id' => $register->id, 'status' => 'Checked In']);

        $this->call([E2e\ActivitySeeder::class, E2e\MessagesSeeder::class, E2e\NotificationsSeeder::class]);
    }
}
