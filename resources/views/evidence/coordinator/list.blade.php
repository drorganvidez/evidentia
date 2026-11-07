{{--
    A review queue with one tab per state, a search and pages (App\Http\Controllers\ReviewQueueController): the
    coordination of a committee or the presidency, whose rows come from every committee.
--}}
@use('App\Support\ListPage')
@php
    $presidency = $kind === \App\Services\ReviewQueue::PRESIDENCY;
    $name = $committee?->name ?? 'Presidencia';
    $route = Route::currentRouteName();
    $closesAt = \Config::validate_evidences_timestamp();
    $open = !\Carbon\Carbon::now()->gt($closesAt);
    // Every tab keeps the search and the rows per page, back on the first page
    $tabs = collect([
        ['Todas', $routes['all'], $counts['all']],
        ['Pendientes', $routes['pending'], $counts['pending']],
        ['Aceptadas', $routes['accepted'], $counts['accepted']],
        ['Rechazadas', $routes['rejected'], $counts['rejected']],
    ])
        ->map(
            fn($tab) => [
                'label' => $tab[0],
                'href' => ListPage::url([], route($tab[1])),
                'count' => $tab[2],
                'current' => $route === $tab[1],
            ],
        )
        ->all();
    $empty = match ($term !== null ? 'search' : $type) {
        'search' => 'Ninguna evidencia coincide con la búsqueda',
        'pending' => 'No hay evidencias pendientes',
        'accepted' => 'No hay evidencias aceptadas',
        'rejected' => 'No hay evidencias rechazadas',
        default => 'No hay evidencias',
    };
    $columns = $sort->columns(
        array_values(
            array_filter([
                ['label' => 'Evidencia', 'key' => 'title'],
                ['label' => 'Estudiante', 'key' => 'student'],
                $presidency ? ['label' => 'Comité', 'key' => 'committee'] : null,
                ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
                ['label' => 'Recibida', 'key' => 'created_at'],
                ['label' => 'Estado', 'key' => 'status'],
                ['label' => 'Acciones', 'srOnly' => true],
            ]),
        ),
    );
@endphp
<x-layout.app :title="'Revisar evidencias de ' . $name" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Revisar evidencias']]">
    <x-ui.page-header title="Revisar evidencias">
        <x-slot:eyebrow>
            @if ($committee !== null)
                <x-ui.committee-chip :committee="$committee" />
            @endif
        </x-slot:eyebrow>
        @if ($closesAt !== null)
            <x-slot:meta>
                <x-ui.deadline label="Validación de evidencias" variant="inline" verb="para validar" :closes-at="$closesAt" />
            </x-slot:meta>
        @endif
        <x-slot:actions>
            <x-ui.button icon="activity"
                :href="route('activity.index')">{{ $presidency ? 'Registro de actividad' : 'Actividad del comité' }}</x-ui.button>
            <x-ui.button icon="inbox" :href="route('messages.inbox')">Mensajes</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table :caption="'Evidencias de ' . $name" :columns="$columns" :paginator="$evidences" filterable count-label="evidencias"
        count-singular="evidencia" filter-placeholder="Buscar evidencias">
        <x-slot:toolbar>
            <x-ui.tabs label="Filtrar evidencias por estado" :items="$tabs" />
        </x-slot:toolbar>
        @if ($evidences->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="list-checks" :title="$empty" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($evidences as $evidence)
            @php
                $href = route($routes['view'], ['id' => $evidence->id]);
                // The queue never holds the evidences of the person (App\Services\ReviewQueue)
                $reviewable = $open && $evidence->user_id != Auth::id();
            @endphp
            <x-ui.table.row :href="$href">
                <x-ui.table.cell label="Evidencia" primary>
                    <a href="{{ $href }}">{{ $evidence->title }}</a>
                </x-ui.table.cell>
                <x-ui.table.cell label="Estudiante">
                    <x-ui.person :name="$evidence->user->name" :surname="$evidence->user->surname" size="sm" reverse />
                </x-ui.table.cell>
                @if ($presidency)
                    <x-ui.table.cell label="Comité" meta>
                        @if ($evidence->committee)
                            <x-ui.committee-chip :committee="$evidence->committee" />
                        @endif
                    </x-ui.table.cell>
                @endif
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Recibida" meta hide><x-ui.time :value="$evidence->created_at" inline
                        short /></x-ui.table.cell>
                <x-ui.table.cell label="Estado" end>
                    <x-ui.evidence-status :status="$evidence->status" :reason="$evidence->reasonRejection?->reason" size="sm" />
                </x-ui.table.cell>
                <x-ui.table.cell tools>
                    <div class="flex items-center justify-end gap-1">
                        @if ($reviewable && $evidence->status != 'ACCEPTED')
                            <form method="post" action="{{ route($routes['accept'], ['id' => $evidence->id]) }}">
                                @csrf
                                <x-ui.button type="submit" icon-only icon="circle-check" :label="'Aceptar ' . $evidence->title"
                                    variant="ghost" />
                            </form>
                        @endif
                        @if ($reviewable && $evidence->status != 'REJECTED')
                            <x-ui.dialog :id="'reject-' . $evidence->id" title="Rechazar la evidencia" :description="$evidence->title"
                                :trigger="'Rechazar ' . $evidence->title" trigger-variant="ghost" trigger-icon="circle-x" trigger-icon-only>
                                <form id="reject-form-{{ $evidence->id }}" method="post"
                                    action="{{ route($routes['reject']) }}">
                                    @csrf
                                    <input type="hidden" name="_id" value="{{ $evidence->id }}">
                                    <x-ui.field name="reasonrejection" :id="'reasonrejection-' . $evidence->id" label="Motivo del rechazo"
                                        type="textarea" :rows="4" :maxlength="1000" required />
                                </form>
                                <x-slot:footer>
                                    <x-ui.button x-on:click="close">Cancelar</x-ui.button>
                                    <x-ui.button variant="danger" type="submit" form="reject-form-{{ $evidence->id }}"
                                        icon="circle-x">Rechazar</x-ui.button>
                                </x-slot:footer>
                            </x-ui.dialog>
                        @endif
                        @if ($reviewable && $evidence->status === 'PENDING')
                            <x-ui.button icon-only icon="arrow-right-left" :label="'Mover a otro comité ' . $evidence->title" :href="route('activity.evidence', ['id' => $evidence->id, 'move' => 1])"
                                variant="ghost" />
                        @endif
                        <x-ui.button icon-only icon="eye" :label="'Abrir ' . $evidence->title" :href="$href" variant="ghost"
                            dim />
                    </div>
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($evidences->isNotEmpty())
            <x-slot:footer>
                <span>{{ $evidences->total() }} {{ $evidences->total() === 1 ? 'evidencia' : 'evidencias' }} ·
                    <x-ui.hours :value="$hours" size="sm" class="text-ink" /></span>
            </x-slot:footer>
        @endif
    </x-ui.table>
</x-layout.app>
