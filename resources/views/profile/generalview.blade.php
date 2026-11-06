{{-- Profile of a student for the management: the summary and every evidence they sent, which open in their detail --}}
@php
    $fullName = trim($user->name . ' ' . $user->surname);
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $evidences = $user->evidencesNotDraft()->sortByDesc('created_at');
    $columns = [
        ['label' => 'Evidencia', 'key' => 'title'],
        ['label' => 'Comité', 'key' => 'committee', 'hide' => true],
        ['label' => 'Horas', 'key' => 'hours', 'align' => 'end'],
        ['label' => 'Estado', 'key' => 'status'],
        ['label' => 'Creada', 'key' => 'created'],
    ];
@endphp
<x-layout.app :title="$fullName" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Alumnos', 'href' => route($prefix . '.user.list')],
    ['label' => $fullName],
]">
    <x-ui.page-header :title="$fullName" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <x-ui.table caption="Evidencias enviadas" :columns="$columns" show-caption>
            @foreach ($evidences as $evidence)
                @php
                    $href = route('profiles.view.evidence', ['id_user' => $user->id, 'id_evidence' => $evidence->id]);
                @endphp
                <x-ui.table.row :href="$href">
                    <x-ui.table.cell label="Evidencia" primary><a
                            href="{{ $href }}">{{ $evidence->title }}</a></x-ui.table.cell>
                    <x-ui.table.cell label="Comité" hide><x-ui.committee-chip :committee="$evidence->committee" /></x-ui.table.cell>
                    <x-ui.table.cell label="Horas" numeric meta><x-ui.hours :value="$evidence->hours" /></x-ui.table.cell>
                    <x-ui.table.cell label="Estado" end><x-ui.evidence-status :status="$evidence->status"
                            size="sm" /></x-ui.table.cell>
                    <x-ui.table.cell label="Creada" meta><x-ui.time :value="$evidence->created_at" inline short /></x-ui.table.cell>
                </x-ui.table.row>
            @endforeach
            @if ($evidences->isEmpty())
                <x-slot:empty>
                    <x-ui.empty icon="file-text" title="No ha enviado evidencias" size="sm" />
                </x-slot:empty>
            @endif
        </x-ui.table>

        <aside aria-label="Resumen" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            @include('profile.partials.summary', ['user' => $user, 'hours' => $hours])
        </aside>
    </div>
</x-layout.app>
