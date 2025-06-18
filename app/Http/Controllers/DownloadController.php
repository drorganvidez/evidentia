<?php

namespace App\Http\Controllers;

use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function request_download($id)
    {
        $meeting_request = MeetingRequest::findOrFail($id);

        $response = Storage::download('/meeting_requests/meeting_request_' .$meeting_request->id . '.pdf');

        // limpiar búfer de salida
        if (ob_get_level()) {
            ob_end_clean();
        }

        return $response;
    }

    public function minutes_download($id)
    {
        $meeting_minutes = MeetingMinutes::findOrFail($id);

        $response = Storage::download('/meeting_minutes/meeting_minutes_' .$meeting_minutes->id . '.pdf');

        // limpiar búfer de salida
        if (ob_get_level()) {
            ob_end_clean();
        }

        return $response;
    }
}
