<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Enums\ActivityFlag;
use App\Facades\Activity;
use App\Http\Middleware\CheckRoles;
use App\Http\Services\UserService;
use App\Models\Attendee;
use App\Models\Committee;
use App\Models\Event;
use App\Models\Evidence;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\User;
use App\Services\Activity\SubjectInfo;
use App\Support\Changes;
use App\Support\StrongPassword;
use App\View\CommitteeOverview;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManagementController extends Controller
{
    private $user_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class.':LECTURE,PRESIDENT');
        $this->user_service = new UserService;
    }

    public function user_list(Request $request)
    {

        $users = User::all();

        // el presidente no puede editar los usuarios de tipo profesor
        $filtered_users = collect();
        if (Auth::user()->hasRole('PRESIDENT')) {
            $users->each(function ($item, $key) use ($filtered_users) {
                if (! $item->hasRole('LECTURE')) {
                    $filtered_users->push($item);
                }
            });
        } else {
            $filtered_users = $users;
        }

        // filtros y orden del listado, en la URL
        $roles = Auth::user()->hasRole('PRESIDENT') ? Role::where('rol', '!=', 'LECTURE')->get() : Role::all();
        $filters = [
            'rol' => $roles->contains('rol', $request->query('rol')) ? $request->query('rol') : null,
            'estado' => in_array($request->query('estado'), ['activos', 'bloqueados'], true) ? $request->query('estado') : null,
        ];
        $sort = in_array($request->query('sort'), ['surname', 'name', 'username'], true) ? $request->query('sort') : 'surname';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $filtered_users = $filtered_users
            ->when($filters['rol'], fn ($list, $rol) => $list->filter(fn ($user) => $user->hasRole($rol)))
            ->when($filters['estado'] === 'activos', fn ($list) => $list->reject(fn ($user) => $user->block))
            ->when($filters['estado'] === 'bloqueados', fn ($list) => $list->filter(fn ($user) => $user->block))
            ->sortBy(fn ($user) => mb_strtolower((string) $user->{$sort}), SORT_NATURAL, $dir === 'desc')
            ->values();

        return view('manage.user_list',
            ['users' => $filtered_users, 'roles' => $roles, 'filters' => $filters, 'sort' => $sort, 'dir' => $dir]);
    }

    public function evidence_list(Request $request)
    {
        $committees = Committee::orderBy('name')->get(['id', 'name']);
        $filters = [
            'comite' => in_array((int) $request->query('comite'), $committees->pluck('id')->all(), true) ? (int) $request->query('comite') : null,
            'desde' => $this->date_query($request, 'desde'),
            'hasta' => $this->date_query($request, 'hasta'),
        ];

        $evidences = Evidence::where('last', true)->where('status', '!=', 'DRAFT')
            ->with(['user', 'committee', 'reasonRejection'])
            ->when($filters['comite'], fn ($query, $id) => $query->where('committee_id', $id))
            ->when($filters['desde'], fn ($query, $date) => $query->where('created_at', '>=', $date->copy()->startOfDay()))
            ->when($filters['hasta'], fn ($query, $date) => $query->where('created_at', '<=', $date->copy()->endOfDay()))
            ->orderByDesc('updated_at')->orderByDesc('id')->get();

        // filtro por estado, en la URL; «atrasadas» son las pendientes que esperan más de lo debido
        $late = fn (Evidence $evidence): bool => $evidence->status === 'PENDING'
            && $evidence->updated_at->lt(now()->subDays(CommitteeOverview::LATE_DAYS));
        $states = ['pendientes' => 'PENDING', 'atrasadas' => 'PENDING', 'aceptadas' => 'ACCEPTED', 'rechazadas' => 'REJECTED'];
        $state = array_key_exists((string) $request->query('estado'), $states) ? $request->query('estado') : null;
        $counts = $evidences->countBy('status')->put('LATE', $evidences->filter($late)->count());
        if ($state !== null) {
            $evidences = $evidences->where('status', $states[$state])
                ->when($state === 'atrasadas', fn ($list) => $list->filter($late))
                ->values();
        }

        return view('manage.evidence_list', [
            'evidences' => $evidences, 'state' => $state, 'counts' => $counts, 'filters' => $filters,
            'committees' => $committees->map(fn (Committee $committee): array => ['value' => (string) $committee->id, 'label' => $committee->name])->all(),
            'lateDays' => CommitteeOverview::LATE_DAYS,
        ]);
    }

    /** A date of the query string in Y-m-d, or null when missing or malformed. */
    private function date_query(Request $request, string $key): ?Carbon
    {
        $value = $request->query($key);
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }
        try {
            return Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function meeting_list(Request $request)
    {
        $committees = Committee::orderBy('name')->get(['id', 'name']);
        $filters = [
            'comite' => in_array((int) $request->query('comite'), $committees->pluck('id')->all(), true) ? (int) $request->query('comite') : null,
            'desde' => $this->date_query($request, 'desde'),
            'hasta' => $this->date_query($request, 'hasta'),
            'acta' => in_array($request->query('acta'), ['con', 'sin'], true) ? $request->query('acta') : null,
        ];

        $meetings = Meeting::with(['committee', 'meetingMinutes'])->withCount('users')
            ->when($filters['comite'], fn ($query, $id) => $query->where('committee_id', $id))
            ->when($filters['desde'], fn ($query, $date) => $query->where('datetime', '>=', $date->copy()->startOfDay()))
            ->when($filters['hasta'], fn ($query, $date) => $query->where('datetime', '<=', $date->copy()->endOfDay()))
            ->when($filters['acta'] === 'con', fn ($query) => $query->has('meetingMinutes'))
            ->when($filters['acta'] === 'sin', fn ($query) => $query->doesntHave('meetingMinutes'))
            ->orderByDesc('datetime')->orderByDesc('id')->get();

        return view('manage.meeting_list', [
            'meetings' => $meetings, 'filters' => $filters,
            'committees' => $committees->map(fn (Committee $committee): array => ['value' => (string) $committee->id, 'label' => $committee->name])->all(),
        ]);
    }

    /** Filters of the attendances by state, over the literals Eventbrite stores */
    private const ATTENDEE_STATES = [
        'asistidas' => ['Checked In', 'Guests Attended'],
        'pendientes' => ['Attending', 'Guests Attending'],
        'no-asistidas' => ['Not Attending', 'Not Attending (Refunded/Canceled)'],
    ];

    public function attendee_list(Request $request)
    {
        // every event, hidden ones too, with its sign-ups and the attendances that count hours
        $events = Event::withCount(['attendees', 'attendees as attended_count' => fn ($query) => $query->where('status', Attendee::ATTENDED)])
            ->orderByDesc('start_datetime')->orderByDesc('id')->get();

        // filtros por evento y estado, en la URL
        $event = $events->firstWhere('id', (int) $request->query('evento'));
        $state = array_key_exists((string) $request->query('estado'), self::ATTENDEE_STATES) ? $request->query('estado') : null;

        $attendees = Attendee::with(['user', 'event'])
            ->when($event, fn ($query) => $query->where('event_id', $event->id))
            ->get();
        $counts = collect(self::ATTENDEE_STATES)->map(fn (array $statuses) => $attendees->whereIn('status', $statuses)->count())
            ->put('todas', $attendees->count());
        if ($state !== null) {
            $attendees = $attendees->whereIn('status', self::ATTENDEE_STATES[$state]);
        }

        return view('manage.attendee_list', [
            'attendees' => $attendees->sortByDesc(fn ($attendee) => [$attendee->event?->start_datetime, $attendee->id])->values(),
            'events' => $events,
            'event' => $event,
            'state' => $state,
            'counts' => $counts,
        ]);
    }

    public function committee_list()
    {

        $committees = Committee::all();
        $summaries = app(CommitteeOverview::class)->summaries()->keyBy(fn (array $summary): int => $summary['committee']->id);

        $route = null;
        $route_new = null;
        $route_remove = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $route = route('president.committee.management.save');
            $route_new = route('president.committee.management.new');
            $route_remove = route('president.committee.management.remove');
        } else {
            $route = route('lecture.committee.management.save');
            $route_new = route('lecture.committee.management.new');
            $route_remove = route('lecture.committee.management.remove');
        }

        return view('manage.committee_list',
            ['committees' => $committees, 'summaries' => $summaries, 'prefix' => Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president',
                'route' => $route, 'route_new' => $route_new, 'route_remove' => $route_remove]);
    }

    public function committee_new(Request $request)
    {

        $request->validate([
            'icon' => 'max:255',
            'name' => 'required|max:255|unique:committees',
        ]);

        DB::transaction(function () use ($request) {
            $committee = Committee::create([
                'icon' => $request->input('icon'),
                'name' => $request->input('name'),
            ]);

            Activity::record(ActivityAction::CommitteeCreated, $committee);
        });

        if (Auth::user()->hasRole('PRESIDENT')) {
            return redirect()->route('president.committee.list')->with('success', 'Comité creado con éxito.');
        } else {
            return redirect()->route('lecture.committee.list')->with('success', 'Comité creado con éxito.');
        }
    }

    public function committee_save(Request $request)
    {

        $returned_route = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $returned_route = 'president.committee.list';
        } else {
            $returned_route = 'lecture.committee.list';
        }

        // los nombres resultantes no pueden repetirse, igual que al crear un comité
        $names = Committee::all()->map(fn ($committee) => mb_strtolower(trim($request->input('name_'.$committee->id) ?: $committee->name)));
        if ($names->duplicates()->isNotEmpty()) {
            return redirect()->route($returned_route)->with('error', 'Dos comités no pueden llamarse igual.');
        }

        DB::transaction(function () use ($request) {
            foreach (Committee::all() as $committee) {

                $new_icon = $request->input('icon_'.$committee->id);
                $new_name = $request->input('name_'.$committee->id);

                if ($new_name != '') {

                    $saved_committee = Committee::find($committee->id);
                    $before = $saved_committee->only(['icon', 'name']);
                    $saved_committee->icon = $new_icon;
                    $saved_committee->name = $new_name;

                    $saved_committee->save();

                    // solo los comités que cambian
                    if (Changes::any($before, $saved_committee->only(['icon', 'name']))) {
                        Activity::record(ActivityAction::CommitteeEdited, $saved_committee, details: Changes::between($before, $saved_committee->only(['icon', 'name'])));
                    }
                }
            }
        });

        return redirect()->route($returned_route)->with('success', 'Comités guardados con éxito.');
    }

    public function committee_remove(Request $request)
    {

        $returned_route = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $returned_route = 'president.committee.list';
        } else {
            $returned_route = 'lecture.committee.list';
        }

        $committee = Committee::find($request->input('_id'));
        if ($committee->can_be_removed()) {
            DB::transaction(function () use ($committee) {
                // se anota antes de borrar: el registro copia el nombre del comité
                Activity::record(ActivityAction::CommitteeDeleted, $committee, subjectTitle: $committee->name);

                $committee->delete();
            });

            return redirect()->route($returned_route)->with('success', 'Comité eliminado con éxito.');
        } else {
            return redirect()->route($returned_route)->with('error', 'El comité no ha podido ser eliminado.');
        }

    }

    public function user_management($id)
    {

        $user = User::findOrFail($id);
        $this->abort_unless_can_manage($user);

        $route = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $route = route('president.user.management.save');
        } else {
            $route = route('lecture.user.management.save');
        }

        // el presidente no puede crear usuarios de tipo profesor
        $roles = null;
        if (Auth::user()->hasRole('PRESIDENT')) {
            $roles = Role::where('rol', '!=', 'LECTURE')->get();
        } else {
            $roles = Role::all();
        }

        $committees = Committee::all();

        return view('manage.user_management',
            ['route' => $route, 'user' => $user, 'roles' => $roles, 'committees' => $committees]);
    }

    public function user_management_save(Request $request)
    {
        $user = User::findOrFail($request->input('user_id'));
        $this->abort_unless_can_manage($user);

        // el presidente no puede dar el rol de profesor
        $roles_id = Role::whereIn('id', $request->input('roles', []))
            ->when(! Auth::user()->hasRole('LECTURE'), fn ($query) => $query->where('rol', '!=', 'LECTURE'))
            ->pluck('id');
        $needs_committee = Role::whereIn('id', $roles_id)->whereIn('rol', ['COORDINATOR', 'SECRETARY'])->exists();

        // validamos todo antes de modificar nada
        $request->validate([
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'username' => ['required', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', StrongPassword::rule()],
            'committee' => $needs_committee ? 'required|exists:committees,id' : 'nullable',
        ], [
            'committee.required' => 'Comité no válido.',
            'committee.exists' => 'Comité no válido.',
        ]);

        $committee_id = $request->input('committee');
        $before = $this->user_snapshot($user);

        DB::transaction(function () use ($request, $user, $roles_id, $committee_id, $before) {
            // guardar bloqueo
            $user->block = $request->input('pass') == 'on' ? false : true;

            // asignar nuevos roles
            $user->roles()->sync($roles_id);
            $user->load('roles');

            // asignar coordinador o secretario, conservando su fila (actas, convocatorias, listas...)
            foreach (['coordinators' => 'COORDINATOR', 'secretaries' => 'SECRETARY'] as $table => $role) {
                if ($user->hasRole($role)) {
                    DB::table($table)->updateOrInsert(['user_id' => $user->id], ['committee_id' => $committee_id]);
                } else {
                    DB::table($table)->where('user_id', $user->id)->delete();
                }
            }

            $user->username = $request->input('username');
            $user->name = $request->input('name');
            $user->surname = $request->input('surname');
            $user->email = $request->input('email');
            $user->clean_name = \StringUtilites::clean(trim($request->input('name')));
            $user->clean_surname = \StringUtilites::clean(trim($request->input('surname')));

            // cambio de contraseña (si se indica)
            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();

            $this->record_user_changes($user, $before, $this->user_snapshot($user), $request->filled('password'));
        });

        // redirección según el rol del que ejecuta la acción
        if (Auth::user()->hasRole('PRESIDENT')) {
            return redirect()->route('president.user.list', ['id' => $user->id])
                ->with('success', 'Usuario actualizado con éxito');
        }

        return redirect()->route('lecture.user.list', ['id' => $user->id])
            ->with('success', 'Usuario actualizado con éxito');
    }

    // datos, roles, comité y bloqueo de un usuario, tal como se anotan
    private function user_snapshot(User $user): array
    {
        $committee_id = DB::table('coordinators')->where('user_id', $user->id)->value('committee_id')
            ?? DB::table('secretaries')->where('user_id', $user->id)->value('committee_id');

        return [
            'data' => $user->only(['username', 'name', 'surname', 'email']),
            'roles' => $user->roles()->pluck('rol')->sort()->values()->all(),
            'committee' => $committee_id === null ? null : Committee::whereKey($committee_id)->value('name'),
            'block' => (bool) $user->block,
        ];
    }

    // una anotación por cada clase de cambio; el gestor que se cambia a sí mismo queda señalado
    private function record_user_changes(User $user, array $before, array $after, bool $password): void
    {
        $record = fn (ActivityAction $action, array $details = []) => Activity::record(
            $action,
            $user,
            $user,
            details: $details,
            flags: (int) $user->id === (int) Auth::id() ? [ActivityFlag::RoleOverlap] : [],
        );

        $data = ['committee' => $before['committee']] + $before['data'];
        $new_data = ['committee' => $after['committee']] + $after['data'];
        if (Changes::any($data, $new_data)) {
            $record(ActivityAction::UserEdited, Changes::between($data, $new_data));
        }

        $roles = ['roles' => $before['roles'], 'committee' => $before['committee']];
        $new_roles = ['roles' => $after['roles'], 'committee' => $after['committee']];
        if (Changes::any($roles, $new_roles)) {
            $record(ActivityAction::UserRolesChanged, ['before' => $roles, 'after' => $new_roles]);
        }

        if ($before['block'] !== $after['block']) {
            $record($after['block'] ? ActivityAction::UserBlocked : ActivityAction::UserUnblocked);
        }

        if ($password) {
            $record(ActivityAction::UserPasswordChangedByManager);
        }
    }

    // el presidente no puede editar los usuarios de tipo profesor
    private function abort_unless_can_manage(User $user): void
    {
        if (! Auth::user()->hasRole('LECTURE') && $user->hasRole('LECTURE')) {
            abort(404);
        }
    }

    public function user_management_new(Request $request)
    {

        $request->validate([
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'username' => 'required|max:255|unique:users',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->input('name'),
                'surname' => $request->input('surname'),
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'password' => Hash::make(\Random::getRandomIdentifier(16)),
                'clean_name' => \StringUtilites::clean(trim($request->input('name'))),
                'clean_surname' => \StringUtilites::clean(trim($request->input('surname'))),
            ]);

            $student_role = Role::where('rol', 'STUDENT')->get();
            $user->roles()->attach($student_role);

            // nunca la contraseña
            Activity::record(ActivityAction::UserCreated, $user, $user, details: ['username' => $user->username, 'roles' => ['STUDENT']]);
        });

        $list = Auth::user()->hasRole('PRESIDENT') ? 'president.user.list' : 'lecture.user.list';

        return redirect()->route($list)->with('success', 'Usuario creado con éxito');

    }

    public function user_management_delete_all(Request $request)
    {
        $users = $this->user_service->all_except_logged();

        // una transacción por usuario; todas las anotaciones comparten la petición
        foreach ($users as $user) {
            DB::transaction(function () use ($user) {
                Activity::record(
                    ActivityAction::UserDeleted,
                    $user,
                    $user,
                    details: ['bulk' => true, 'roles' => $user->roles()->pluck('rol')->sort()->values()->all()],
                    subjectTitle: SubjectInfo::personName($user),
                );

                $this->user_service->delete($user->id);
            });
        }

        return redirect()->route('lecture.user.list', [])->with('success', 'Usuarios borrados con éxito');
    }
}
