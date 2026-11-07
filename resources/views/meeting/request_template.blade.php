{{--
    PDF of a meeting call. Rendered by dompdf when the call is saved and again on every download, so a change of this
    template reaches past calls too.
--}}
@php
    $datetime = \Carbon\Carbon::parse($meeting_request->datetime);
    $committee = $meeting_request->committee->name;
    $secretary = trim($meeting_request->secretary->user->name . ' ' . $meeting_request->secretary->user->surname);
    $type = [1 => 'Ordinaria', 2 => 'Extraordinaria'][(int) $meeting_request->type] ?? '';
    $modality = [1 => 'Presencial', 2 => 'Telemática', 3 => 'Híbrida'][(int) $meeting_request->modality] ?? '';
    $diaryPoints = $meeting_request->diary?->diaryPoints ?? collect();
@endphp
<x-pdf.document title="Convocatoria de reunión" :subtitle="$meeting_request->title" :committee="'Comité de ' . $committee" :footer-text="'Convocatoria del Comité de ' . $committee . ' · ' . $datetime->format('d/m/Y')">
    <table class="pdf-meta">
        <tr>
            <td>
                <span class="pdf-meta__label">Fecha</span>
                <span class="pdf-meta__value">{{ $datetime->format('d/m/Y') }}</span>
            </td>
            <td>
                <span class="pdf-meta__label">Hora</span>
                <span class="pdf-meta__value">{{ $datetime->format('H:i') }}</span>
            </td>
            <td>
                <span class="pdf-meta__label">Tipo</span>
                <span class="pdf-meta__value">{{ $type ?: '—' }}</span>
            </td>
            <td>
                <span class="pdf-meta__label">Modalidad</span>
                <span class="pdf-meta__value">{{ $modality ?: '—' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="pdf-meta__label">Lugar</span>
                <span
                    class="pdf-meta__value">{{ filled($meeting_request->place) ? $meeting_request->place : '—' }}</span>
            </td>
        </tr>
    </table>

    <section class="pdf-section">
        <p>Se le convoca a la reunión {{ Str::lower($type) }} del Comité de {{ $committee }}, en modalidad
            {{ Str::lower($modality) }}, el {{ $datetime->day }} de {{ $datetime->locale('es')->monthName }} de
            {{ $datetime->year }} a las {{ $datetime->format('H:i') }}, para tratar los asuntos del orden del día.</p>
    </section>

    <section class="pdf-section">
        <h2 class="pdf-section__title">Orden del día</h2>
        @if ($diaryPoints->isEmpty())
            <p class="pdf-muted">Sin puntos</p>
        @else
            <ol>
                @foreach ($diaryPoints as $point)
                    <li>{{ $point->point }}</li>
                @endforeach
            </ol>
        @endif
    </section>

    <div class="pdf-signature">
        <p>Le saluda cordialmente</p>
        <p class="pdf-signature__name">{{ $secretary }}</p>
        <p class="pdf-muted">Secretaría del Comité de {{ $committee }}</p>
    </div>
</x-pdf.document>
