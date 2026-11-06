{{--
    Activity log: the whole of it for the management, the committee for coordinators and secretaries. Accesses and
    sent notices stay out unless asked for; the annotations of one request are grouped behind a link.
--}}
<x-layout.app :title="$title" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => $title]]">
    <x-ui.page-header :title="$title" :lede="$entries->total() . ' ' . ($entries->total() === 1 ? 'anotación' : 'anotaciones')">
        @if ($filters->request)
            <x-slot:actions>
                <x-ui.button icon="arrow-left" :href="route('activity.index')">Volver al registro</x-ui.button>
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.filter-bar :action="route('activity.index')" label="Filtros del registro" :open="$filters->active() > 0" :active="$filters->active()" :reset-href="route('activity.index')"
        :download="$canExport ? ['href' => route('activity.export'), 'label' => 'Descargar CSV'] : null" :keep="Arr::only(\App\Support\ListPage::query(), [\App\Support\ListPage::SIZE, \App\Support\ListSort::KEY, \App\Support\ListSort::DIR])" id="activity-filters">
        <x-ui.field name="type" label="Tipo" type="select" :options="[['value' => '', 'label' => 'Todos'], ...$types]" :value="$filters->type ?? ''" />
        <x-ui.picker name="user" label="Persona" :options="$people" :selected="array_filter([$filters->user])" :max="1"
            placeholder="Buscar persona" />
        <x-ui.field name="role" label="Rol" type="select" :options="[['value' => '', 'label' => 'Todos'], ...$roles]" :value="$filters->role ?? ''" />
        <x-ui.field name="committee" label="Comité" type="select" :options="[['value' => '', 'label' => 'Todos'], ...$committees]" :value="$committeeValue ?? ''"
            :disabled="$committeeLocked" />
        <x-ui.field name="from" label="Desde" type="date" :value="$filters->from" />
        <x-ui.field name="to" label="Hasta" type="date" :value="$filters->to" />
        <x-ui.choice name="flagged" label="Solo anotaciones señaladas" :checked="$filters->flagged" />
        <x-ui.choice name="all" label="Incluir accesos y avisos" :checked="$filters->all" />
    </x-ui.filter-bar>

    <x-ui.table caption="Registro de actividad" density="compact" :paginator="$entries"
        pagination-label="Paginación del registro" :columns="$sort->columns([
            ['label' => 'Cuándo', 'key' => 'at'],
            ['label' => 'Quién', 'key' => 'who'],
            ['label' => 'Acción', 'key' => 'action'],
            ['label' => 'Objeto', 'key' => 'subject', 'hide' => true],
            ['label' => 'Afecta a', 'key' => 'target', 'hide' => true],
            ['label' => 'Señales', 'key' => 'flags', 'align' => 'end'],
        ])">
        @foreach ($entries as $entry)
            <x-ui.table.row :flag="$presenter->flagsLabel($entry)">
                <x-ui.table.cell label="Cuándo" meta header>
                    <x-ui.time :value="$entry->created_at" inline short />
                </x-ui.table.cell>
                <x-ui.table.cell label="Quién" full>
                    @if ($entry->user_id)
                        <x-ui.person :name="$entry->user?->name ?? $entry->user_name" :surname="$entry->user?->surname" :role="$presenter->roleLine($entry)" size="sm"
                            :href="$access->profileHref($me, $entry->user_id)" />
                    @else
                        <span class="text-sm">Sistema</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Acción" primary>
                    <span class="font-semibold">{{ $presenter->label($entry) }}</span>
                    @if ($summary = $presenter->summary($entry))
                        <span class="mt-0.5 block text-sm text-ink-muted">{{ $summary }}</span>
                    @endif
                    @if (($siblings[$entry->request_id] ?? 1) > 1 && !$filters->request)
                        @php($more = '+' . ($siblings[$entry->request_id] - 1) . ' en la misma acción')
                        <a class="mt-0.5 block text-sm"
                            href="{{ route('activity.index', ['request' => $entry->request_id]) }}">{{ $more }}</a>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Objeto" hide>
                    @if ($url = $presenter->subjectUrl($entry, $me))
                        <a href="{{ $url }}">{{ $entry->subject_title }}</a>
                    @else
                        {{ $entry->subject_title }}
                    @endif
                    @if ($entry->committee_id)
                        <x-ui.committee-chip :id="$entry->committee_id" :name="$entry->committee?->name ?? $entry->committee_name" class="ml-2" />
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Afecta a" hide>
                    @if ($entry->target_user_id)
                        <x-ui.person :name="$entry->targetUser?->name ?? $entry->target_name" :surname="$entry->targetUser?->surname" size="sm" />
                    @elseif ($entry->targets_count > 0)
                        <span class="text-sm text-ink-muted">{{ $entry->targets_count }}
                            {{ $entry->targets_count === 1 ? 'persona' : 'personas' }}</span>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell label="Señales" end>
                    <span class="flex flex-wrap justify-end gap-1">
                        @foreach ($entry->flagList() as $flag)
                            <x-ui.badge :tone="$flag->tone()" size="sm">{{ $flag->label() }}</x-ui.badge>
                        @endforeach
                    </span>
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($entries->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="activity" title="Ninguna anotación con estos filtros" size="sm" />
            </x-slot:empty>
        @endif
    </x-ui.table>
</x-layout.app>
