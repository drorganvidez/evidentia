{{--
    Inbox of the people in charge: the threads addressed to their audiences or in which they wrote; the lecturers can
    also read every thread.
--}}
@php
    $tabs = [
        [
            'label' => 'Abiertos',
            'href' => route('messages.inbox', $query + ['state' => 'open']),
            'count' => $counts['open'],
            'current' => $state === 'open',
        ],
        [
            'label' => 'Resueltos',
            'href' => route('messages.inbox', $query + ['state' => 'resolved']),
            'count' => $counts['resolved'],
            'current' => $state === 'resolved',
        ],
        [
            'label' => 'Todos',
            'href' => route('messages.inbox', $query + ['state' => 'all']),
            'current' => $state === 'all',
        ],
    ];
@endphp
<x-layout.app title="Mensajes" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Mensajes']]">
    <x-ui.page-header title="Mensajes" :lede="$unread . ' sin leer'">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" :href="route('messages.create')">Nuevo mensaje</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.filter-bar :action="route('messages.inbox')" label="Filtros de la bandeja" :open="$active > 0" :active="$active"
        :reset-href="route('messages.inbox')" id="inbox-filters">
        <input type="hidden" name="state" value="{{ $state }}">
        <x-ui.field name="to" label="Dirigido a" type="select" :options="[['value' => '', 'label' => 'Mis bandejas'], ...$audiences]" :value="$to" />
        <x-ui.field name="committee" label="Comité" type="select" :options="[['value' => '', 'label' => 'Todos'], ...$committees]" :value="$committee ?? ''" />
        <x-ui.field name="q" label="Texto" type="search" icon="search" placeholder="Asunto o remitente"
            :value="$q" />
    </x-ui.filter-bar>

    <x-ui.table caption="Mensajes recibidos" density="compact" :columns="[
        ['label' => 'Asunto', 'key' => 'subject'],
        ['label' => 'De', 'key' => 'author'],
        ['label' => 'Para', 'key' => 'audience', 'hide' => true],
        ['label' => 'Sobre', 'key' => 'about', 'hide' => true],
        ['label' => 'Estado', 'key' => 'state'],
        ['label' => 'Última respuesta', 'key' => 'last'],
        ['label' => 'Abrir', 'srOnly' => true],
    ]">
        <x-slot:toolbar>
            <x-ui.tabs label="Filtrar por estado" :items="$tabs" />
        </x-slot:toolbar>
        @include('messages.partials.rows', ['threads' => $threads, 'me' => $me, 'withAuthor' => true])
        @if ($threads->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="inbox" title="No hay mensajes" size="sm" />
            </x-slot:empty>
        @endif
        @if ($threads->hasPages())
            <x-slot:footer>
                <x-ui.pagination :paginator="$threads" label="Paginación de los mensajes" />
            </x-slot:footer>
        @endif
    </x-ui.table>
</x-layout.app>
