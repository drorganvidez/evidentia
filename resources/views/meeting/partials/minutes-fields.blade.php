{{--
    Fields shared by new and edited minutes: the meeting data, the attendees (users[] with the default lists) and the
    points with their agreements (points_json). $source is the meeting or the meeting call that fills the data,
    $hours the stored hours of the meeting, $selected the attendees and $points the starting points.
--}}
@php
    $datetime = $source?->datetime ? \Carbon\Carbon::parse($source->datetime) : null;
    $types = [['value' => 1, 'label' => 'Ordinaria'], ['value' => 2, 'label' => 'Extraordinaria']];
    $modalities = [
        ['value' => 1, 'label' => 'Presencial'],
        ['value' => 2, 'label' => 'Telemática'],
        ['value' => 3, 'label' => 'Híbrida'],
    ];
    $people = $users
        ->map(
            fn($user) => [
                'value' => $user->id,
                'label' => trim($user->surname) . ', ' . trim($user->name),
                'hint' => $user->username,
            ],
        )
        ->values()
        ->all();
    $presets = $defaultlists
        ->map(fn($list) => ['label' => trim($list->name), 'values' => $list->users->modelKeys()])
        ->values()
        ->all();
@endphp
<x-ui.card title="Reunión" :level="2" id="card-reunion">
    <div class="grid grid-cols-[minmax(0,1fr)] gap-5">
        <x-ui.field name="title" label="Título" :value="$source?->title" required />

        <div class="grid grid-cols-[minmax(0,1fr)] gap-5 md:grid-cols-2">
            <x-ui.field name="date" label="Fecha" type="date" :value="$datetime?->format('Y-m-d')" required />
            <x-ui.field name="time" label="Hora" type="time" :value="$datetime?->format('H:i')" required />
        </div>

        <x-ui.field name="place" label="Lugar" :value="$source?->place" required />

        <div class="flex flex-wrap gap-x-8 gap-y-4">
            <x-ui.segmented name="type" legend="Tipo" :hide-legend="false" :options="$types" :value="$source?->type ?? 1"
                :error="$errors->first('type') ?: null" />
            <x-ui.segmented name="modality" legend="Modalidad" :hide-legend="false" :options="$modalities" :value="$source?->modality ?? 1"
                :error="$errors->first('modality') ?: null" />
        </div>

        <fieldset class="grid gap-1.5">
            <legend class="mb-1.5 text-sm font-semibold">Duración</legend>
            <div class="grid grid-cols-2 gap-5">
                <x-ui.field name="hours" label="Horas" type="number" :value="\Time::complex_shape_hours($hours ?? '')" min="0"
                    max="99" step="0.01" inputmode="decimal" suffix="h" />
                <x-ui.field name="minutes" label="Minutos" type="number" :value="\Time::complex_shape_minutes($hours ?? '')" min="0"
                    max="60" step="1" inputmode="numeric" suffix="min" />
            </div>
        </fieldset>
    </div>
</x-ui.card>

<x-ui.card title="Asistencia" :level="2" id="card-asistencia">
    <x-ui.picker name="users[]" label="Asistentes" :options="$people" :selected="$selected" :presets="$presets" />
</x-ui.card>

<x-ui.card title="Desarrollo" :level="2" id="card-desarrollo">
    <x-ui.minutes-editor :points="session('points', $points)" label="Puntos y acuerdos" />
</x-ui.card>
