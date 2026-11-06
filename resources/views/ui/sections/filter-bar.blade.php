{{-- Filter bars: the activity log filters, open with two active and a CSV download, and a closed one for the inbox --}}
@php
    $names = ['Juan', 'María', 'Carlos', 'Lucía', 'Pedro', 'Ana', 'Miguel', 'Laura', 'Javier', 'Elena'];
    $surnames = ['García', 'Pérez', 'López', 'Martínez', 'Sánchez', 'Romero', 'Navarro', 'Torres', 'Ruiz', 'Díaz'];
    $people = array_map(
        fn(int $i): array => [
            'value' => $i,
            'label' => $surnames[$i % 10] . ' ' . $surnames[($i * 3) % 10] . ', ' . $names[$i % 10],
            'hint' => 'uvus' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
        ],
        range(1, 20),
    );
    $types = ['' => 'Todos los tipos', 'evidence' => 'Evidencias', 'meeting' => 'Reuniones', 'user' => 'Usuarios'];
    $committees = [
        '' => 'Todos los comités',
        1 => 'Programa',
        2 => 'Logística',
        3 => 'Comunicación',
        4 => 'Patrocinio',
    ];
    $roles = ['' => 'Todos', 'student' => 'Estudiantes', 'coordinator' => 'Coordinadores'];
@endphp
<section id="ui-filter-bar" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-filter-bar-title">
    <h2 id="ui-filter-bar-title" class="text-2xl font-bold tracking-tight">Filtros</h2>

    <x-ui.filter-bar :action="route('ui.index')" open :active="2" :reset-href="route('ui.index') . '#ui-filter-bar'" :download="['href' => route('ui.index'), 'label' => 'Descargar CSV']" id="activity-filters">
        <x-ui.field name="activity_type" label="Tipo" type="select" :options="$types" value="evidence" />
        <x-ui.picker name="actor" label="Persona" :options="$people" :max="1" placeholder="Buscar persona" />
        <fieldset class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-1.5">
            <legend class="text-sm font-semibold">Rol</legend>
            <div class="flex flex-wrap gap-x-4">
                @foreach ($roles as $value => $label)
                    <x-ui.choice type="radio" name="activity_role" :value="$value" :label="$label"
                        :checked="$value === ''" />
                @endforeach
            </div>
        </fieldset>
        <x-ui.field name="filter_committee" label="Comité" type="select" :options="$committees" />
        <fieldset class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-1.5 md:col-span-2">
            <legend class="text-sm font-semibold">Fechas</legend>
            <div class="grid grid-cols-2 gap-4">
                <x-ui.field name="from" label="Desde" type="date" value="2026-10-01" />
                <x-ui.field name="to" label="Hasta" type="date" />
            </div>
        </fieldset>
    </x-ui.filter-bar>

    <x-ui.filter-bar :action="route('ui.index')" label="Filtros de la bandeja" id="inbox-filters">
        <x-ui.field name="inbox_state" label="Estado" type="select" :options="['' => 'Todos', 'open' => 'Abiertos', 'resolved' => 'Resueltos']" />
        <x-ui.field name="inbox_q" label="Texto" type="search" icon="search" placeholder="Asunto o remitente"
            autocomplete="off" />
    </x-ui.filter-bar>
</section>
