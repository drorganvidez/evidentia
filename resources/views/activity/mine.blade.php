{{--
    Activity of the signed-in person: what they did, what others did on their things and their accesses. The IP is
    only shown for their own failed accesses.
--}}
@php
    $tabs = [
        ['label' => 'Todo', 'href' => route('activity.mine'), 'count' => $counts['all'], 'current' => $scope === 'all'],
        [
            'label' => 'Lo que he hecho',
            'href' => route('activity.mine', ['scope' => 'own']),
            'count' => $counts['own'],
            'current' => $scope === 'own',
        ],
        [
            'label' => 'Sobre mis cosas',
            'href' => route('activity.mine', ['scope' => 'about']),
            'count' => $counts['about'],
            'current' => $scope === 'about',
        ],
        [
            'label' => 'Accesos',
            'href' => route('activity.mine', ['scope' => 'access']),
            'count' => $counts['access'],
            'current' => $scope === 'access',
        ],
    ];
@endphp
<x-layout.app title="Mi actividad" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Mi actividad']]">
    <x-ui.page-header title="Mi actividad" />

    <x-ui.tabs class="min-w-0" label="Ámbito de mi actividad" :items="$tabs" />

    <x-ui.card title="Historial" :level="2" padding="sm">
        @if ($entries->isEmpty())
            <x-ui.empty icon="history" title="Todavía no hay actividad" size="sm" />
        @else
            <x-ui.timeline label="Mi actividad">
                @foreach ($entries as $entry)
                    <x-ui.timeline.item :tone="$presenter->tone($entry)" :title="$presenter->label($entry)" :at="$entry->created_at">
                        @php
                            $parts = array_filter([
                                $entry->user_id !== $me->id ? 'por ' . $presenter->actorName($entry) : null,
                                $presenter->roleLine($entry),
                            ]);
                            $url = $entry->subject_title ? $presenter->subjectUrl($entry, $me) : null;
                            $summary = $presenter->summary($entry);
                        @endphp
                        @if ($parts !== [])
                            <span class="block text-ink-muted">{{ implode(' · ', $parts) }}</span>
                        @endif
                        @if ($entry->subject_title)
                            <span class="block">
                                @if ($url)
                                    <a href="{{ $url }}">{{ $entry->subject_title }}</a>
                                @else
                                    {{ $entry->subject_title }}
                                @endif
                            </span>
                        @endif
                        @if ($summary)
                            <span class="block">{{ $summary }}</span>
                        @endif
                        @if ($entry->action === 'login_failed' && $entry->ip)
                            <span class="block text-ink-muted">Desde {{ $entry->ip }}</span>
                        @endif
                        @if ($entry->flagList() !== [])
                            <span class="mt-1 flex flex-wrap gap-1.5">
                                @foreach ($entry->flagList() as $flag)
                                    <x-ui.badge :tone="$flag->tone()" size="sm">{{ $flag->label() }}</x-ui.badge>
                                @endforeach
                            </span>
                        @endif
                    </x-ui.timeline.item>
                @endforeach
            </x-ui.timeline>
        @endif
        @if ($entries->hasPages())
            <x-slot:footer>
                <x-ui.pagination :paginator="$entries" label="Paginación de mi actividad" />
            </x-slot:footer>
        @endif
    </x-ui.card>
</x-layout.app>
