{{-- Create or edit a meeting call: its data, the agenda (points_list) and, when created, its signature sheet --}}
@php
    $editing = isset($edit);
    $title = $editing ? $meeting_request->title : 'Nueva convocatoria';
    $datetime = $editing && $meeting_request->datetime ? \Carbon\Carbon::parse($meeting_request->datetime) : null;
    $types = [['value' => 1, 'label' => 'Ordinaria'], ['value' => 2, 'label' => 'Extraordinaria']];
    $modalities = [
        ['value' => 1, 'label' => 'Presencial'],
        ['value' => 2, 'label' => 'Telemática'],
        ['value' => 3, 'label' => 'Híbrida'],
    ];
    $breadcrumbs = [
        ['label' => 'Inicio', 'href' => route('home')],
        ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
        ['label' => 'Convocatorias', 'href' => route('secretary.meeting.manage.request.list')],
        ['label' => $editing ? 'Editar' : 'Nueva'],
    ];
    // The sheet is created by default; after a validation error the form keeps what was sent
    $createSheet = session()->hasOldInput() ? old('create_signature_sheet') === 'on' : true;
@endphp
<x-layout.app :title="$editing ? 'Editar convocatoria' : 'Nueva convocatoria'" :breadcrumbs="$breadcrumbs">
    <x-ui.page-header :title="$title">
        @if ($editing)
            <x-slot:actions>
                <x-ui.button icon="file-text" :href="route('secretary.meeting.manage.request.download', ['id' => $meeting_request->id])">Descargar
                    PDF</x-ui.button>
                @include('meeting.partials.remove-request', [
                    'meeting_request' => $meeting_request,
                    'dialogId' => 'remove-request',
                ])
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @include('meeting.partials.tabs')

    <x-ui.card>
        <x-ui.form :action="route('secretary.meeting.manage.request.' . ($editing ? 'save' : 'new'))" id="request-form" :fields="[
            'title' => 'Título',
            'date' => 'Fecha',
            'time' => 'Hora',
            'place' => 'Lugar',
            'type' => 'Tipo',
            'modality' => 'Modalidad',
            'points_list' => 'Orden del día',
        ]">
            @if ($editing)
                <input type="hidden" name="_id" value="{{ $meeting_request->id }}">
            @endif

            <x-ui.field name="title" label="Título" :value="$meeting_request->title ?? null" required />

            <div class="grid grid-cols-[minmax(0,1fr)] gap-5 md:grid-cols-2">
                <x-ui.field name="date" label="Fecha" type="date" :value="$datetime?->format('Y-m-d')" required />
                <x-ui.field name="time" label="Hora" type="time" :value="$datetime?->format('H:i')" required />
            </div>

            <x-ui.field name="place" label="Lugar" :value="$meeting_request->place ?? null" required />

            <div class="flex flex-wrap gap-x-8 gap-y-4">
                <x-ui.segmented name="type" legend="Tipo" :hide-legend="false" :options="$types" :value="$meeting_request->type ?? 1"
                    :error="$errors->first('type') ?: null" />
                <x-ui.segmented name="modality" legend="Modalidad" :hide-legend="false" :options="$modalities"
                    :value="$meeting_request->modality ?? 1" :error="$errors->first('modality') ?: null" />
            </div>

            <x-ui.agenda-editor :points="session('points', $points ?? [])" />

            @unless ($editing)
                <x-ui.choice type="switch" name="create_signature_sheet" value="on" label="Crear su hoja de firmas"
                    :checked="$createSheet" />
            @endunless

            <x-slot:actions>
                <x-ui.button type="submit" variant="primary"
                    icon="send">{{ $editing ? 'Guardar convocatoria' : 'Crear convocatoria' }}</x-ui.button>
                <x-ui.button variant="ghost" :href="route('secretary.meeting.manage.request.list')">Cancelar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
