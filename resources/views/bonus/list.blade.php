{{-- Hour bonuses of the committee of the secretary; while the bonus deadline is open each row opens its edition --}}
@php
    $bonusOpen = !\Carbon\Carbon::now()->gt(\Config::bonus_timestamp() ?? now());
    $columns = [
        ['label' => 'Bono', 'key' => 'reason'],
        ['label' => 'Personas', 'key' => 'users', 'align' => 'end'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
    ];
    if ($bonusOpen) {
        $columns[] = ['label' => 'Acciones', 'srOnly' => true];
    }
@endphp
<x-layout.app title="Bonos" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Bonos']]">
    <x-ui.page-header title="Bonos">
        @if (\Config::bonus_timestamp())
            <x-slot:meta>
                <x-ui.deadline label="Registro de bonos" variant="inline" :closes-at="\Carbon\Carbon::parse(\Config::bonus_timestamp())" />
            </x-slot:meta>
        @endif
        @if ($bonusOpen)
            <x-slot:actions>
                <x-ui.button variant="primary" icon="plus" :href="route('secretary.bonus.create')">Nuevo bono</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.table caption="Bonos" :columns="$columns" :filterable="$bonus->isNotEmpty()" count-label="bonos" filter-placeholder="Filtrar bonos">
        @forelse ($bonus as $bono)
            @php
                $edit = $bonusOpen ? route('secretary.bonus.edit', ['id' => $bono->id]) : null;
            @endphp
            <x-ui.table.row :href="$edit">
                <x-ui.table.cell label="Bono" primary>
                    @if ($edit)
                        <a href="{{ $edit }}">{{ $bono->reason }}</a>
                    @else
                        {{ $bono->reason }}
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Personas" numeric meta>{{ $bono->users->count() }}</x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$bono->hours" /></x-ui.table.cell>
                @if ($bonusOpen)
                    <x-ui.table.cell actions>
                        <span class="flex justify-end gap-0.5">
                            <x-ui.button icon-only icon="pen-line" variant="ghost" size="sm" :label="'Editar ' . $bono->reason"
                                :href="$edit" />
                            @include('bonus.partials.remove', [
                                'bono' => $bono,
                                'dialogId' => 'remove-bonus-' . $bono->id,
                                'iconOnly' => true,
                            ])
                        </span>
                    </x-ui.table.cell>
                @endif
            </x-ui.table.row>
        @empty
            <x-slot:empty>
                <x-ui.empty icon="puzzle" title="Sin bonos" size="sm" :level="2" />
            </x-slot:empty>
        @endforelse
    </x-ui.table>
</x-layout.app>
