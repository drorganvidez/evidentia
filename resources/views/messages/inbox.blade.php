{{--
    Inbox of the people in charge: the threads addressed to their audiences or in which they wrote; the management can
    also read every thread. The tabs and the filters keep the order, the search and the rows per page.
--}}
@use('App\Support\ListPage')
@php
    $inbox = route('messages.inbox');
    $tabs = [
        [
            'label' => 'Abiertos',
            'href' => ListPage::url(['state' => 'open'], $inbox),
            'count' => $counts['open'],
            'current' => $state === 'open',
        ],
        [
            'label' => 'Resueltos',
            'href' => ListPage::url(['state' => 'resolved'], $inbox),
            'count' => $counts['resolved'],
            'current' => $state === 'resolved',
        ],
        [
            'label' => 'Todos',
            'href' => ListPage::url(['state' => 'all'], $inbox),
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

    <x-ui.filter-bar :action="$inbox" label="Filtros de la bandeja" :open="$active > 0" :active="$active"
        :reset-href="ListPage::url(['to' => null, 'committee' => null, 'q' => null], $inbox)" :keep="ListPage::query(['to', 'committee', 'q'])" id="inbox-filters">
        <x-ui.field name="to" label="Dirigido a" type="select" :options="[['value' => '', 'label' => 'Mis bandejas'], ...$audiences]" :value="$to" />
        <x-ui.field name="committee" label="Comité" type="select" :options="[['value' => '', 'label' => 'Todos'], ...$committees]" :value="$committee ?? ''" />
        <x-ui.field name="q" label="Texto" type="search" icon="search" placeholder="Asunto o remitente"
            :value="$q" />
    </x-ui.filter-bar>

    <x-ui.table caption="Mensajes recibidos" density="compact" :paginator="$threads"
        pagination-label="Paginación de los mensajes" :columns="$sort->columns([
            ['label' => 'Asunto', 'key' => 'subject'],
            ['label' => 'De', 'key' => 'author'],
            ['label' => 'Para', 'key' => 'audience', 'hide' => true],
            ['label' => 'Sobre', 'key' => 'about', 'hide' => true],
            ['label' => 'Estado', 'key' => 'state'],
            ['label' => 'Última respuesta', 'key' => 'last'],
            ['label' => 'Abrir', 'srOnly' => true],
        ])">
        <x-slot:toolbar>
            <x-ui.tabs label="Filtrar por estado" :items="$tabs" />
        </x-slot:toolbar>
        @include('messages.partials.rows', ['threads' => $threads, 'me' => $me, 'withAuthor' => true])
        @if ($threads->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="inbox" title="No hay mensajes" size="sm" />
            </x-slot:empty>
        @endif
    </x-ui.table>
</x-layout.app>
