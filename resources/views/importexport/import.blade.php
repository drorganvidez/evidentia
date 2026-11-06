{{-- Mass import of the students of the course from a spreadsheet; every new person starts as a student --}}
<x-layout.app title="Importar alumnos" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Alumnos', 'href' => route('lecture.user.list')],
    ['label' => 'Importar alumnos'],
]">
    <x-ui.page-header title="Importar alumnos" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-2 lg:gap-8">
        <x-ui.card title="Hoja de cálculo" :level="2">
            <x-ui.form :action="$route" id="import-form" busy-label="Importando" :fields="['files' => 'Hoja de cálculo']">
                <x-ui.upload name="files[]" :url="route('xls.upload.process')" :revert-url="route('upload.revert')" :remove-url="route('xls.upload.remove', 'NAME')" accept=".xls,.xlsx"
                    :multiple="false" :max-files="1" label="Arrastra aquí la hoja de cálculo o pulsa para elegirla"
                    hint="Excel (.xls o .xlsx) · UVUS y correo únicos, sin celdas combinadas" />
                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary" icon="import">Importar alumnos</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </x-ui.card>

        <x-ui.table caption="Columnas del Excel" show-caption :cards="false" density="compact" :columns="[
            ['label' => 'apellidos', 'key' => 'surname'],
            ['label' => 'nombre', 'key' => 'name'],
            ['label' => 'uvus', 'key' => 'username'],
            ['label' => 'grupo', 'key' => 'group'],
            ['label' => 'email', 'key' => 'email'],
        ]">
            <x-ui.table.row>
                <x-ui.table.cell>Polo Polo</x-ui.table.cell>
                <x-ui.table.cell>Marco</x-ui.table.cell>
                <x-ui.table.cell><span class="font-mono">marpolpol</span></x-ui.table.cell>
                <x-ui.table.cell>Grupo 1</x-ui.table.cell>
                <x-ui.table.cell><span class="font-mono">polo@mail.com</span></x-ui.table.cell>
            </x-ui.table.row>
        </x-ui.table>
    </div>
</x-layout.app>
