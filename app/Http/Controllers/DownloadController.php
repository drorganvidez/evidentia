<?php

namespace App\Http\Controllers;

use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use App\Support\MeetingPdf;

class DownloadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function request_download($id)
    {
        $meeting_request = MeetingRequest::findOrFail($id);

        return MeetingPdf::downloadRequest($meeting_request);
    }

    public function minutes_download($id)
    {
        $meeting_minutes = MeetingMinutes::findOrFail($id);

        return MeetingPdf::downloadMinutes($meeting_minutes);
    }
}
