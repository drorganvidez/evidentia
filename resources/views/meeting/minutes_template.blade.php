{{--
    PDF of the minutes of a meeting. Rendered by dompdf when the minutes are saved and again on every download, so a
    change of this template reaches the minutes of past meetings too. The text of points and agreements goes through
    PlainText::rich: lists, bold and paragraphs of the textarea print as such.
--}}
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
    $coordinators = $meeting->committee->coordinators->pluck('user_id')->all();
    $secretaries = $meeting->committee->secretaries->pluck('user_id')->all();
    // The people marked as attendees, once each, by surname, with their role in the committee
    $attendees = $meeting->users
        ->unique('id')
        ->sortBy(fn($user) => Str::lower(Str::ascii($user->surname . ' ' . $user->name)))
        ->map(
            fn($user) => [
                'name' => trim($user->surname . ', ' . $user->name, ', '),
                'role' => in_array($user->id, $coordinators)
                    ? 'Coordinación'
                    : (in_array($user->id, $secretaries)
                        ? 'Secretaría'
                        : null),
            ],
        )
        ->values();
    $points = $meeting_minutes->points;
    $meta = [
        'Fecha' => $start->format('d/m/Y'),
        'Horario' => $start->format('H:i') . ' a ' . $end->format('H:i'),
        'Tipo' => $type,
        'Modalidad' => $modality,
        'Lugar' => $meeting->place,
        'Secretaría' => $secretary,
    ];
@endphp
<x-pdf.document title="Acta de reunión" :subtitle="$meeting->title" :committee="'Comité de ' . $committee" :footer-text="'Acta del Comité de ' . $committee . ' · ' . $start->format('d/m/Y')">
    <table class="pdf-meta">
        @foreach (array_chunk($meta, 4, true) as $row)
            <tr>
                @foreach ($row as $label => $value)
                    <td @if ($loop->parent->last && $loop->last && count($row) < 4) colspan="{{ 5 - count($row) }}" @endif>
                        <span class="pdf-meta__label">{{ $label }}</span>
                        <span class="pdf-meta__value">{{ filled($value) ? $value : '—' }}</span>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>

    <section class="pdf-section">
        <h2 class="pdf-section__title">Asistentes <span class="pdf-count">· {{ $attendees->count() }}</span></h2>
        @if ($attendees->isEmpty())
            <p class="pdf-muted">No consta ningún asistente.</p>
        @else
            <table class="pdf-people">
                @foreach ($attendees->chunk(2) as $pair)
                    <tr>
                        @foreach ($pair as $person)
                            <td>{{ $person['name'] }}@if ($person['role'])
                                    <span class="pdf-tag">{{ $person['role'] }}</span>
                                @endif
                            </td>
                        @endforeach
                        @if ($pair->count() < 2)
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        @endif
    </section>

    <section class="pdf-section">
        <h2 class="pdf-section__title">Orden del día</h2>
        @if ($points->isEmpty())
            <p class="pdf-muted">Sin puntos</p>
        @else
            <ol>
                @foreach ($points as $point)
                    <li>{{ $point->title }}</li>
                @endforeach
            </ol>
        @endif
    </section>

    @if ($points->isNotEmpty())
        <section class="pdf-section">
            <h2 class="pdf-section__title">Desarrollo de la reunión</h2>
            @foreach ($points as $point)
                <div class="pdf-point">
                    <table class="pdf-point__head">
                        <tr>
                            <td class="pdf-point__title"><span
                                    class="pdf-point__number">{{ $loop->iteration }}.</span>{{ $point->title }}</td>
                            @if ($point->duration)
                                <td class="pdf-point__time">{{ $point->duration }} min</td>
                            @endif
                        </tr>
                    </table>
                    @if (filled($point->description))
                        <div class="pdf-rich">{!! \App\View\PlainText::rich($point->description) !!}</div>
                    @endif
                    @if ($point->agreements->isNotEmpty())
                        <div class="pdf-agreements">
                            <p class="pdf-agreements__label">
                                {{ $point->agreements->count() === 1 ? 'Acuerdo' : 'Acuerdos' }}</p>
                            <table>
                                @foreach ($point->agreements as $agreement)
                                    <tr>
                                        <td class="pdf-agreements__id"><span
                                                class="pdf-pill">{{ $agreement->identificator }}</span></td>
                                        <td class="pdf-rich">{!! \App\View\PlainText::rich($agreement->description) !!}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    @endif

    <section class="pdf-section pdf-keep">
        <h2 class="pdf-section__title">Cierre</h2>
        <p>Se levanta la sesión a las {{ $end->format('H:i') }} del {{ $start->day }} de
            {{ $start->locale('es')->monthName }} de {{ $start->year }}.</p>
        <div class="pdf-signature">
            <p>En conformidad con lo anterior y para que conste en acta, da fe</p>
            <p class="pdf-signature__name">{{ $secretary }}</p>
            <p class="pdf-muted">Secretaría del Comité de {{ $committee }}</p>
        </div>
    </section>
</x-pdf.document>
