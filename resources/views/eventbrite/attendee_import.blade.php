{{-- Attendances of one event loaded from a spreadsheet, for when Eventbrite is not at hand; every row counts as attended --}}
@php
    $text = fn($html) => trim(html_entity_decode(strip_tags(\Html::clean((string) $html)), ENT_QUOTES | ENT_HTML5));
    $eventOptions = $events
        ->map(
            fn($item) => [
                'value' => (string) $item->id,
                'label' =>
                    ($text($item->name) ?: 'Sin nombre') .
                    ($item->start_datetime
                        ? ' · ' . \Carbon\Carbon::parse($item->start_datetime)->format('d/m/Y H:i')
                        : ''),
            ],
        )
        ->values()
        ->all();
@endphp
<x-layout.app title="Importar asistencias" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Asistencias', 'href' => route('registercoordinator.attendee.list')],
    ['label' => 'Importar asistencias'],
]">
    <x-ui.page-header title="Importar asistencias" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2 lg:gap-8">
        <x-ui.card title="Hoja de cálculo" :level="2">
            <x-ui.form :action="route('attendee.import.save')" id="attendee-import-form" busy-label="Importando" :fields="['event_id' => 'Evento', 'files' => 'Hoja de cálculo']">
                <x-ui.field name="event_id" label="Evento" type="select" :options="$eventOptions" required />
                <x-ui.upload name="files[]" :url="route('xls.upload.process')" :revert-url="route('upload.revert')" :remove-url="route('xls.upload.remove', 'NAME')"
                    accept=".xls,.xlsx,.csv" :multiple="false" :max-files="1"
                    label="Arrastra aquí la hoja de cálculo o pulsa para elegirla"
                    hint="Excel (.xls o .xlsx) o CSV · una fila por cada persona que asistió" />
                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary" icon="import">Importar asistencias</x-ui.button>
                    <x-ui.button variant="ghost" :href="route('registercoordinator.attendee.list')">Cancelar</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </x-ui.card>

        <div class="grid content-start gap-4">
            <x-ui.table caption="Columnas de la hoja" show-caption :cards="false" density="compact"
                :columns="[['label' => 'uvus', 'key' => 'username'], ['label' => 'correo', 'key' => 'email']]">
                <x-ui.table.row>
                    <x-ui.table.cell><span class="font-mono">marpolpol</span></x-ui.table.cell>
                    <x-ui.table.cell><span class="font-mono">marpolpol@alum.us.es</span></x-ui.table.cell>
                </x-ui.table.row>
            </x-ui.table>
            <p class="text-sm text-ink-muted">Basta con una de las dos columnas. Cada persona encontrada queda como
                asistida al evento elegido, y las filas sin alumno en Evidentia se indican al terminar.</p>
        </div>
    </div>
</x-layout.app>
