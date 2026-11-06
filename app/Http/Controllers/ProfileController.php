<?php

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Facades\Activity;
use App\Models\Evidence;
use App\Models\User;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use App\Support\Changes;
use App\Support\StrongPassword;
use App\View\Dashboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function view(Dashboard $dashboard)
    {

        $route_upload_info = route('profile.upload.info');
        $route_upload_biography = route('profile.upload.biography');
        $route_upload_pass = route('profile.upload.pass');

        return view('profile.view',
            ['route_upload_info' => $route_upload_info,
                'route_upload_biography' => $route_upload_biography,
                'route_upload_pass' => $route_upload_pass,
                'hours' => $dashboard->hours(Auth::user())]);
    }

    public function upload_info(Request $request)
    {

        $user = Auth::user();

        $request->validate([
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $before = $user->only(['name', 'surname', 'email']);

        DB::transaction(function () use ($request, $user, $before) {
            $user->name = $request->input('name');
            $user->surname = $request->input('surname');
            $user->email = $request->input('email');

            $user->clean_name = \StringUtilites::clean($request->input('name'));
            $user->clean_surname = \StringUtilites::clean($request->input('surname'));

            $user->save();

            Activity::record(ActivityAction::ProfileEdited, $user, details: Changes::between($before, $user->only(['name', 'surname', 'email'])));
        });

        return redirect()->route('profile.view')->with('success', 'Datos personales editados con éxito.');

    }

    public function upload_biography(Request $request)
    {

        $user = Auth::user();

        $request->validate([
            'biography' => ['required', new MinCharacters(10), new MaxCharacters(20000)],
        ]);

        $before = $user->biography;

        DB::transaction(function () use ($request, $user, $before) {
            $user->biography = $request->input('biography');

            $user->save();

            // de la biografía solo su longitud, nunca el texto
            Activity::record(ActivityAction::ProfileEdited, $user, details: [
                'fields' => $before !== $user->biography ? ['biography'] : [],
                'length' => mb_strlen(strip_tags((string) $user->biography)),
            ]);
        });

        return redirect()->route('profile.view')->with('success', 'Biografía editada con éxito.');

    }

    public function upload_pass(Request $request)
    {

        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', StrongPassword::rule()],
        ]);

        DB::transaction(function () use ($request, $user) {
            $user->password = Hash::make($request->input('password'));

            $user->save();

            Activity::record(ActivityAction::UserPasswordChanged, $user);
        });

        return redirect()->route('profile.view')->with('success', 'Contraseña cambiada con éxito.');

    }

    public function profiles_view(Dashboard $dashboard, $id)
    {

        $user = User::find($id);

        return view('profile.generalview',
            ['user' => $user, 'hours' => $dashboard->hours($user)]);
    }

    public function evidences_view(Dashboard $dashboard, $id_user, $id_evidence)
    {

        $user = User::find($id_user);
        $evidence = Evidence::find($id_evidence);

        if ($evidence == null) {
            return redirect()->route('home', []);
        } elseif ($evidence->status == 'DRAFT') {
            return redirect()->route('home', []);
        }

        return view('profile.profile_evidence_view',
            ['user' => $user, 'evidence' => $evidence, 'hours' => $dashboard->hours($user)]);
    }
}
