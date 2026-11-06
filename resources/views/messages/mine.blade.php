{{-- Threads the signed-in person opened, by state --}}
@php
    $tabs = [
        [
            'label' => 'Abiertos',
            'href' => route('messages.mine', ['state' => 'open']),
            'count' => $counts['open'],
            'current' => $state === 'open',
        ],
        [
            'label' => 'Resueltos',
            'href' => route('messages.mine', ['state' => 'resolved']),
            'count' => $counts['resolved'],
            'current' => $state === 'resolved',
        ],
        [
            'label' => 'Todos',
            'href' => route('messages.mine'),
            'count' => $counts['all'],
            'current' => $state === 'all',
        ],
    ];
@endphp
<x-layout.app title="Mis mensajes" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Mis mensajes']]">
    <x-ui.page-header title="Mis mensajes" :lede="$unread > 0 ? $unread . ' sin leer' : null">
        <x-slot:actions>
            <x-ui.button variant="primary" icon="plus" :href="route('messages.create')">Escribir un mensaje</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table caption="Mensajes que has escrito" density="compact" :columns="[
        ['label' => 'Asunto', 'key' => 'subject'],
        ['label' => 'Para', 'key' => 'audience', 'hide' => true],
        ['label' => 'Sobre', 'key' => 'about', 'hide' => true],
        ['label' => 'Estado', 'key' => 'state'],
        ['label' => 'Última respuesta', 'key' => 'last'],
        ['label' => 'Abrir', 'srOnly' => true],
    ]">
        <x-slot:toolbar>
            <x-ui.tabs label="Filtrar por estado" :items="$tabs" />
        </x-slot:toolbar>
        @include('messages.partials.rows', ['threads' => $threads, 'me' => $me, 'withAuthor' => false])
        @if ($threads->isEmpty() && $counts['all'] > 0)
            <x-slot:empty>
                <x-ui.empty icon="message-square" title="No hay mensajes" size="sm" />
            </x-slot:empty>
        @elseif ($threads->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="message-square" title="Todavía no has escrito ningún mensaje" size="sm">
                    <x-slot:actions>
                        <x-ui.button variant="primary" icon="plus" :href="route('messages.create')">Escribir un
                            mensaje</x-ui.button>
                    </x-slot:actions>
                </x-ui.empty>
            </x-slot:empty>
        @endif
        @if ($threads->hasPages())
            <x-slot:footer>
                <x-ui.pagination :paginator="$threads" label="Paginación de mis mensajes" />
            </x-slot:footer>
        @endif
    </x-ui.table>
</x-layout.app>
