{{-- Lists of bars: pending evidences by committee (hue dots and links) and hours by type (tone dots, hours format) --}}
@php
    $hue = fn(int $id): int => (($id * 7) % 12) * 30;
    $link = fn(int $id): string => route('ui.index', ['comite' => $id]) . '#ui-bar-list';
    $committees = [
        [7, 'Logística', 12],
        [3, 'Programa', 9],
        [8, 'Comunicación', 7],
        [2, 'Secretaría', 5],
        [6, 'Finanzas', 4],
        [4, 'Igualdad', 3],
        [5, 'Sostenibilidad', 3],
        [9, 'I+D+I', 2],
        [1, 'Presidencia', 1],
    ];
    $pending = [];
    foreach ($committees as [$id, $name, $count]) {
        $pending[] = ['label' => $name, 'value' => $count, 'href' => $link($id), 'hue' => $hue($id)];
    }
    $hours = [
        ['label' => 'Evidencias', 'value' => 4, 'tone' => 'accepted'],
        ['label' => 'Reuniones', 'value' => 2.5, 'tone' => 'notice'],
        ['label' => 'Asistencias', 'value' => 2.25, 'tone' => 'pending'],
        ['label' => 'Bonificaciones', 'value' => 1.75, 'tone' => 'draft'],
    ];
@endphp
<section id="ui-bar-list" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-bar-list-title">
    <h2 id="ui-bar-list-title" class="text-2xl font-bold tracking-tight">Listas de barras</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-2">
        <x-ui.card title="Evidencias pendientes por comité" :level="3">
            <x-slot:extra>
                <span class="text-sm text-ink-muted">46 pendientes</span>
            </x-slot:extra>
            <x-ui.bar-list label="Evidencias pendientes por comité" :items="$pending" />
        </x-ui.card>

        <x-ui.card title="Horas por tipo" :level="3">
            <x-slot:extra>
                <x-ui.hours :value="10.5" size="sm" />
            </x-slot:extra>
            <x-ui.bar-list label="Horas por tipo" :items="$hours" value-format="hours" :max="20" />
        </x-ui.card>
    </div>
</section>
