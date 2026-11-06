{{-- Inbox of the notices of the signed-in person: all or only the unread ones, newest first --}}
@php
    $tabs = [
        ['label' => 'Todos', 'href' => route('notifications.index'), 'count' => $total, 'current' => !$onlyUnread],
        [
            'label' => 'Sin leer',
            'href' => route('notifications.index', ['unread' => 1]),
            'count' => $unread,
            'current' => $onlyUnread,
        ],
    ];
@endphp
<x-layout.app title="Avisos" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Avisos']]" :notifications="['count' => $unread, 'href' => route('notifications.index')]">
    <x-ui.page-header title="Avisos">
        <x-slot:actions>
            <form method="post" action="{{ route('notifications.read_all') }}">
                @csrf
                <x-ui.button type="submit" icon="check-check" :disabled="$unread === 0">Marcar todo como leído</x-ui.button>
            </form>
            <x-ui.button variant="ghost" icon="settings" :href="route('notifications.preferences')">Preferencias</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.tabs label="Filtrar avisos" :items="$tabs" />

    <x-ui.card title="Avisos" :level="2" padding="sm" id="inbox-title">
        <x-slot:extra>
            <span class="inline-flex items-center gap-2 text-sm text-ink-muted">Sin leer <x-ui.badge
                    variant="count">{{ $unread }}</x-ui.badge></span>
        </x-slot:extra>
        <x-ui.notification-list :items="$items" label="Lista de avisos" empty-text="No tienes avisos" />
        @if ($notifications->hasPages())
            <x-slot:footer>
                <x-ui.pagination :paginator="$notifications" label="Paginación de los avisos" />
            </x-slot:footer>
        @endif
    </x-ui.card>
</x-layout.app>
