{{-- The default lists of people of every secretary for the lecturer and the president, newest first. Read only. --}}
@php
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $columns = [
        ['label' => 'Lista', 'key' => 'name'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Secretaría', 'key' => 'secretary', 'hide' => true],
        ['label' => 'Personas', 'key' => 'users', 'align' => 'end'],
        ['label' => 'Creada', 'key' => 'created_at', 'hide' => true, 'sorted' => 'desc'],
    ];
    $names = fn($users) => $users
        ->sortBy(fn($user) => mb_strtolower($user->surname . ' ' . $user->name))
        ->map(fn($user) => trim($user->surname . ', ' . $user->name, ', '))
        ->implode('; ');
@endphp
<x-layout.app title="Listas" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route($prefix . '.meeting.list')],
    ['label' => 'Listas'],
]">
    <x-ui.page-header title="Listas" />

    @include('manage.partials.meeting-tabs')

    <x-ui.table caption="Listas de todos los comités" :columns="$columns" sortable :filterable="$default_lists->isNotEmpty()" count-label="listas"
        filter-placeholder="Filtrar listas">
        @if ($default_lists->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="users" title="No hay listas" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($default_lists as $default_list)
            @php
                $people = $names($default_list->users);
                $committee = $default_list->secretary?->committee;
                $secretary = $default_list->secretary?->user;
            @endphp
            <x-ui.table.row :search="implode(' ', [
                $default_list->name,
                $committee?->name,
                $secretary?->surname,
                $secretary?->name,
                $people,
            ])">
                <x-ui.table.cell label="Lista" primary>
                    <span class="font-semibold tracking-tight">{{ $default_list->name }}</span>
                    @if ($people !== '')
                        <span class="mt-0.5 block max-w-[48ch] text-sm text-ink-muted">{{ $people }}</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Comité">
                    @if ($committee)
                        <x-ui.committee-chip :committee="$committee" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Secretaría" hide>
                    @if ($secretary)
                        <x-ui.person :name="$secretary->name" :surname="$secretary->surname" size="sm" reverse :href="route('profiles.view', ['id' => $secretary->id])" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Personas" numeric meta>{{ $default_list->users->count() }}<span
                        class="md:hidden">{{ $default_list->users->count() === 1 ? ' persona' : ' personas' }}</span></x-ui.table.cell>
                <x-ui.table.cell label="Creada" meta hide>
                    <x-ui.time :value="$default_list->created_at" inline short />
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
    </x-ui.table>
</x-layout.app>
