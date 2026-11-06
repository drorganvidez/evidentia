{{-- Deadlines of the course: the day and the hour each phase closes, with its state --}}
@php
    $deadlines = [
        ['Subida de evidencias', 'upload_evidences', $configuration?->upload_evidences_timestamp],
        ['Validación de evidencias', 'validate_evidences', $configuration?->validate_evidences_timestamp],
        ['Registro de reuniones', 'meetings', $configuration?->meetings_timestamp],
        ['Registro de bonos', 'bonus', $configuration?->bonus_timestamp],
        ['Registro de eventos y asistencias', 'attendees', $configuration?->attendee_timestamp],
    ];
    $fields = collect($deadlines)
        ->flatMap(
            fn($deadline) => [
                $deadline[1] . '_date' => 'Día · ' . $deadline[0],
                $deadline[1] . '_time' => 'Hora · ' . $deadline[0],
            ],
        )
        ->all();
@endphp
<x-layout.app title="Configurar curso" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Configurar curso']]">
    <x-ui.page-header title="Configurar curso" />

    <x-ui.form :action="$route" id="config-form" :fields="$fields">
        <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2">
            @foreach ($deadlines as [$label, $key, $closesAt])
                @php
                    $date = filled($closesAt) ? \Carbon\Carbon::parse($closesAt) : null;
                    $titleId = 'card-' . Str::slug($label);
                @endphp
                <x-ui.card :title="$label" :level="2">
                    <x-slot:extra>
                        @if ($date === null)
                            <x-ui.badge tone="draft" size="sm">Sin plazo</x-ui.badge>
                        @elseif ($date->isFuture())
                            <x-ui.badge tone="accepted" size="sm">Abierto</x-ui.badge>
                        @else
                            <x-ui.badge tone="draft" size="sm">Cerrado</x-ui.badge>
                        @endif
                    </x-slot:extra>
                    <fieldset aria-labelledby="{{ $titleId }}"
                        class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
                        <x-ui.field :name="$key . '_date'" label="Día" type="date" :value="$date?->format('Y-m-d')" required />
                        <x-ui.field :name="$key . '_time'" label="Hora" type="time" :value="$date?->format('H:i')" required />
                    </fieldset>
                </x-ui.card>
            @endforeach
        </div>
        <x-slot:actions>
            <x-ui.button type="submit" variant="primary" icon="save">Guardar configuración</x-ui.button>
        </x-slot:actions>
    </x-ui.form>
</x-layout.app>
