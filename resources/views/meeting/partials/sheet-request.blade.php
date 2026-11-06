{{-- The meeting call tied to a signature sheet, as a card with its data --}}
<x-ui.card title="Convocatoria" :level="2" id="card-convocatoria">
    @if ($signature_sheet->meetingRequest)
        <x-ui.dl term-width="sm">
            <x-ui.dl.item term="Título">{{ $signature_sheet->meetingRequest->title }}</x-ui.dl.item>
            <x-ui.dl.item term="Fecha"><x-ui.time :value="$signature_sheet->meetingRequest->datetime" inline short /></x-ui.dl.item>
            <x-ui.dl.item term="Lugar">{{ $signature_sheet->meetingRequest->place }}</x-ui.dl.item>
        </x-ui.dl>
    @else
        <p class="text-ink-muted">Sin convocatoria</p>
    @endif
</x-ui.card>
