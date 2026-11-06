{{--
    Complete HTML document for dompdf: Helvetica, flat hex colours of the palette, a header with the brand mark and the
    event, a footer and page numbers when dompdf may run PHP. The same markup opens in a browser (/_ui/pdf).
    <x-pdf.document title="Acta de reunión" committee="Comité de Programa" subtitle="Reunión ordinaria 3">
        <x-pdf.section :number="1" title="Datos de la reunión"><x-pdf.table key-value :rows="$rows" /></x-pdf.section>
    </x-pdf.document>
    The logo is a file path (embedded as a data URI so dompdf and the browser both show it) or a URL.
--}}
@props([
    'title' => null,
    'event' => null,
    'committee' => null,
    'subtitle' => null,
    'logo' => null,
    'pageNumbers' => true,
    'footerText' => null,
])
@php
    throw_if(blank($title), InvalidArgumentException::class, 'The pdf document needs a title.');
    $event ??= 'Jornadas InnoSoft Days ' . now()->year;
    $logo ??= public_path('img/brand/mark-256.png');
    $logoSrc = null;
    if (is_string($logo) && $logo !== '') {
        if (preg_match('#^(data:|https?://)#', $logo) === 1) {
            $logoSrc = $logo;
        } elseif (is_file($logo)) {
            $logoSrc =
                'data:' .
                (mime_content_type($logo) ?: 'image/png') .
                ';base64,' .
                base64_encode(file_get_contents($logo));
        }
    }
    // dompdf runs the embedded PHP only with enable_php; the browser ignores a script of that type
    $numbered = $pageNumbers && (bool) config('dompdf.options.enable_php');
@endphp
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>{{ $title }} · {{ $event }}</title>
    <style nonce="{{ Vite::cspNonce() }}">
        @page {
            margin: 30mm 18mm 24mm;
        }

        body {
            margin: 0 auto;
            max-width: 180mm;
            padding: 14mm 12mm;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10.5pt;
            line-height: 1.45;
            color: #282d36;
            background: #ffffff;
        }

        p {
            margin: 0 0 6pt;
        }

        ol,
        ul {
            margin: 0 0 6pt;
            padding-left: 18pt;
        }

        li {
            margin-bottom: 2pt;
        }

        .pdf-header,
        .pdf-footer {
            font-size: 8.5pt;
            color: #616875;
        }

        .pdf-header {
            border-bottom: 1px solid #d4d9e3;
            padding-bottom: 6pt;
            margin-bottom: 14pt;
        }

        .pdf-header__table {
            width: 100%;
            border-collapse: collapse;
        }

        .pdf-header__table td {
            padding: 0;
            vertical-align: middle;
            border: 0;
        }

        .pdf-header__mark {
            width: 28px;
            height: 28px;
            vertical-align: middle;
        }

        .pdf-header__name {
            display: inline-block;
            padding-left: 6pt;
            font-size: 11pt;
            font-weight: bold;
            color: #282d36;
            vertical-align: middle;
        }

        .pdf-header__event {
            text-align: right;
        }

        .pdf-footer {
            border-top: 1px solid #d4d9e3;
            padding-top: 6pt;
            margin-top: 18pt;
            text-align: right;
        }

        .pdf-title {
            margin: 0 0 4pt;
            font-size: 20pt;
            line-height: 1.2;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #624ad3;
        }

        .pdf-subtitle {
            margin: 0 0 2pt;
            font-size: 12pt;
            font-weight: bold;
        }

        .pdf-committee {
            margin: 0 0 12pt;
            color: #616875;
        }

        .pdf-section {
            margin-top: 16pt;
        }

        .pdf-section--break {
            page-break-before: always;
        }

        .pdf-section__title {
            margin: 0 0 8pt;
            padding-bottom: 4pt;
            border-bottom: 1px solid #d4d9e3;
            font-size: 13pt;
            line-height: 1.3;
            color: #624ad3;
        }

        h3.pdf-section__title {
            margin-bottom: 4pt;
            padding-bottom: 0;
            border-bottom: 0;
            font-size: 11.5pt;
            color: #282d36;
        }

        .pdf-section__number {
            color: #616875;
            padding-right: 4pt;
        }

        .pdf-table {
            width: 100%;
            margin: 6pt 0 10pt;
            border-collapse: collapse;
            font-size: 10pt;
        }

        .pdf-table th,
        .pdf-table td {
            border: 1px solid #d4d9e3;
            padding: 5pt 7pt;
            text-align: left;
            vertical-align: top;
        }

        .pdf-table--compact th,
        .pdf-table--compact td {
            padding: 3pt 5pt;
            font-size: 9pt;
        }

        .pdf-table--brand thead th {
            background: #624ad3;
            color: #ffffff;
        }

        .pdf-table--plain thead th {
            background: #f3f7fe;
            color: #282d36;
        }

        .pdf-table tbody th {
            width: 32%;
            background: #f3f7fe;
            font-weight: bold;
        }

        .pdf-muted {
            color: #616875;
        }

        .pdf-accepted {
            color: #007c45;
        }

        .pdf-pending {
            color: #8b5d00;
        }

        .pdf-rejected {
            color: #bc2028;
        }

        .pdf-signature {
            margin-top: 28pt;
            text-align: center;
        }

        .pdf-signature__name {
            margin: 14pt 0 2pt;
            font-size: 12pt;
            font-weight: bold;
        }

        /* Only dompdf reads this media type: the header and the footer repeat on every page inside its margins */
        @media dompdf {
            body {
                max-width: none;
                padding: 0;
            }

            .pdf-header {
                position: fixed;
                top: -22mm;
                left: 0;
                right: 0;
                margin: 0;
            }

            .pdf-footer {
                position: fixed;
                bottom: -16mm;
                left: 0;
                right: 0;
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <header class="pdf-header">
        <table class="pdf-header__table">
            <tr>
                <td>
                    @if ($logoSrc !== null)
                        <img class="pdf-header__mark" src="{{ $logoSrc }}" alt="" width="28" height="28">
                    @endif
                    <span class="pdf-header__name">Evidentia</span>
                </td>
                <td class="pdf-header__event">{{ $event }}</td>
            </tr>
        </table>
    </header>
    <footer class="pdf-footer">{{ $footerText }}</footer>
    <main>
        <h1 class="pdf-title">{{ $title }}</h1>
        @if ($subtitle !== null)
            <p class="pdf-subtitle">{{ $subtitle }}</p>
        @endif
        @if ($committee !== null)
            <p class="pdf-committee">{{ $committee }}</p>
        @endif
        {{ $slot }}
    </main>
    @if ($numbered)
        <script type="text/php" nonce="{{ Vite::cspNonce() }}">
            if (isset($pdf)) {
                $font = $fontMetrics->getFont('Helvetica');
                $size = 8.5;
                $width = $fontMetrics->getTextWidth('Página 00 de 00', $font, $size);
                $pdf->page_text($pdf->get_width() - 51 - $width, $pdf->get_height() - 48, 'Página {PAGE_NUM} de {PAGE_COUNT}', $font, $size, [0.38, 0.41, 0.46]);
            }
        </script>
    @endif
</body>

</html>
