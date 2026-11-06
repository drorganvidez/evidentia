{{--
    Annotations of the activity log as a timeline: the histories of an evidence, a minutes, a meeting request and a thread.
    @include('activity.partials.log', ['entries' => $entries, 'viewer' => $me, 'label' => 'Historial de la evidencia'])
    @include('activity.partials.log', ['entries' => $entries, 'viewer' => $me, 'label' => 'Actividad', 'subjects' => true])
    entries is a collection of ActivityLog with their user loaded; the empty state replaces the list. subjects adds the
    title of what each annotation is about, linked to its page when the viewer can open it (a log of many subjects).
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
                @if (!empty($subjects) && filled($entry->subject_title))
                    <span class="block">
                        @if ($url = $presenter->subjectUrl($entry, $viewer))
                            <a href="{{ $url }}"
                                class="font-medium text-brand-ink pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:min-w-11 pointer-coarse:items-center">{{ $entry->subject_title }}</a>
                        @else
                            {{ $entry->subject_title }}
                        @endif
                    </span>
                @endif
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
