{{-- PDF of the minutes of a meeting, rendered by dompdf when the minutes are created or saved --}}
@php
    $meeting = $meeting_minutes->meeting;
    $start = \Carbon\Carbon::parse($meeting->datetime);
    $end = $start
        ->copy()
        ->addHours((int) \Time::complex_shape_hours($meeting->hours))
        ->addMinutes((int) \Time::complex_shape_minutes($meeting->hours));
    $committee = $meeting->committee->name;
    $secretary = trim($meeting_minutes->secretary->user->name . ' ' . $meeting_minutes->secretary->user->surname);
    $type = [1 => 'Ordinaria', 2 => 'Extraordinaria'][(int) $meeting->type] ?? '';
    $modality = [1 => 'Presencial', 2 => 'Telemática', 3 => 'Híbrida'][(int) $meeting->modality] ?? '';
    $attendees = collect()
        ->concat(
            $meeting->committee->coordinators->map(
                fn($coordinator) => ['Coordinación', $coordinator->user->surname, $coordinator->user->name],
            ),
        )
        ->concat(
            $meeting->committee->secretaries->map(
                fn($secretaryProfile) => [
                    'Secretaría',
                    $secretaryProfile->user->surname,
                    $secretaryProfile->user->name,
                ],
            ),
        )
        ->concat($meeting->users->map(fn($user) => ['Asistente', $user->surname, $user->name]))
        ->all();
    $points = $meeting_minutes->points;
@endphp
<x-pdf.document title="Acta de reunión" :subtitle="$meeting->title" :committee="'Comité de ' . $committee" :footer-text="'Evidentia · Acta del Comité de ' . $committee">
    <x-pdf.section :number="1" title="Datos de la reunión">
        <x-pdf.table key-value :rows="[
            'Fecha' => $start->format('d/m/Y'),
            'Hora de comienzo' => $start->format('H:i'),
            'Hora de fin' => $end->format('H:i'),
            'Lugar' => $meeting->place,
            'Convoca' => $secretary,
            'Comité' => $committee,
            'Tipo de reunión' => $type,
            'Modalidad' => $modality,
        ]" />
    </x-pdf.section>

    <x-pdf.section :number="2" title="Asistentes">
        <x-pdf.table :columns="['Rol', 'Apellidos', 'Nombre']" :rows="$attendees" />
    </x-pdf.section>

    <x-pdf.section :number="3" title="Orden del día">
        @if ($points->isEmpty())
            <p class="pdf-muted">Sin puntos</p>
        @else
            <ol>
                @foreach ($points as $point)
                    <li>{{ $point->title }}</li>
                @endforeach
            </ol>
        @endif
    </x-pdf.section>

    @if ($points->isNotEmpty())
        <x-pdf.section :number="4" title="Desarrollo de la reunión" page-break>
            @foreach ($points as $point)
                <x-pdf.section :number="'4.' . $loop->iteration" :title="$point->title . ($point->duration ? ' (' . $point->duration . ' min)' : '')" :level="3">
                    @if ($point->description)
                        <p>{{ $point->description }}</p>
                    @endif
                    @if ($point->agreements->isNotEmpty())
                        <x-pdf.table :columns="['Identificador', 'Acuerdo']" :rows="$point->agreements
                            ->map(fn($agreement) => [$agreement->identificator, $agreement->description])
                            ->all()" compact header-tone="plain" />
                    @endif
                </x-pdf.section>
            @endforeach
        </x-pdf.section>
    @endif

    <x-pdf.section :number="$points->isNotEmpty() ? 5 : 4" title="Cierre de la reunión">
        <p>Se levantó la sesión a las {{ $end->format('H:i') }} del {{ $start->day }} de
            {{ $start->locale('es')->monthName }}
            de {{ $start->year }} por {{ $secretary }}.</p>
        <div class="pdf-signature">
            <p>En conformidad con lo anterior y para que conste en acta, da fe</p>
            <p class="pdf-signature__name">{{ $secretary }}</p>
            <p class="pdf-muted">Secretaría del Comité de {{ $committee }}</p>
        </div>
    </x-pdf.section>
</x-pdf.document>
