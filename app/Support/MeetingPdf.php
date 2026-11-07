<?php

namespace App\Support;

use App\Models\MeetingMinutes;
use App\Models\MeetingRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * PDFs of meeting calls and minutes. They are stored when saved and rendered again on every download, so a change
 * of the template (or of a name) reaches the documents of past meetings; the stored copy is refreshed on the way.
 */
final class MeetingPdf
{
    public static function storeRequest(MeetingRequest $request): string
    {
        return self::store('meeting.request_template', ['meeting_request' => $request], self::requestPath($request));
    }

    public static function storeMinutes(MeetingMinutes $minutes): string
    {
        return self::store('meeting.minutes_template', ['meeting_minutes' => $minutes], self::minutesPath($minutes));
    }

    public static function downloadRequest(MeetingRequest $request): Response
    {
        self::storeRequest($request);

        return self::download(self::requestPath($request));
    }

    public static function downloadMinutes(MeetingMinutes $minutes): Response
    {
        self::storeMinutes($minutes);

        return self::download(self::minutesPath($minutes));
    }

    private static function requestPath(MeetingRequest $request): string
    {
        return '/meeting_requests/meeting_request_'.$request->id.'.pdf';
    }

    private static function minutesPath(MeetingMinutes $minutes): string
    {
        return '/meeting_minutes/meeting_minutes_'.$minutes->id.'.pdf';
    }

    /** @param  array<string, mixed>  $data */
    private static function store(string $view, array $data, string $path): string
    {
        $level = ob_get_level();
        $content = Pdf::loadView($view, $data)->output();
        // dompdf may leave output buffers of its own open
        while (ob_get_level() > $level) {
            ob_end_clean();
        }
        Storage::put($path, $content);

        return $content;
    }

    private static function download(string $path): Response
    {
        $response = Storage::download($path);

        // a stray output buffer would corrupt the binary
        if (ob_get_level()) {
            ob_end_clean();
        }

        return $response;
    }
}
