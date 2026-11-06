{{-- A signature sheet: the people who signed, its meeting call and the link to sign --}}
@php
    $signUrl = url('/sign/' . $signature_sheet->random_identifier);
    $signers = $signature_sheet->users->sortByDesc(fn($user) => $user->pivot->created_at);
    $columns = [
        ['label' => 'Persona', 'key' => 'name'],
        ['label' => 'UVUS', 'key' => 'username'],
        ['label' => 'Firmada', 'key' => 'signed_at'],
    ];
@endphp
<x-layout.app :title="$signature_sheet->title" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Reuniones', 'href' => route('secretary.meeting.manage')],
    ['label' => 'Hojas de firmas', 'href' => route('secretary.meeting.manage.signaturesheet.list')],
    ['label' => $signature_sheet->title],
]">
    <x-ui.page-header :title="$signature_sheet->title">
        <x-slot:eyebrow>
            <x-ui.badge variant="chip" mono>{{ $signature_sheet->random_identifier }}</x-ui.badge>
        </x-slot:eyebrow>
        <x-slot:actions>
            <x-ui.button icon="pen-line" :href="route('secretary.meeting.manage.signaturesheet.edit', ['id' => $signature_sheet->id])">Editar</x-ui.button>
            @include('meeting.partials.remove-sheet', [
                'signature_sheet' => $signature_sheet,
                'dialogId' => 'remove-sheet',
            ])
        </x-slot:actions>
    </x-ui.page-header>

    @include('meeting.partials.tabs')

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <x-ui.table caption="Firmas" :columns="$columns" :filterable="$signers->isNotEmpty()" count-label="firmas"
            filter-placeholder="Filtrar firmas">
            @forelse ($signers as $user)
                <x-ui.table.row>
                    <x-ui.table.cell label="Persona" primary>
                        <x-ui.person :name="$user->name" :surname="$user->surname" size="sm" />
                    </x-ui.table.cell>
                    <x-ui.table.cell label="UVUS" meta>{{ $user->username }}</x-ui.table.cell>
                    <x-ui.table.cell label="Firmada" meta>
                        <x-ui.time :value="$user->pivot->created_at" inline short />
                    </x-ui.table.cell>
                </x-ui.table.row>
            @empty
                <x-slot:empty>
                    <x-ui.empty icon="file-signature" title="Sin firmas" size="sm" :level="3" />
                </x-slot:empty>
            @endforelse
        </x-ui.table>

        <aside aria-label="Enlace y convocatoria" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Enlace para firmar" :level="2">
                <div class="grid gap-3">
                    <a href="{{ $signUrl }}" class="font-mono text-sm break-all">{{ $signUrl }}</a>
                    <x-ui.button icon="copy" :copy="$signUrl" class="justify-self-start">Copiar
                        enlace</x-ui.button>
                </div>
            </x-ui.card>
            @include('meeting.partials.sheet-request')
        </aside>
    </div>
</x-layout.app>
