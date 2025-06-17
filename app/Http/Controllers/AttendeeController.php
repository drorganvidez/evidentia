<?php

namespace App\Http\Controllers;

use App\Exports\MyAttendeesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Middleware\CheckRoles;

class AttendeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class . ':STUDENT');
    }

    public function list()
    {
        $attendees = Auth::user()->attendees;

        $events_update = \Config::events_uploaded_timestamp();
        $attendees_update = \Config::attendees_uploaded_timestamp();

        return view('attendee.list',
            ['attendees' => $attendees,
                'events_update' => $events_update, 'attendees_update' => $attendees_update]);
    }

    public function export($instance, $ext)
    {
        try {
            ob_end_clean();
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new MyAttendeesExport(), 'misasistencias-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
