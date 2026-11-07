{{-- Settings of the course: the event it runs for, the day and the hour each phase closes with its state, the hour caps --}}
@php
    $deadlines = [
        ['Subida de evidencias', 'upload_evidences', $configuration?->upload_evidences_timestamp],
        ['Validación de evidencias', 'validate_evidences', $configuration?->validate_evidences_timestamp],
        ['Registro de reuniones', 'meetings', $configuration?->meetings_timestamp],
        ['Registro de bonos', 'bonus', $configuration?->bonus_timestamp],
        ['Registro de eventos y asistencias', 'attendees', $configuration?->attendee_timestamp],
    ];
    $fields = [
        'event_name' => 'Nombre del evento',
        'event_edition' => 'Edición',
    ];
    $fields += collect($deadlines)
        ->flatMap(
            fn($deadline) => [
                $deadline[1] . '_date' => 'Día · ' . $deadline[0],
                $deadline[1] . '_time' => 'Hora · ' . $deadline[0],
            ],
        )
        ->all();
    $fields += [
        'max_evidences_hours' => 'Límite de horas de evidencias',
        'max_attendees_hours' => 'Límite de horas de asistencia',
    ];
    $limit = fn($value) => $value === null ? null : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
@endphp
<x-layout.app title="Configurar curso" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Configurar curso']]">
    <x-ui.page-header title="Configurar curso" />

    <x-ui.form :action="$route" id="config-form" :fields="$fields">
        <x-ui.card title="Evento" :level="2" id="card-event" class="mb-6">
            <p class="text-sm text-ink-muted">Aparece en la página de acceso y en la cabecera de todos los PDF, como
                actas, certificados y diplomas.</p>
            <div class="mt-4 grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
                <x-ui.field name="event_name" label="Nombre del evento" :value="$configuration?->event_name" maxlength="120"
                    hint="Por ejemplo, Jornadas InnoSoft Days" required />
                <x-ui.field name="event_edition" label="Edición" :value="$configuration?->event_edition" maxlength="20"
                    hint="Por ejemplo, 2026 o 2026-27" />
            </div>
        </x-ui.card>
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
            <x-ui.card title="Límites de horas" :level="2" id="card-limits">
                <p class="text-sm text-ink-muted">Tope de horas que cuentan para cada alumno. Déjalo vacío para que
                    cuenten todas.</p>
                <div class="mt-4 grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
                    <x-ui.field name="max_evidences_hours" label="Evidencias" type="number" suffix="h"
                        inputmode="decimal" min="0" max="9999" step="0.25" :value="$limit($configuration?->max_evidences_hours)"
                        hint="Horas de evidencias aceptadas" />
                    <x-ui.field name="max_attendees_hours" label="Asistencia" type="number" suffix="h"
                        inputmode="decimal" min="0" max="9999" step="0.25" :value="$limit($configuration?->max_attendees_hours)"
                        hint="Horas de eventos asistidos" />
                </div>
            </x-ui.card>
        </div>
        <x-slot:actions>
            <x-ui.button type="submit" variant="primary" icon="save">Guardar configuración</x-ui.button>
        </x-slot:actions>
    </x-ui.form>
    @php
        $open = collect(\App\Enums\Deadline::cases())->filter(
            fn($deadline) => filled($configuration?->{$deadline->column()}) &&
                \Carbon\Carbon::parse($configuration->{$deadline->column()})->isFuture(),
        );
    @endphp
    @if ($open->isNotEmpty())
        <x-ui.card title="Cerrar un plazo ahora" :level="2" class="mt-6">
            <p class="text-sm text-ink-muted">Si una fase ha terminado antes de lo previsto, ciérrala ya sin esperar a
                su fecha.
                Cerrar la validación cierra también la subida de evidencias.</p>
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($open as $deadline)
                    <x-ui.dialog :id="'close-' . $deadline->value" variant="alert" :title="'Cerrar ' . mb_strtolower($deadline->label())"
                        description="El plazo se cerrará en este momento. Podrás reabrirlo cambiando su fecha."
                        :trigger="$deadline->label()" trigger-icon="lock">
                        <x-slot:footer>
                            <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                            <form method="post" action="{{ $close }}">
                                @csrf
                                <input type="hidden" name="deadline" value="{{ $deadline->value }}">
                                <x-ui.button variant="primary" type="submit" icon="lock" block>Sí, cerrar
                                    ahora</x-ui.button>
                            </form>
                        </x-slot:footer>
                    </x-ui.dialog>
                @endforeach
            </div>
        </x-ui.card>
    @endif
</x-layout.app>
