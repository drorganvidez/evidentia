{{-- People pickers: sixty people with three chosen and two presets inside a form, a single choice and an error --}}
@php
    // The same deterministic people as the demo endpoint
    $names = [
        'Juan',
        'María',
        'Carlos',
        'Lucía',
        'Pedro',
        'Ana',
        'Miguel',
        'Laura',
        'Javier',
        'Elena',
        'Sergio',
        'Marta',
        'David',
        'Paula',
        'Álvaro',
        'Carmen',
        'Pablo',
        'Sara',
        'Diego',
        'Nuria',
    ];
    $surnames = [
        'García',
        'Pérez',
        'López',
        'Martínez',
        'Sánchez',
        'Romero',
        'Navarro',
        'Torres',
        'Ruiz',
        'Díaz',
        'Moreno',
        'Jiménez',
        'Muñoz',
        'Álvarez',
        'Gómez',
    ];
    $people = array_map(
        fn(int $i): array => [
            'value' => $i,
            'label' => $surnames[$i % 15] . ' ' . $surnames[($i * 7) % 15] . ', ' . $names[$i % 20],
            'hint' => 'uvus' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
        ],
        range(1, 60),
    );
    $presets = [
        ['label' => 'Comité de Programa', 'values' => range(1, 12)],
        ['label' => 'Comité de Logística', 'values' => [3, 6, 9, 12, 15, 18, 21, 24]],
    ];
@endphp
<section id="ui-picker" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-picker-title">
    <h2 id="ui-picker-title" class="text-2xl font-bold tracking-tight">Selector de personas</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2">
        <x-ui.form method="get" :action="route('ui.index')" :summary="false" id="attendees-form" busy-label="Guardando">
            <x-ui.picker name="attendees[]" label="Asistentes" :options="$people" :selected="[2, 5, 9]" :presets="$presets"
                hint="Intro añade a la persona activa de la lista" />
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary">Guardar asistentes</x-ui.button>
            </x-slot:actions>
        </x-ui.form>

        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.picker name="student" label="Alumno" :options="$people" :max="1"
                placeholder="Buscar al alumno" />
            <x-ui.picker name="reviewers[]" label="Revisores" :options="array_slice($people, 0, 10)" error="Elige al menos a una persona" />
        </div>
    </div>
</section>
