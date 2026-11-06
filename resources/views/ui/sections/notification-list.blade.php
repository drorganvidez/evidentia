{{-- The notices tray: four notices, two of them unread and one without a link, and the empty state --}}
@php
    $now = now();
    $link = route('ui.index') . '#ui-notification-list';
    $items = [
        [
            'title' => 'Evidencia rechazada',
            'text' => 'Montaje del photocall · Carlos Navarro pide más pruebas',
            'at' => $now->copy()->subHours(2),
            'href' => $link,
            'unread' => true,
            'tone' => 'rejected',
        ],
        [
            'title' => 'Reunión convocada',
            'text' => 'Programa · jueves 12 a las 10:00 en el aula A2.12',
            'at' => $now->copy()->subHours(5),
            'href' => $link,
            'unread' => true,
            'tone' => 'notice',
        ],
        [
            'title' => 'Evidencia aceptada',
            'text' => 'Guion de la mesa redonda · 2,50 h computadas',
            'at' => $now->copy()->subDay(),
            'href' => $link,
            'unread' => false,
            'tone' => 'accepted',
        ],
        [
            'title' => 'Plazo de evidencias',
            'text' => 'Quedan 9 días para subir evidencias',
            'at' => $now->copy()->subDays(3),
            'href' => null,
            'unread' => false,
            'tone' => 'pending',
        ],
    ];
@endphp
<section id="ui-notification-list" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-notification-list-title">
    <h2 id="ui-notification-list-title" class="text-2xl font-bold tracking-tight">Bandeja de avisos</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-2">
        <x-ui.card title="Avisos" :level="3">
            <x-slot:extra>
                <span class="inline-flex items-center gap-2 text-sm text-ink-muted">
                    Sin leer
                    <x-ui.badge variant="count">2</x-ui.badge>
                </span>
            </x-slot:extra>
            <x-ui.notification-list :items="$items" />
        </x-ui.card>

        <x-ui.card title="Sin avisos" :level="3">
            <x-ui.notification-list :items="[]" />
        </x-ui.card>
    </div>
</section>
