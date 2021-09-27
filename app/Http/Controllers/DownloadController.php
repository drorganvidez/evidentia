<?php

namespace App\Http\Controllers;

use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function request_download($instance, $id)
    {
        $meeting_request = MeetingRequest::findOrFail($id);

        $response = Storage::download(\Instantiation::instance() .'/meeting_requests/meeting_request_' .$meeting_request->id . '.pdf');

        // limpiar búfer de salida
        ob_end_clean();

        return $response;
    }

    public function minutes_download($instance, $id)
    {
        $meeting_minutes = MeetingMinutes::findOrFail($id);

        $response = Storage::download(\Instantiation::instance() .'/meeting_minutes/meeting_minutes_' .$meeting_minutes->id . '.pdf');

        // limpiar búfer de salida
        ob_end_clean();

        return $response;
    }
}
