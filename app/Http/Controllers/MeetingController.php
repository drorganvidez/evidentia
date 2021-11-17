<?php

namespace App\Http\Controllers;

use App\Exports\MyEvidencesExport;
use App\Exports\MyMeetingsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class MeetingController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:PRESIDENT|COORDINATOR|REGISTER_COORDINATOR|SECRETARY|STUDENT');
    }

    public function list()
    {
        $instance = \Instantiation::instance();

        $meetings = Auth::user()->meetings()->get();

        return view('meeting.mylist',
            ['instance' => $instance, 'meetings' => $meetings]);
    }

    public function export($instance, $ext)
    {
        try {
            ob_end_clean();
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new MyMeetingsExport(), 'misreuniones-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
