{{--
    Annotations of the activity log as a timeline: the histories of an evidence, a minutes, a meeting request and a thread.
    @include('activity.partials.log', ['entries' => $entries, 'viewer' => $me, 'label' => 'Historial de la evidencia'])
    entries is a collection of ActivityLog with their user loaded; the empty state replaces the list.
--}}
@inject('presenter', 'App\Services\Activity\ActivityPresenter')
@if ($entries->isEmpty())
    <x-ui.empty icon="history" title="Sin anotaciones" size="sm" />
@else
    <x-ui.timeline :label="$label ?? 'Historial'">
        @foreach ($entries as $entry)
            <x-ui.timeline.item :tone="$presenter->tone($entry)" :title="$presenter->label($entry)" :at="$entry->created_at">
                <span class="block text-ink-muted">
                    @if ($entry->user_id === null)
                        Sistema
                    @else
                        por {{ $presenter->actorName($entry) }} {{ $presenter->roleLine($entry) }}
                    @endif
                </span>
                @if ($version = $presenter->version($entry))
                    <span class="block">Versión {{ $version }}</span>
                @endif
                @if ($summary = $presenter->summary($entry, withVersion: false))
                    <span class="block">{{ $summary }}</span>
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
