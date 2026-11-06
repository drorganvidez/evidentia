{{-- Example minutes composed with the pdf components: served at /_ui/pdf and rendered by dompdf in the tests --}}
@php
    $meeting = [
        'title' => 'Reunión ordinaria 3 del Comité de Programa',
        'date' => '21/10/2026',
        'start' => '17:00',
        'end' => '18:45',
        'place' => 'Aula A2.11, Escuela Técnica Superior de Ingeniería Informática',
        'type' => 'Ordinaria',
        'modality' => 'Híbrida',
        'secretary' => 'Lucía Pérez Romero',
        'committee' => 'Comité de Programa',
    ];
    $attendees = [
        ['Coordinación', 'Pérez García', 'Juan'],
        ['Coordinación', 'Navarro Ruiz', 'Carlos'],
        ['Secretaría', 'Pérez Romero', 'Lucía'],
        ['Asistente', 'Martín Díaz', 'Elena'],
        ['Asistente', 'López Torres', 'Miguel'],
        ['Asistente', 'Sánchez Moreno', 'Paula'],
    ];
    $points = [
        [
            'title' => 'Lectura y aprobación del acta anterior',
            'duration' => 10,
            'description' => 'Se lee el acta de la reunión 2 y se aprueba por asentimiento sin cambios.',
            'agreements' => [
                [
                    'identificator' => 'ISD-2026-10-21-7',
                    'description' => 'Publicar el acta de la reunión 2 en la web del comité.',
                ],
            ],
        ],
        [
            'title' => 'Presupuesto del photocall',
            'duration' => 25,
            'description' =>
                'Se revisan las tres ofertas recibidas para la estructura y la lona del photocall del vestíbulo y se compara el plazo de entrega de cada una.',
            'agreements' => [
                [
                    'identificator' => 'ISD-2026-10-21-8',
                    'description' => 'Aceptar la oferta de Rotulación Sur por 480 euros.',
                ],
                [
                    'identificator' => 'ISD-2026-10-21-9',
                    'description' => 'Pedir una segunda lona de repuesto antes del 15 de noviembre.',
                ],
            ],
        ],
        [
            'title' => 'Calendario de montaje',
            'duration' => 15,
            'description' =>
                'Se fija el montaje para la tarde anterior a la inauguración, con dos turnos de voluntariado.',
            'agreements' => [],
        ],
    ];
@endphp
<x-pdf.document title="Acta de reunión" :subtitle="$meeting['title']" :committee="$meeting['committee']" :footer-text="'Evidentia · Jornadas InnoSoft Days ' . now()->year">
    <x-pdf.section :number="1" title="Datos de la reunión">
        <x-pdf.table key-value :rows="[
            'Fecha' => $meeting['date'],
            'Hora de comienzo' => $meeting['start'],
            'Hora de fin' => $meeting['end'],
            'Lugar' => $meeting['place'],
            'Convoca' => $meeting['secretary'],
            'Comité' => $meeting['committee'],
            'Tipo de reunión' => $meeting['type'],
            'Modalidad' => $meeting['modality'],
        ]" />
    </x-pdf.section>

    <x-pdf.section :number="2" title="Asistentes">
        <x-pdf.table :columns="['Rol', 'Apellidos', 'Nombre']" :rows="$attendees" />
    </x-pdf.section>

    <x-pdf.section :number="3" title="Orden del día">
        <ol>
            @foreach ($points as $point)
                <li>{{ $point['title'] }}</li>
            @endforeach
        </ol>
    </x-pdf.section>

    <x-pdf.section :number="4" title="Desarrollo de la reunión" page-break>
        @foreach ($points as $point)
            <x-pdf.section :number="'4.' . $loop->iteration" :title="$point['title'] . ($point['duration'] ? ' (' . $point['duration'] . ' min)' : '')" :level="3">
                <p>{{ $point['description'] }}</p>
                @if ($point['agreements'] !== [])
                    <x-pdf.table :columns="['Identificador', 'Acuerdo']" :rows="collect($point['agreements'])
                        ->map(fn(array $agreement): array => [$agreement['identificator'], $agreement['description']])
                        ->all()" compact header-tone="plain" />
                @endif
            </x-pdf.section>
        @endforeach
    </x-pdf.section>

    <x-pdf.section :number="5" title="Cierre de la reunión">
        <p>Se levantó la sesión a las {{ $meeting['end'] }} del 21 de octubre de 2026 por {{ $meeting['secretary'] }}.
        </p>
        <div class="pdf-signature">
            <p>En conformidad con lo anterior y para que conste en acta, da fe:</p>
            <p class="pdf-signature__name">{{ $meeting['secretary'] }}</p>
            <p class="pdf-muted">Secretaría del {{ $meeting['committee'] }}</p>
        </div>
    </x-pdf.section>
</x-pdf.document>
