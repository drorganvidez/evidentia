{{-- PDF of a certificate of attendance or a diploma, with the code and the address that verifies it --}}
@php
    $hours = $certificate->hours ? rtrim(rtrim(number_format($certificate->hours, 2, ',', ''), '0'), ',') : null;
    $hoursText = $hours ? $hours . ($hours === '1' ? ' hora' : ' horas') : null;
    $start = $certificate->event?->start_datetime ? \Carbon\Carbon::parse($certificate->event->start_datetime) : null;
@endphp
<x-pdf.document :title="$certificate->typeLabel()" :subtitle="$certificate->recipient_name" :page-numbers="false"
    :footer-text="'Código ' . $certificate->code . ' · ' . $certificate->verifyUrl()">
    <style nonce="{{ Vite::cspNonce() }}">
        .certificate-text {
            margin-top: 18pt;
            font-size: 12pt;
            line-height: 1.6;
        }

        .certificate-note {
            margin-top: 10pt;
            font-size: 11pt;
            color: #3d4350;
        }

        .certificate-date {
            margin-top: 22pt;
            color: #616875;
        }

        .certificate-verify {
            margin-top: 34pt;
            padding: 10pt 12pt;
            border: 1px solid #d4d9e3;
            border-radius: 4pt;
            font-size: 9pt;
            color: #616875;
        }

        .certificate-code {
            font-family: Courier, monospace;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.08em;
            color: #282d36;
        }
    </style>

    @if ($certificate->isDiploma())
        <p class="certificate-text">Se otorga el presente diploma a <strong>{{ $certificate->recipient_name }}</strong>
            por <strong>{{ $certificate->title }}</strong>{{ $hoursText ? ', con un reconocimiento de ' . $hoursText : '' }}.</p>
        @if (filled($certificate->body))
            <p class="certificate-note">{{ $certificate->body }}</p>
        @endif
    @else
        <p class="certificate-text">Se certifica que <strong>{{ $certificate->recipient_name }}</strong> asistió al
            evento <strong>«{{ $certificate->title }}»</strong>{{ $start ? ', celebrado el ' . $start->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : '' }}{{ $hoursText ? ', con una duración de ' . $hoursText : '' }}.</p>
    @endif

    <p class="certificate-date">Emitido el {{ $certificate->issued_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
    </p>

    <div class="certificate-verify">
        Código de verificación <span class="certificate-code">{{ $certificate->code }}</span><br>
        Cualquiera puede comprobar su validez en {{ $certificate->verifyUrl() }}
    </div>
</x-pdf.document>
