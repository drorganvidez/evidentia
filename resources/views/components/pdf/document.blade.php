{{--
    Complete HTML document for dompdf: Helvetica, flat hex colours of the palette, a header with the brand mark and the
    event, a footer and page numbers when dompdf may run PHP. The same markup opens in a browser (/_ui/pdf).
    <x-pdf.document title="Acta de reunión" committee="Comité de Programa" subtitle="Reunión ordinaria 3">
        <x-pdf.section :number="1" title="Datos de la reunión"><x-pdf.table key-value :rows="$rows" /></x-pdf.section>
    </x-pdf.document>
    The logo is the path of an image inside public/ or resources/, embedded as a data URI so dompdf and the browser
    both show it; any other path or a URL prints no logo.
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
    $event ??= \App\Models\Configuration::eventTitle();
    $logo ??= public_path('img/brand/mark-256.png');
    $logoSrc = null;
    // realpath resolves ../ and links before the check; a URL or a data URI has no real path
    $logoPath = is_string($logo) && $logo !== '' ? realpath($logo) : false;
    $logoRoots = array_map(
        fn(string $root): string => $root . DIRECTORY_SEPARATOR,
        array_filter([realpath(public_path()), realpath(resource_path())]),
    );
    if ($logoPath !== false && is_file($logoPath) && Str::startsWith($logoPath, $logoRoots)) {
        $logoMime = mime_content_type($logoPath) ?: '';
        if (str_starts_with($logoMime, 'image/')) {
            $logoSrc = 'data:' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    }
    // dompdf runs the embedded PHP only with enable_php; the browser ignores a script of that type
    $numbered = $pageNumbers && (bool) config('dompdf.options.enable_php');
@endphp
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>{{ $title }}{{ filled($event) ? ' · ' . $event : '' }}</title>
    <style nonce="{{ Vite::cspNonce() }}">
        @page {
            margin: 28mm 18mm 22mm;
        }

        body {
            margin: 0 auto;
            max-width: 180mm;
            padding: 14mm 12mm;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.5;
            color: #282d36;
            background: #ffffff;
        }

        p {
            margin: 0 0 5pt;
        }

        ol,
        ul {
            margin: 0 0 6pt;
            padding-left: 16pt;
        }

        li {
            margin-bottom: 2pt;
        }

        strong,
        b {
            font-weight: bold;
        }

        a {
            color: #624ad3;
        }

        .pdf-header,
        .pdf-footer {
            font-size: 8pt;
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
        }

        /* The kind of document, as a small label over the name of the meeting */
        .pdf-title {
            margin: 0 0 3pt;
            font-size: 8.5pt;
            line-height: 1.3;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #624ad3;
        }

        .pdf-subtitle {
            margin: 0 0 3pt;
            font-size: 19pt;
            line-height: 1.2;
            font-weight: bold;
            color: #282d36;
        }

        .pdf-committee {
            margin: 0 0 14pt;
            font-size: 10.5pt;
            color: #616875;
        }

        .pdf-section {
            margin-top: 18pt;
        }

        .pdf-keep {
            page-break-inside: avoid;
        }

        .pdf-section--break {
            page-break-before: always;
        }

        .pdf-section__title {
            margin: 0 0 8pt;
            padding-bottom: 4pt;
            border-bottom: 1px solid #d4d9e3;
            font-size: 9pt;
            line-height: 1.3;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #624ad3;
        }

        h3.pdf-section__title {
            margin-bottom: 4pt;
            padding-bottom: 0;
            border-bottom: 0;
            font-size: 11pt;
            letter-spacing: 0;
            text-transform: none;
            color: #282d36;
        }

        .pdf-section__number {
            color: #616875;
            padding-right: 5pt;
        }

        .pdf-table {
            width: 100%;
            margin: 4pt 0 10pt;
            border-collapse: collapse;
            font-size: 9.5pt;
        }

        .pdf-table th,
        .pdf-table td {
            border-bottom: 1px solid #e4e8ef;
            padding: 5pt 6pt;
            text-align: left;
            vertical-align: top;
        }

        .pdf-table thead th {
            font-size: 8pt;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .pdf-table--compact th,
        .pdf-table--compact td {
            padding: 3pt 5pt;
            font-size: 9pt;
        }

        .pdf-table--brand thead th {
            border-bottom: 1.5px solid #624ad3;
            color: #624ad3;
        }

        .pdf-table--plain thead th {
            background: #f3f7fe;
            color: #616875;
        }

        .pdf-table tbody th {
            width: 32%;
            color: #616875;
            font-weight: normal;
        }

        /* Facts of the meeting: a soft panel of label and value pairs, four per row */
        .pdf-meta {
            width: 100%;
            margin: 0 0 6pt;
            border-collapse: separate;
            border-spacing: 0;
            background: #f3f7fe;
            border-radius: 6pt;
        }

        .pdf-meta td {
            width: 25%;
            padding: 7pt 9pt;
            vertical-align: top;
        }

        .pdf-meta__label {
            display: block;
            font-size: 7.5pt;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #616875;
        }

        .pdf-meta__value {
            display: block;
            font-size: 10pt;
            font-weight: bold;
            color: #282d36;
        }

        /* Attendees in two columns, each name with its role in the committee */
        .pdf-people {
            width: 100%;
            border-collapse: collapse;
        }

        .pdf-people td {
            width: 50%;
            padding: 3pt 8pt 3pt 0;
            border-bottom: 1px solid #e4e8ef;
            vertical-align: top;
        }

        .pdf-count {
            color: #616875;
            font-weight: normal;
            letter-spacing: 0;
            text-transform: none;
        }

        .pdf-tag {
            padding: 0 4pt;
            margin-left: 3pt;
            font-size: 7.5pt;
            font-weight: bold;
            color: #624ad3;
            background: #efecfc;
            border-radius: 3pt;
        }

        /* One point of the agenda: number, title and duration, its text and its agreements */
        .pdf-point {
            margin: 0 0 14pt;
            padding-left: 10pt;
            border-left: 2pt solid #624ad3;
        }

        .pdf-point__head {
            width: 100%;
            margin: 0 0 4pt;
            border-collapse: collapse;
        }

        .pdf-point__head td {
            padding: 0;
            vertical-align: baseline;
        }

        .pdf-point__title {
            font-size: 11pt;
            font-weight: bold;
            color: #282d36;
        }

        .pdf-point__number {
            padding-right: 5pt;
            color: #624ad3;
        }

        .pdf-point__time {
            width: 50pt;
            font-size: 8.5pt;
            color: #616875;
            text-align: right;
            white-space: nowrap;
        }

        .pdf-rich p:last-child,
        .pdf-rich ul:last-child,
        .pdf-rich ol:last-child {
            margin-bottom: 0;
        }

        .pdf-rich blockquote {
            margin: 0 0 6pt;
            padding-left: 8pt;
            border-left: 2pt solid #d4d9e3;
            color: #616875;
        }

        .pdf-agreements {
            margin-top: 8pt;
            padding: 6pt 9pt;
            background: #f3f7fe;
            border-radius: 5pt;
        }

        .pdf-agreements__label {
            margin: 0 0 3pt;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #616875;
        }

        .pdf-agreements table {
            width: 100%;
            border-collapse: collapse;
        }

        .pdf-agreements td {
            padding: 3pt 0;
            vertical-align: top;
        }

        .pdf-agreements td.pdf-agreements__id {
            width: 62pt;
            padding-right: 8pt;
        }

        .pdf-pill {
            padding: 1pt 5pt;
            font-size: 8pt;
            font-weight: bold;
            color: #ffffff;
            background: #624ad3;
            border-radius: 3pt;
            white-space: nowrap;
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
            margin-top: 30pt;
            text-align: center;
            page-break-inside: avoid;
        }

        .pdf-signature__name {
            width: 70mm;
            margin: 26pt auto 2pt;
            padding-top: 5pt;
            border-top: 1px solid #282d36;
            font-size: 11pt;
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
                top: -20mm;
                left: 0;
                right: 0;
                margin: 0;
            }

            .pdf-footer {
                position: fixed;
                bottom: -14mm;
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
