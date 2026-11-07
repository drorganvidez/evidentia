<?php

namespace App\Support;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * PDF of a certificate or a diploma, rendered on every download so a correction of the template reaches the old ones;
 * the code and the data live in the database, the file is not stored.
 */
final class CertificatePdf
{
    public static function render(Certificate $certificate): string
    {
        $level = ob_get_level();
        $content = Pdf::loadView('certificate.pdf', ['certificate' => $certificate])->output();
        // dompdf may leave output buffers of its own open
        while (ob_get_level() > $level) {
            ob_end_clean();
        }

        return $content;
    }

    public static function download(Certificate $certificate): Response
    {
        return response(self::render($certificate), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$certificate->fileName().'"',
        ]);
    }
}
