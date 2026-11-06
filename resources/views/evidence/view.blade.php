{{--
    Detail of an evidence for its author (evidence.view) and for the coordinator of its committee
    (coordinator.evidence.view): the steps, the latest reason of a rejection, the description, the proofs, the data and
    the history. The coordinator who wrote it reads it as its author: nobody accepts or rejects their own evidence.
--}}
@php
    $user = Auth::user();
    $coordinated = $user->hasRole('COORDINATOR') ? $user->coordinator?->committee : null;
    $reviewer =
        $coordinated !== null && $evidence->committee_id === $coordinated->id && $evidence->user_id !== $user->id;
    $author = !$reviewer && $evidence->user_id === $user->id;
    $validationOpen = !now()->gt(\Config::validate_evidences_timestamp());
    $uploadClosesAt = \Config::upload_evidences_timestamp();
    $uploadOpen = !now()->gt($uploadClosesAt);
    $reason = $evidence->status === 'REJECTED' ? $evidence->reasonRejection : null;
    $reasons = $evidence->allRejectionReasons();
    $versions = $evidence->flowEvidences();
    $first = $versions->sortBy('created_at')->first();
    $proofs = $evidence->proofs->map(
        fn($proof) => [
            'name' => $proof->file->name . '.' . $proof->file->type,
            'size' => $proof->file->size,
            'href' => route('proof.download', ['id' => $proof->id]),
            'type' => $proof->file->type,
        ],
    );
    // Newest first; events of the same second keep their order (versions, then rejections by id)
    $history = $versions
        ->sortBy('created_at')
        ->map(
            fn($version) => [
                'at' => $version->created_at,
                'tone' => 'draft',
                'title' => $version->is($first) ? 'Creada' : 'Editada',
                'text' => null,
            ],
        )
        ->concat(
            $reasons->map(
                fn($rejection) => [
                    'at' => $rejection->created_at,
                    'tone' => 'rejected',
                    'title' => 'Rechazada',
                    'text' => $rejection->reason,
                ],
            ),
        )
        ->values()
        ->map(fn(array $event, int $index) => $event + ['order' => $index])
        ->sortByDesc(fn(array $event) => sprintf('%012d-%05d', $event['at']?->getTimestamp() ?? 0, $event['order']))
        ->values();
    $list = $reviewer
        ? ['label' => 'Revisar evidencias', 'href' => route('coordinator.evidence.list.all')]
        : ['label' => 'Mis evidencias', 'href' => route('evidence.list')];
    $sent = $evidence->status !== 'DRAFT';
    $reviewed = in_array($evidence->status, ['ACCEPTED', 'REJECTED'], true);
@endphp
<x-layout.app :title="$evidence->title" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], $list, ['label' => $evidence->title]]">
    <x-ui.page-header :title="$evidence->title">
        <x-slot:eyebrow>
            <x-ui.evidence-status :status="$evidence->status" :reason="$reason?->reason" />
            <x-ui.committee-chip :committee="$evidence->committee" />
            <x-ui.badge variant="chip" mono>EV-{{ str_pad((string) $evidence->id, 4, '0', STR_PAD_LEFT) }}</x-ui.badge>
        </x-slot:eyebrow>
        @if ($author && $uploadOpen && $uploadClosesAt !== null && in_array($evidence->status, ['DRAFT', 'REJECTED'], true))
            <x-slot:meta>
                <x-ui.deadline label="Subida de evidencias" variant="inline" :verb="$evidence->status === 'REJECTED' ? 'para corregirla' : 'para enviarla'" :closes-at="$uploadClosesAt" />
            </x-slot:meta>
        @endif
        @if ($author && $uploadOpen)
            <x-slot:actions>
                @include('evidence.partials.actions', ['evidence' => $evidence])
            </x-slot:actions>
        @elseif ($reviewer && $validationOpen && $evidence->status !== 'DRAFT')
            <x-slot:actions>
                @if ($evidence->status !== 'ACCEPTED')
                    <form method="post" action="{{ route('coordinator.evidence.accept', ['id' => $evidence->id]) }}">
                        @csrf
                        <x-ui.button variant="primary" type="submit" icon="circle-check" block>Aceptar</x-ui.button>
                    </form>
                @endif
                @if ($evidence->status !== 'REJECTED')
                    <x-ui.dialog id="reject" title="Rechazar la evidencia" trigger="Rechazar" trigger-icon="circle-x"
                        trigger-variant="danger">
                        <x-ui.form :action="route('coordinator.evidence.reject')" id="reject-form" busy-label="Rechazando" :fields="['reasonrejection' => 'Motivo del rechazo']"
                            :focus-summary="false">
                            <input type="hidden" name="_id" value="{{ $evidence->id }}">
                            <x-ui.field name="reasonrejection" label="Motivo del rechazo" type="textarea"
                                :rows="4" :maxlength="1000" required />
                            <x-slot:actions>
                                <x-ui.button variant="danger" type="submit" icon="circle-x">Rechazar</x-ui.button>
                                <x-ui.button x-on:click="close">Cancelar</x-ui.button>
                            </x-slot:actions>
                        </x-ui.form>
                    </x-ui.dialog>
                @endif
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.timeline variant="steps" label="Recorrido de la evidencia" :columns="3">
        <x-ui.timeline.item :state="$sent ? 'done' : 'current'" title="Borrador" :at="$first?->created_at" />
        <x-ui.timeline.item :state="$reviewed ? 'done' : ($sent ? 'current' : 'upcoming')" title="Enviada a revisión" :at="$sent ? $evidence->created_at : null" />
        <x-ui.timeline.item :state="match ($evidence->status) {
            'ACCEPTED' => 'done',
            'REJECTED' => 'error',
            default => 'upcoming',
        }" :title="$evidence->status === 'REJECTED' ? 'Rechazada' : 'Aceptada'" :at="$reviewed ? $evidence->updated_at : null" />
    </x-ui.timeline>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            @if ($reason !== null)
                <x-ui.alert tone="rejected" title="Motivo del rechazo" :quote="$reason->reason">
                    <x-slot:meta>
                        <x-ui.time :value="$reason->created_at" inline short />
                    </x-slot:meta>
                </x-ui.alert>
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

        <aside aria-label="Datos e historial" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Datos" :level="2">
                <x-ui.dl>
                    <x-ui.dl.item term="Registro"
                        mono>EV-{{ str_pad((string) $evidence->id, 4, '0', STR_PAD_LEFT) }}</x-ui.dl.item>
                    <x-ui.dl.item term="Comité"><x-ui.committee-chip :committee="$evidence->committee" /></x-ui.dl.item>
                    <x-ui.dl.item term="Horas"><x-ui.hours :value="$evidence->hours" /></x-ui.dl.item>
                    <x-ui.dl.item term="Estado"><x-ui.evidence-status :status="$evidence->status"
                            size="sm" /></x-ui.dl.item>
                    <x-ui.dl.item term="Editada"><x-ui.time :value="$evidence->created_at" inline short /></x-ui.dl.item>
                    @unless ($author)
                        <x-ui.dl.item term="Autor"><x-ui.person :name="$evidence->user?->name" :surname="$evidence->user?->surname"
                                size="sm" /></x-ui.dl.item>
                    @endunless
                </x-ui.dl>
            </x-ui.card>

            <x-ui.card title="Historial" :level="2" id="history">
                <x-ui.timeline label="Historial de la evidencia">
                    @foreach ($history as $event)
                        <x-ui.timeline.item :tone="$event['tone']" :title="$event['title']" :at="$event['at']">
                            @if ($event['text'] !== null)
                                <span class="block text-ink-muted">{{ $event['text'] }}</span>
                            @endif
                        </x-ui.timeline.item>
                    @endforeach
                </x-ui.timeline>
            </x-ui.card>
        </aside>
    </div>
</x-layout.app>
