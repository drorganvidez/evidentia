{{-- Review of the evidences of the coordinator's committee, with one tab per state --}}
@php
    $committee = Auth::user()->coordinator->committee;
    $route = Route::currentRouteName();
    $closesAt = \Config::validate_evidences_timestamp();
    $open = !\Carbon\Carbon::now()->gt($closesAt);
    $tabs = [
        ['Todas', 'coordinator.evidence.list.all', $committee->evidencesNotDraft()->count()],
        ['Pendientes', 'coordinator.evidence.list.pending', $committee->evidencesPending()->count()],
        ['Aceptadas', 'coordinator.evidence.list.accepted', $committee->evidencesAccepted()->count()],
        ['Rechazadas', 'coordinator.evidence.list.rejected', $committee->evidencesRejected()->count()],
    ];
    $empty = match ($type ?? 'all') {
        'pending' => 'No hay evidencias pendientes',
        'accepted' => 'No hay evidencias aceptadas',
        'rejected' => 'No hay evidencias rechazadas',
        default => 'No hay evidencias',
    };
    $columns = [
        ['label' => 'Evidencia', 'key' => 'title'],
        ['label' => 'Estudiante', 'key' => 'student'],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Recibida', 'key' => 'created_at'],
        ['label' => 'Estado', 'key' => 'status'],
        ['label' => 'Acciones', 'srOnly' => true],
    ];
@endphp
<x-layout.app :title="'Revisar evidencias de ' . $committee->name" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Revisar evidencias']]">
    <x-ui.page-header title="Revisar evidencias">
        <x-slot:eyebrow>
            <x-ui.committee-chip :committee="$committee" />
        </x-slot:eyebrow>
        @if ($closesAt !== null)
            <x-slot:meta>
                <x-ui.deadline label="Validación de evidencias" variant="inline" verb="para validar" :closes-at="$closesAt" />
            </x-slot:meta>
        @endif
    </x-ui.page-header>

    <x-ui.table :caption="'Evidencias de ' . $committee->name" :columns="$columns" :filterable="$evidences->isNotEmpty()" count-label="evidencias"
        filter-placeholder="Filtrar evidencias">
        <x-slot:toolbar>
            <x-ui.tabs label="Filtrar evidencias por estado" :items="collect($tabs)
                ->map(
                    fn($tab) => [
                        'label' => $tab[0],
                        'href' => route($tab[1]),
                        'count' => $tab[2],
                        'current' => $route === $tab[1],
                    ],
                )
                ->all()" />
        </x-slot:toolbar>
        @if ($evidences->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="list-checks" :title="$empty" size="sm" :level="2" />
            </x-slot:empty>
        @endif
        @foreach ($evidences as $evidence)
            @php
                $href = route('coordinator.evidence.view', ['id' => $evidence->id]);
                // A coordinator never reviews their own evidence
                $reviewable = $open && $evidence->user_id != Auth::id();
            @endphp
            <x-ui.table.row :href="$href" :search="implode(' ', [
                $evidence->title,
                $evidence->user->surname,
                $evidence->user->name,
                $evidence->status_label,
            ])">
                <x-ui.table.cell label="Evidencia" primary>
                    <a href="{{ $href }}">{{ $evidence->title }}</a>
                </x-ui.table.cell>
                <x-ui.table.cell label="Estudiante">
                    <x-ui.person :name="$evidence->user->name" :surname="$evidence->user->surname" size="sm" reverse />
                </x-ui.table.cell>
                <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence->hours" /></x-ui.table.cell>
                <x-ui.table.cell label="Recibida" meta hide><x-ui.time :value="$evidence->created_at" inline
                        short /></x-ui.table.cell>
                <x-ui.table.cell label="Estado" end>
                    <x-ui.evidence-status :status="$evidence->status" :reason="$evidence->reasonRejection?->reason" size="sm" />
                </x-ui.table.cell>
                <x-ui.table.cell actions>
                    <div class="flex items-center justify-end gap-1">
                        @if ($reviewable && $evidence->status != 'ACCEPTED')
                            <form method="post"
                                action="{{ route('coordinator.evidence.accept', ['id' => $evidence->id]) }}">
                                @csrf
                                <x-ui.button type="submit" icon-only icon="circle-check" :label="'Aceptar ' . $evidence->title"
                                    variant="ghost" />
                            </form>
                        @endif
                        @if ($reviewable && $evidence->status != 'REJECTED')
                            <x-ui.dialog :id="'reject-' . $evidence->id" title="Rechazar la evidencia" :description="$evidence->title"
                                :trigger="'Rechazar ' . $evidence->title" trigger-variant="ghost" trigger-icon="circle-x" trigger-icon-only>
                                <form id="reject-form-{{ $evidence->id }}" method="post"
                                    action="{{ route('coordinator.evidence.reject') }}">
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
                        <x-ui.button icon-only icon="eye" :label="'Abrir ' . $evidence->title" :href="$href" variant="ghost"
                            dim />
                    </div>
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($evidences->isNotEmpty())
            <x-slot:footer>
                <span>{{ $evidences->count() }} {{ $evidences->count() === 1 ? 'evidencia' : 'evidencias' }} ·
                    <x-ui.hours :value="$evidences->sum('hours')" size="sm" class="text-ink" /></span>
            </x-slot:footer>
        @endif
    </x-ui.table>
</x-layout.app>
