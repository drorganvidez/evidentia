{{-- Spreadsheet with the hours of every person of the course, by the blocks chosen --}}
@php
    $blocks = [
        ['evidences', 'Evidencias'],
        ['meetings', 'Reuniones'],
        ['events', 'Asistencia'],
        ['bonus', 'Bonos de horas'],
    ];
@endphp
<x-layout.app title="Exportar" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Exportar']]">
    <x-ui.page-header title="Exportar" />

    <x-ui.card title="Horas del curso" :level="2" class="max-w-2xl">
        <x-ui.form :action="$route" id="export-form" download busy-label="Preparando">
            <fieldset class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-3">
                <legend class="text-sm font-semibold">Qué incluir</legend>
                <div class="grid gap-2 md:grid-cols-2">
                    @foreach ($blocks as [$name, $label])
                        <x-ui.choice :name="$name" value="on" :label="$label" :checked="true" />
                    @endforeach
                </div>
            </fieldset>
            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="download">Descargar Excel</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>

    <x-ui.card title="Copia de las evidencias" :level="2" class="max-w-2xl" id="archive-title">
        <p class="text-ink-muted">Un archivo ZIP con la lista de todas las evidencias enviadas a revisión (autor,
            comité,
            texto, horas y estado) y sus pruebas, por alumno. Los borradores no se incluyen.</p>
        <div class="mt-4">
            <x-ui.button variant="primary" icon="download" :href="$archive" id="archive-download">Descargar
                copia</x-ui.button>
        </div>
    </x-ui.card>
</x-layout.app>
