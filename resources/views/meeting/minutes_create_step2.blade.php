{{-- New minutes, step 2: the signature sheet whose signers become the attendees --}}
@php
    $ownSheet = $meeting_request?->signatureSheet;
    $sheetOptions = [
        ['value' => '', 'label' => 'Sin hoja de firmas'],
        ...$signature_sheets
            ->map(fn($signature_sheet) => ['value' => $signature_sheet->id, 'label' => $signature_sheet->title])
            ->values()
            ->all(),
    ];
@endphp
<x-layout.app title="Nueva acta" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Actas', 'href' => route('secretary.meeting.manage.minutes.list')],
    ['label' => 'Nueva'],
]">
    <x-ui.page-header title="Nueva acta" />

    @include('meeting.partials.tabs')

    @include('meeting.partials.minutes-steps', ['step' => 2])

    <x-ui.card>
        <x-ui.form :action="route('secretary.meeting.manage.minutes.create.step2_p')" id="minutes-step-form" :fields="['signature_sheet' => 'Hoja de firmas']">
            <input type="hidden" name="meeting_request" value="{{ $meeting_request->id ?? '' }}">

            <x-ui.dl term-width="sm">
                <x-ui.dl.item term="Convocatoria">{{ $meeting_request->title ?? 'Sin convocatoria' }}</x-ui.dl.item>
                @if ($ownSheet)
                    <x-ui.dl.item term="Hoja de firmas">{{ $ownSheet->title }}</x-ui.dl.item>
                @endif
            </x-ui.dl>

            @if ($ownSheet)
                <input type="hidden" name="signature_sheet" value="{{ $ownSheet->id }}">
            @else
                <x-ui.field name="signature_sheet" label="Hoja de firmas" type="select" :options="$sheetOptions" />
            @endif

            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="arrow-right" icon-end>Siguiente</x-ui.button>
                <x-ui.button variant="ghost" icon="arrow-left" :href="route('secretary.meeting.manage.minutes.create.step1')">Anterior</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
