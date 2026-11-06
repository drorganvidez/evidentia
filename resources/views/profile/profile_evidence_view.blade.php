{{-- An evidence of a student as the management reads it: its description, proofs, data and the seal of its integrity --}}
@php
    $fullName = trim($user->name . ' ' . $user->surname);
    $prefix = Auth::user()->hasRole('LECTURE') ? 'lecture' : 'president';
    $intact = $evidence->integrity();
    $reason = $evidence->status === 'REJECTED' ? $evidence->reasonRejection : null;
    $proofs = $evidence->proofs->map(
        fn($proof) => [
            'name' => $proof->file->name . '.' . $proof->file->type,
            'size' => $proof->file->size,
            'href' => route('proof.download', ['id' => $proof->id]),
            'type' => $proof->file->type,
        ],
    );
    $code = 'EV-' . str_pad((string) $evidence->id, 4, '0', STR_PAD_LEFT);
@endphp
<x-layout.app :title="$evidence->title" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Alumnos', 'href' => route($prefix . '.user.list')],
    ['label' => $fullName, 'href' => route('profiles.view', ['id' => $user->id])],
    ['label' => $evidence->title],
]">
    <x-ui.page-header :title="$evidence->title">
        <x-slot:eyebrow>
            <x-ui.evidence-status :status="$evidence->status" :reason="$reason?->reason" />
            <x-ui.committee-chip :committee="$evidence->committee" />
            <x-ui.badge variant="chip" mono>{{ $code }}</x-ui.badge>
        </x-slot:eyebrow>
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            @if ($reason !== null)
                <x-ui.alert tone="rejected" title="Motivo del rechazo" :quote="$reason->reason" />
            @endif

            <x-ui.card title="Descripción" :level="2" halo>
                <x-ui.rich-text :html="\App\View\PlainText::toHtml($evidence->description)" />
            </x-ui.card>

            <x-ui.card title="Pruebas" :level="2">
                <x-slot:extra>
                    <x-ui.badge variant="count">{{ $proofs->count() }}</x-ui.badge>
                </x-slot:extra>
                <x-ui.file-list :files="$proofs" label="Pruebas de la evidencia" />
            </x-ui.card>
        </div>

        <aside aria-label="Datos" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Datos" :level="2">
                <x-ui.dl>
                    <x-ui.dl.item term="Registro" mono>{{ $code }}</x-ui.dl.item>
                    <x-ui.dl.item term="Autor"><x-ui.person :name="$user->name" :surname="$user->surname" size="sm"
                            :href="route('profiles.view', ['id' => $user->id])" /></x-ui.dl.item>
                    <x-ui.dl.item term="Horas"><x-ui.hours :value="$evidence->hours" /></x-ui.dl.item>
                    <x-ui.dl.item term="Editada"><x-ui.time :value="$evidence->created_at" inline short /></x-ui.dl.item>
                    <x-ui.dl.item term="Integridad">
                        <x-ui.badge :tone="$intact ? 'accepted' : 'rejected'" size="sm"
                            :icon="$intact ? 'shield-check' : 'shield-alert'">{{ $intact ? 'Íntegra' : 'Alterada' }}</x-ui.badge>
                    </x-ui.dl.item>
                </x-ui.dl>
            </x-ui.card>

            @include('profile.partials.summary', ['user' => $user, 'hours' => $hours])
        </aside>
    </div>
</x-layout.app>
