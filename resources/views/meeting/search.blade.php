{{--
    Search over the minutes of every committee: agreements by words or identifier (all of one meeting with ?acta=)
    and meetings by words, filtered by committee and dates, each result linked to its minutes.
--}}
@use('App\Support\ListPage')
@php
    $search = route('meeting.search');
    $active = count(array_filter($filters));
    $reset = ListPage::url(['comite' => null, 'desde' => null, 'hasta' => null], $search);
    $tabs = [
        [
            'label' => 'Acuerdos',
            'href' => ListPage::url(['tab' => null, 'acta' => null], $search),
            'current' => $tab === 'acuerdos',
        ],
        [
            'label' => 'Reuniones',
            'href' => ListPage::url(['tab' => 'reuniones', 'acta' => null], $search),
            'current' => $tab === 'reuniones',
        ],
    ];
@endphp
<x-layout.app title="Buscar acuerdos" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Buscar acuerdos']]">
    <x-ui.page-header title="Buscar acuerdos"
        lede="Busca en las actas de todos los comités por palabras clave o por el identificador de un acuerdo." />

    <x-ui.filter-bar :action="$search" label="Filtros de acuerdos" :open="$active > 0" :active="$active" :reset-href="$reset"
        :keep="ListPage::query(['comite', 'desde', 'hasta'])" id="agreement-filters">
        <x-ui.field name="comite" label="Comité" type="select" :options="[['value' => '', 'label' => 'Todos'], ...$committees]" :value="(string) ($filters['comite'] ?? '')" />
        <x-ui.field name="desde" label="Celebrada desde" type="date" :value="$filters['desde']?->format('Y-m-d')" />
        <x-ui.field name="hasta" label="Celebrada hasta" type="date" :value="$filters['hasta']?->format('Y-m-d')" />
    </x-ui.filter-bar>

    @if ($tab === 'acuerdos')
        <x-ui.table caption="Acuerdos" :columns="[['label' => 'Acuerdo'], ['label' => 'Reunión'], ['label' => 'Celebrada']]" :paginator="$agreements" filterable
            filter-placeholder="Palabras o identificador" count-label="acuerdos" count-singular="acuerdo">
            <x-slot:toolbar>
                <x-ui.tabs label="Buscar en" :items="$tabs" />
                @if ($minutes)
                    <span class="inline-flex flex-wrap items-center gap-2 text-sm text-ink-muted">
                        Acuerdos de <strong class="text-ink">{{ $minutes->meeting?->title }}</strong>
                        <x-ui.button size="sm" variant="ghost" icon="x" :href="ListPage::url(['acta' => null], $search)">Quitar
                            filtro</x-ui.button>
                    </span>
                @endif
            </x-slot:toolbar>
            @foreach ($agreements as $agreement)
                @php
                    $meeting = $agreement->point?->meetingMinutes?->meeting;
                    $href = route('activity.minutes', ['id' => $agreement->point->meeting_minutes_id]);
                @endphp
                <x-ui.table.row :href="$href">
                    <x-ui.table.cell label="Acuerdo" primary>
                        <span class="flex flex-wrap items-start gap-2">
                            <x-ui.badge variant="chip" mono>{{ $agreement->identificator }}</x-ui.badge>
                            <a href="{{ $href }}"
                                class="min-w-0 flex-1">{{ \Illuminate\Support\Str::limit(\App\View\PlainText::fromHtml($agreement->description), 220) }}</a>
                        </span>
                        <span class="mt-0.5 block text-sm text-ink-muted">{{ $agreement->point?->title }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Reunión">
                        <span class="block">{{ $meeting?->title }}</span>
                        @if ($meeting?->committee)
                            <x-ui.committee-chip :committee="$meeting->committee" class="mt-1" />
                        @endif
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Celebrada" meta>
                        <x-ui.time :value="$meeting?->datetime" inline short />
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
            @if ($agreements->isEmpty())
                <x-slot:empty>
                    <x-ui.empty icon="search" :title="$term || $active > 0 ? 'Ningún acuerdo coincide' : 'Aún no hay acuerdos'" size="sm" :level="2" />
                </x-slot:empty>
            @endif
        </x-ui.table>
    @else
        <x-ui.table caption="Reuniones" :columns="[
            ['label' => 'Reunión'],
            ['label' => 'Comité', 'hide' => true],
            ['label' => 'Acuerdos', 'align' => 'end'],
            ['label' => 'Celebrada'],
            ['label' => 'Acciones', 'srOnly' => true],
        ]" :paginator="$meetings" filterable
            filter-placeholder="Palabras clave" count-label="reuniones" count-singular="reunión">
            <x-slot:toolbar>
                <x-ui.tabs label="Buscar en" :items="$tabs" />
            </x-slot:toolbar>
            @foreach ($meetings as $meeting)
                @php($href = route('activity.minutes', ['id' => $meeting->meetingMinutes->id]))
                <x-ui.table.row :href="$href">
                    <x-ui.table.cell label="Reunión" primary>
                        <a href="{{ $href }}">{{ $meeting->title }}</a>
                        <span class="mt-0.5 block text-sm text-ink-muted">{{ $meeting->place }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Comité" hide>
                        @if ($meeting->committee)
                            <x-ui.committee-chip :committee="$meeting->committee" />
                        @endif
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Acuerdos" numeric
                        meta>{{ (int) $meeting->agreements_count }}</x-ui.table.cell>
                    <x-ui.table.cell label="Celebrada" meta>
                        <x-ui.time :value="$meeting->datetime" inline short />
                    </x-ui.table.cell>
                    <x-ui.table.cell tools>
                        @if ($meeting->agreements_count > 0)
                            <x-ui.button size="sm" variant="ghost" icon="list" icon-only :label="'Ver los acuerdos de ' . $meeting->title"
                                :href="route('meeting.search', ['acta' => $meeting->meetingMinutes->id])" />
                        @endif
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
            @if ($meetings->isEmpty())
                <x-slot:empty>
                    <x-ui.empty icon="search" :title="$term || $active > 0 ? 'Ninguna reunión coincide' : 'Aún no hay actas'" size="sm" :level="2" />
                </x-slot:empty>
            @endif
        </x-ui.table>
    @endif
</x-layout.app>
