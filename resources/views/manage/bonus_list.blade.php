{{-- The hour bonuses of every committee, for the lecturer and the president, newest first, with the people of each. Read only. --}}
@php
    $columns = [
        ['label' => 'Bono', 'key' => 'reason'],
        ['label' => 'Comité', 'key' => 'committee'],
        ['label' => 'Personas', 'key' => 'users', 'align' => 'end'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Creado', 'key' => 'created_at'],
    ];
    $names = fn($users) => $users
        ->sortBy(fn($user) => mb_strtolower($user->surname . ' ' . $user->name))
        ->map(fn($user) => trim($user->surname . ', ' . $user->name, ', '))
        ->implode('; ');
@endphp
<x-layout.app title="Bonos" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Bonos']]">
    <x-ui.page-header title="Bonos" />

    <x-ui.table caption="Bonos de todos los comités" :columns="$columns" :filterable="$bonus->isNotEmpty()" count-label="bonos"
        filter-placeholder="Filtrar bonos">
        @if ($bonus->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="puzzle" title="No hay bonos" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($bonus as $bono)
            @php
                $people = $names($bono->users);
            @endphp
            <x-ui.table.row :search="implode(' ', [$bono->reason, $bono->committee?->name, $people])">
                <x-ui.table.cell label="Bono" primary>
                    <span class="font-semibold tracking-tight">{{ $bono->reason }}</span>
                    @if ($people !== '')
                        <span class="mt-0.5 block max-w-[48ch] text-sm text-ink-muted">{{ $people }}</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Comité">
                    @if ($bono->committee)
                        <x-ui.committee-chip :committee="$bono->committee" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Personas" numeric meta>{{ $bono->users->count() }}</x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$bono->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Creado" meta><x-ui.time :value="$bono->created_at" inline short /></x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($bonus->isNotEmpty())
            <x-slot:footer>
                <span>{{ $bonus->count() }} {{ $bonus->count() === 1 ? 'bono' : 'bonos' }} ·
                    <x-ui.hours :value="$bonus->sum(fn($bono) => $bono->hours * $bono->users->count())" size="sm" class="text-ink" />
                    repartidas</span>
            </x-slot:footer>
        @endif
    </x-ui.table>
</x-layout.app>
