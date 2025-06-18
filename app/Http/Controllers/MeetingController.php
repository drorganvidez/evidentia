<?php

namespace App\Http\Controllers;

use App\Exports\MeetingsExport;
use App\Exports\MyMeetingsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Middleware\CheckRoles;

class MeetingController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(CheckRoles::class . ':PRESIDENT,COORDINATOR,REGISTER_COORDINATOR,SECRETARY,STUDENT');
    }

    public function list()
    {

        $meetings = Auth::user()->meetings()->get();

        return view('meeting.mylist',
            ['meetings' => $meetings]);
    }


    public function meeting_export($ext)
    {
        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new MeetingsExport(), 'reunion-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function export($ext)
    {
        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            if(!in_array($ext, ['csv', 'pdf', 'xlsx'])){
                return back()->with('error', 'Solo se permite exportar los siguientes formatos: csv, pdf y xlsx');
            }
            return Excel::download(new MyMeetingsExport(), 'misreuniones-' . \Illuminate\Support\Carbon::now() . '.' . $ext);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
