{{-- Integrity of the evidences and of their proofs: the stored stamp against the one computed now --}}
@php
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $here = route($prefix . '.integrity');
    $showProofs = request('ver') === 'pruebas';
    $evidenceRows = $evidences->map(fn($evidence) => [$evidence, $evidence->integrity()]);
    $proofRows = $proofs->map(fn($proof) => [$proof, $proof->integrity()]);
    $alteredEvidences = $evidenceRows->reject(fn($row) => $row[1])->count();
    $alteredProofs = $proofRows->reject(fn($row) => $row[1])->count();
    $tabs = [
        ['label' => 'Evidencias', 'href' => $here, 'count' => $evidences->count(), 'current' => !$showProofs],
        ['label' => 'Pruebas', 'href' => $here . '?ver=pruebas', 'count' => $proofs->count(), 'current' => $showProofs],
    ];
    $owner = fn($user) => trim($user?->surname . ', ' . $user?->name, ', ');
@endphp
<x-layout.app title="Integridad" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Integridad']]">
    <x-ui.page-header title="Integridad" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-2">
        <x-ui.stat label="Evidencias alteradas" :value="(string) $alteredEvidences" :foot="'de ' . $evidences->count() . ' evidencias'" :tone="$alteredEvidences > 0 ? 'rejected' : 'accepted'" />
        <x-ui.stat label="Pruebas alteradas" :value="(string) $alteredProofs" :foot="'de ' . $proofs->count() . ' pruebas'" :tone="$alteredProofs > 0 ? 'rejected' : 'accepted'" />
    </div>

    @if ($showProofs)
        <x-ui.table caption="Integridad de las pruebas" :columns="[
            ['label' => 'Prueba', 'key' => 'file'],
            ['label' => 'Evidencia', 'key' => 'evidence'],
            ['label' => 'Propietario', 'key' => 'owner'],
            ['label' => 'Subida', 'key' => 'created_at'],
            ['label' => 'Integridad', 'key' => 'integrity'],
        ]" sortable :filterable="$proofs->isNotEmpty()" count-label="pruebas"
            filter-placeholder="Filtrar pruebas">
            <x-slot:toolbar>
                <x-ui.tabs label="Qué comprobar" :items="$tabs" />
            </x-slot:toolbar>
            @if ($proofs->isEmpty())
                <x-slot:empty>
                    <x-ui.empty icon="file-text" title="No hay pruebas" size="sm" :level="2" />
                </x-slot:empty>
            @endif
            @foreach ($proofRows as [$proof, $intact])
                <x-ui.table.row :flag="$intact ? null : 'Alterada'">
                    <x-ui.table.cell label="Prueba" primary>
                        <span class="font-semibold tracking-tight">{{ $proof->file?->name }}</span>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Evidencia">{{ $proof->evidence?->title }}</x-ui.table.cell>
                    <x-ui.table.cell label="Propietario" hide>{{ $owner($proof->evidence?->user) }}</x-ui.table.cell>
                    <x-ui.table.cell label="Subida" meta hide><x-ui.time :value="$proof->created_at" inline
                            short /></x-ui.table.cell>
                    <x-ui.table.cell label="Integridad" end :sort-value="$intact ? 1 : 0">
                        <x-ui.badge :tone="$intact ? 'accepted' : 'rejected'"
                            size="sm">{{ $intact ? 'Íntegra' : 'Alterada' }}</x-ui.badge>
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
        </x-ui.table>
    @else
        <x-ui.table caption="Integridad de las evidencias" :columns="[
            ['label' => 'Evidencia', 'key' => 'title'],
            ['label' => 'Propietario', 'key' => 'owner'],
            ['label' => 'Subida', 'key' => 'created_at'],
            ['label' => 'Integridad', 'key' => 'integrity'],
        ]" sortable :filterable="$evidences->isNotEmpty()"
            count-label="evidencias" filter-placeholder="Filtrar evidencias">
            <x-slot:toolbar>
                <x-ui.tabs label="Qué comprobar" :items="$tabs" />
            </x-slot:toolbar>
            @if ($evidences->isEmpty())
                <x-slot:empty>
                    <x-ui.empty icon="list-checks" title="No hay evidencias" size="sm" :level="2" />
                </x-slot:empty>
            @endif
            @foreach ($evidenceRows as [$evidence, $intact])
                @php
                    $href = route('profiles.view.evidence', [
                        'id_user' => $evidence->user_id,
                        'id_evidence' => $evidence->id,
                    ]);
                @endphp
                <x-ui.table.row :href="$href" :flag="$intact ? null : 'Alterada'">
                    <x-ui.table.cell label="Evidencia" primary>
                        <a href="{{ $href }}">{{ $evidence->title }}</a>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Propietario">
                        <a href="{{ route('profiles.view', ['id' => $evidence->user_id]) }}"
                            class="font-medium text-brand-ink">{{ $owner($evidence->user) }}</a>
                    </x-ui.table.cell>
                    <x-ui.table.cell label="Subida" meta hide><x-ui.time :value="$evidence->created_at" inline
                            short /></x-ui.table.cell>
                    <x-ui.table.cell label="Integridad" end :sort-value="$intact ? 1 : 0">
                        <x-ui.badge :tone="$intact ? 'accepted' : 'rejected'"
                            size="sm">{{ $intact ? 'Íntegra' : 'Alterada' }}</x-ui.badge>
                    </x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
        </x-ui.table>
    @endif
</x-layout.app>
