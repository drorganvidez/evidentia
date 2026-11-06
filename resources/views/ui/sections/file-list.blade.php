{{-- Attached files: three types with their readable size, one without a link, and the empty state --}}
@php
    $link = route('ui.index') . '#ui-file-list';
    $files = [
        ['name' => 'informe-final.pdf', 'size' => 1258291, 'href' => $link, 'type' => 'application/pdf'],
        ['name' => 'captura-photocall.png', 'size' => 655360, 'href' => $link, 'type' => 'image/png'],
        [
            'name' => 'materiales-de-la-mesa-redonda-con-un-nombre-bastante-largo.zip',
            'size' => 3670016,
            'href' => $link,
            'type' => 'application/zip',
        ],
        ['name' => 'guion-presentacion.docx', 'size' => 90112, 'href' => null, 'type' => null],
    ];
@endphp
<section id="ui-file-list" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-file-list-title">
    <h2 id="ui-file-list-title" class="text-2xl font-bold tracking-tight">Archivos</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-2">
        <x-ui.card title="Pruebas" :level="3">
            <x-slot:extra>
                <span class="text-sm text-ink-muted">4 archivos</span>
            </x-slot:extra>
            <x-ui.file-list :files="$files" label="Pruebas de la evidencia" />
        </x-ui.card>

        <x-ui.card title="Sin pruebas" :level="3">
            <x-ui.file-list :files="[]" />
        </x-ui.card>
    </div>
</section>
