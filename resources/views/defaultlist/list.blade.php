{{-- Default lists of the secretary, reused to fill the attendees of minutes and the people of bonuses --}}
@php
    $columns = [
        ['label' => 'Lista', 'key' => 'name'],
        ['label' => 'Personas', 'key' => 'users', 'align' => 'end'],
        ['label' => 'Creada', 'key' => 'created_at', 'hide' => true, 'sorted' => 'desc'],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
@endphp
<x-layout.app title="Listas" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Listas']]">
    <x-ui.page-header title="Listas">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" :href="route('secretary.defaultlist.create')">Nueva lista</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Listas" :columns="$columns" sortable :filterable="$defaultlists->isNotEmpty()" count-label="listas"
        filter-placeholder="Filtrar listas">
        @forelse ($defaultlists as $defaultlist)
            @php
                $edit = route('secretary.defaultlist.edit', ['id' => $defaultlist->id]);
            @endphp
            <x-ui.table.row :href="$edit">
                <x-ui.table.cell label="Lista" primary><a
                        href="{{ $edit }}">{{ $defaultlist->name }}</a></x-ui.table.cell>
                <x-ui.table.cell label="Personas" numeric meta>{{ $defaultlist->users->count() }}</x-ui.table.cell>
                <x-ui.table.cell label="Creada" meta hide>
                    <x-ui.time :value="$defaultlist->created_at" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell actions>
                    <span class="flex justify-end gap-0.5">
                        <x-ui.button icon-only icon="pen-line" variant="ghost" size="sm" :label="'Editar ' . $defaultlist->name"
                            :href="$edit" />
                        @include('defaultlist.partials.remove', [
                            'defaultlist' => $defaultlist,
                            'dialogId' => 'remove-list-' . $defaultlist->id,
                            'iconOnly' => true,
                        ])
                    </span>
                </x-ui.table.cell>
            </x-ui.table.row>
        @empty
            <x-slot:empty>
                <x-ui.empty icon="users" title="Sin listas" size="sm" :level="2">
                    <x-slot:actions>
                        <x-ui.button variant="primary" icon="plus" :href="route('secretary.defaultlist.create')">Nueva
                            lista</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty>
            </x-slot:empty>
        @endforelse
    </x-ui.table>
</x-layout.app>
