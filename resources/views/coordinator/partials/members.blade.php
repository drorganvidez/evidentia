{{--
    Members of a committee (App\View\CommitteePanel::members) with their evidences by state, the hours the committee
    credits them, their meetings and their latest activity; for «Mi comité» and for the page of a committee.
    @include('coordinator.partials.members', ['committee' => $committee, 'members' => $members, 'hours' => $total])
    profiles links each member to their profile, for the management.
--}}
@php
    $profiles ??= false;
    $plural = fn(int $count, string $one, string $many): string => $count . ' ' . ($count === 1 ? $one : $many);
    $columns = [
        ['label' => 'Miembro', 'key' => 'member', 'sorted' => 'asc'],
        ['label' => 'Evidencias', 'key' => 'evidences'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Reuniones', 'key' => 'meetings', 'align' => 'end'],
        ['label' => 'Última actividad', 'key' => 'last'],
    ];
@endphp
<x-ui.table id="members" :caption="'Miembros de ' . $committee->name" show-caption :columns="$columns" sortable :filterable="$members->isNotEmpty()"
    count-label="miembros" filter-placeholder="Filtrar miembros">
    @if ($members->isEmpty())
        <x-slot:empty>
            <x-ui.empty icon="users" title="Sin miembros todavía" size="sm" :level="3" />
        </x-slot:empty>
    @endif
    @foreach ($members as $member)
        <x-ui.table.row :search="implode(
            ' ',
            array_filter([
                $member['surname'],
                $member['name'],
                $member['username'],
                $member['email'],
                $member['pending'] > 0 ? 'pendiente' : null,
                $member['rejected'] > 0 ? 'rechazada' : null,
            ]),
        )">
            <x-ui.table.cell label="Miembro" primary header>
                <x-ui.person :name="$member['name']" :surname="$member['surname']" :role="$member['username']" size="sm" reverse
                    :href="$profiles ? route('profiles.view', ['id' => $member['id']]) : null" />
            </x-ui.table.cell>
            <x-ui.table.cell label="Evidencias" full :sort-value="sprintf('%05d%05d%05d', $member['accepted'], $member['pending'], $member['rejected'])">
                @if ($member['accepted'] + $member['pending'] + $member['rejected'] === 0)
                    <span class="text-sm text-ink-muted">Sin evidencias</span>
                @else
                    <span class="flex flex-wrap items-center gap-1.5">
                        @if ($member['accepted'] > 0)
                            <x-ui.badge tone="accepted"
                                size="sm">{{ $plural($member['accepted'], 'aceptada', 'aceptadas') }}</x-ui.badge>
                        @endif
                        @if ($member['pending'] > 0)
                            <x-ui.badge tone="pending"
                                size="sm">{{ $plural($member['pending'], 'pendiente', 'pendientes') }}</x-ui.badge>
                        @endif
                        @if ($member['rejected'] > 0)
                            <x-ui.badge tone="rejected"
                                size="sm">{{ $plural($member['rejected'], 'rechazada', 'rechazadas') }}</x-ui.badge>
                        @endif
                    </span>
                @endif
            </x-ui.table.cell>
            <x-ui.table.cell label="Horas" numeric><x-ui.hours :value="$member['hours']" /></x-ui.table.cell>
            <x-ui.table.cell label="Reuniones" numeric meta hide :sort-value="$member['meetings']">{{ $member['meetings'] }}<span
                    class="md:hidden">
                    {{ $member['meetings'] === 1 ? 'reunión' : 'reuniones' }}</span></x-ui.table.cell>
            <x-ui.table.cell label="Última actividad" meta hide>
                @if ($member['last'])
                    <x-ui.time :value="$member['last']" format="relative" />
                @else
                    —
                @endif
            </x-ui.table.cell>
        </x-ui.table.row>
    @endforeach
    @if ($members->isNotEmpty())
        <x-slot:footer>
            <span>{{ $plural($members->count(), 'miembro', 'miembros') }} ·
                <x-ui.hours :value="$hours" size="sm" class="text-ink" /></span>
        </x-slot:footer>
    @endif
</x-ui.table>
