{{-- PDF of a meeting call, rendered by dompdf when the call is created or saved --}}
@php
    $datetime = \Carbon\Carbon::parse($meeting_request->datetime);
    $committee = $meeting_request->committee->name;
    $secretary = trim($meeting_request->secretary->user->name . ' ' . $meeting_request->secretary->user->surname);
    $type = [1 => 'Ordinaria', 2 => 'Extraordinaria'][(int) $meeting_request->type] ?? '';
    $modality = [1 => 'Presencial', 2 => 'Telemática', 3 => 'Híbrida'][(int) $meeting_request->modality] ?? '';
@endphp
<x-pdf.document title="Convocatoria de reunión" :subtitle="$meeting_request->title" :committee="'Comité de ' . $committee" :footer-text="'Evidentia · Convocatoria del Comité de ' . $committee">
    <p>Por medio del presente escrito, queda usted convocado a la reunión {{ Str::lower($type) }} con modalidad
        {{ Str::lower($modality) }} del Comité de {{ $committee }}, en la que se tratarán los asuntos del orden del
        día.
    </p>

    <x-pdf.section :number="1" title="Datos de la reunión">
        <x-pdf.table key-value :rows="[
            'Fecha' => $datetime->format('d/m/Y'),
            'Hora' => $datetime->format('H:i'),
            'Lugar' => $meeting_request->place,
            'Tipo de reunión' => $type,
            'Modalidad' => $modality,
            'Convoca' => $secretary,
            'Comité' => $committee,
        ]" />
    </x-pdf.section>

    <x-pdf.section :number="2" title="Orden del día">
        <ol>
            @foreach ($meeting_request->diary?->diaryPoints ?? [] as $point)
                <li>{{ $point->point }}</li>
            @endforeach
        </ol>
    </x-pdf.section>

    <div class="pdf-signature">
        <p>Le saluda cordialmente</p>
        <p class="pdf-signature__name">{{ $secretary }}</p>
        <p class="pdf-muted">Secretaría del Comité de {{ $committee }}</p>
    </div>
</x-pdf.document>
