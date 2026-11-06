{{--
    Section of the record: the annotations of the activity log where the person acts or is affected, in four views
    (?ambito=), a page at a time
--}}
@php
    $scopeTabs = collect(\App\View\StudentRecord::SCOPES)
        ->map(
            fn(string $label, string $key) => [
                'label' => $label,
                'href' =>
                    route(
                        'profiles.view',
                        array_filter([
                            'id' => $user->id,
                            'tab' => 'actividad',
                            'ambito' => $key === 'todo' ? null : $key,
                        ]),
                    ) . '#secciones',
                'count' => $scopeCounts[$key],
                'current' => $scope === $key,
            ],
        )
        ->values()
        ->all();
@endphp
<x-ui.card title="Actividad" :level="2" id="activity-title" padding="sm">
    <div class="grid gap-4">
        <x-ui.tabs class="min-w-0" label="Ámbito de la actividad" :items="$scopeTabs" />
        @include('activity.partials.log', [
            'entries' => $activity,
            'viewer' => auth()->user(),
            'label' => 'Actividad de ' . $fullName,
            'subjects' => true,
        ])
    </div>
    @if ($activity->isNotEmpty())
        <x-slot:footer class="flex flex-col items-start gap-2 md:flex-row md:items-center md:justify-between">
            <p class="text-sm text-ink-muted">{{ $plural($activity->total(), 'anotación', 'anotaciones') }}</p>
            @if ($activity->hasPages())
                <x-ui.pagination :paginator="$activity" label="Paginación de la actividad" />
            @endif
        </x-slot:footer>
    @endif
</x-ui.card>
