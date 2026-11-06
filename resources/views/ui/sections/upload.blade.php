{{-- Upload zones against the demo endpoints: several files with two already on the server, one spreadsheet, an error --}}
<section id="ui-upload" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-upload-title">
    <h2 id="ui-upload-title" class="text-2xl font-bold tracking-tight">Subida de archivos</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2">
        <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
            <h3 class="text-lg font-bold tracking-tight">Varios archivos</h3>
            <x-ui.upload :url="route('ui.demo.upload')" :revert-url="route('ui.demo.revert')" :remove-url="route('ui.demo.remove', 'NAME')" :max-size="1048576"
                hint="PDF, imágenes o ZIP · hasta 1 MB por archivo" :files="[
                    ['name' => 'acta-reunion-3.pdf', 'size' => 1258291, 'id' => 'demo-initial'],
                    'foto-photocall.jpg',
                ]" />
        </div>

        <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
            <h3 class="text-lg font-bold tracking-tight">Un solo archivo</h3>
            <x-ui.upload name="import_file" :url="route('ui.demo.upload')" :revert-url="route('ui.demo.revert')" accept=".xls,.xlsx" :multiple="false"
                label="Arrastra aquí la hoja de cálculo o pulsa para elegirla" hint="Excel (.xls o .xlsx)" />
        </div>

        <div class="grid grid-cols-[minmax(0,1fr)] gap-3">
            <h3 class="text-lg font-bold tracking-tight">Con error</h3>
            <x-ui.upload name="proof[]" :url="route('ui.demo.upload')" :revert-url="route('ui.demo.revert')" error="Adjunta al menos un archivo" />
        </div>
    </div>
</section>
