{{--
    History of an evidence: its steps, every version, the annotations the viewer may see and the threads about it.
    From here the author changes its committee, the coordinator moves a pending one and the management moves or
    reviews it outside the flow.
--}}
@php
    $registry = 'EV-' . str_pad((string) $root->id, 4, '0', STR_PAD_LEFT);
    $moveTitle = $isOwner ? 'Cambiar de comité' : 'Mover a otro comité';
    $fixLabel = 'Corregir y volver a enviar';
@endphp
<x-layout.app :title="'Historial · ' . $head->title" :nav-current="$navCurrent" :breadcrumbs="$breadcrumbs">
    <x-ui.page-header :title="$head->title">
        <x-slot:eyebrow>
            <x-ui.evidence-status :status="$head->status" :reason="$head->reasonRejection?->reason" />
            <x-ui.committee-chip :committee="$head->committee" />
            <x-ui.badge variant="chip" mono>{{ $registry }}</x-ui.badge>
        </x-slot:eyebrow>
        @if ($inlineDeadline)
            <x-slot:meta>
                <x-ui.deadline :label="$inlineDeadline->label()" variant="inline" :verb="$inlineDeadline->verb()" :closes-at="$deadlines->closesAt($inlineDeadline)" />
            </x-slot:meta>
        @endif
        @if ($isOwner || $moveRoute || $canReview)
            <x-slot:actions>
                @if ($isOwner)
                    <x-ui.button icon="message-square"
                        :href="route('messages.create', ['evidence' => $head->id])">{{ app(\App\Services\ReviewQueue::class)->goesToPresidency($head) ? 'Escribir a presidencia' : 'Escribir al coordinador' }}</x-ui.button>
                @endif
                @if ($moveRoute)
                    <x-ui.dialog id="move-dialog" :title="$moveTitle" :trigger="$moveTitle" trigger-icon="arrow-right-left"
                        :open-on-load="request()->boolean('move') || $errors->has('committee')">
                        <x-ui.form :action="$moveRoute" :fields="['committee' => 'Comité']" busy-label="Moviendo" :focus-summary="false"
                            id="move-form">
                            <input type="hidden" name="_id" value="{{ $head->id }}">
                            <x-ui.field name="committee" label="Comité de destino" type="select" :options="$committeeOptions"
                                placeholder="Elige un comité" required
                                hint="Conserva las pruebas, la descripción y el estado." />
                            @if ($moveDeadlineClosed)
                                <x-ui.alert tone="pending" title="Fuera de plazo" compact>Quedará señalado en el
                                    registro.</x-ui.alert>
                            @endif
                            <x-slot:actions>
                                <x-ui.button type="submit" variant="primary"
                                    icon="arrow-right-left">Mover</x-ui.button>
                                <x-ui.button x-on:click="close">Cancelar</x-ui.button>
                            </x-slot:actions>
                        </x-ui.form>
                    </x-ui.dialog>
                @endif
                @if ($canReview)
                    <x-ui.dialog id="review-dialog" title="Revisar la evidencia" trigger="Aceptar o rechazar"
                        trigger-icon="shield-check" :open-on-load="$errors->hasAny(['decision', 'reasonrejection'])">
                        <x-ui.form :action="route('management.evidence.review')" :fields="['decision' => 'Decisión', 'reasonrejection' => 'Motivo del rechazo']" busy-label="Guardando" :focus-summary="false"
                            id="review-form">
                            <input type="hidden" name="_id" value="{{ $head->id }}">
                            <x-ui.segmented name="decision" legend="Decisión" :hide-legend="false" :options="[
                                ['value' => 'accept', 'label' => 'Aceptar'],
                                ['value' => 'reject', 'label' => 'Rechazar'],
                            ]"
                                value="accept" />
                            <x-ui.field name="reasonrejection" label="Motivo del rechazo" type="textarea"
                                :rows="4" :maxlength="1000" hint="Obligatorio si rechazas." />
                            @unless ($validationOpen)
                                <x-ui.alert tone="pending" title="Plazo de validación cerrado" compact>Quedará señalado en
                                    el registro.</x-ui.alert>
                            @endunless
                            <x-slot:actions>
                                <x-ui.button type="submit" variant="primary">Guardar</x-ui.button>
                                <x-ui.button x-on:click="close">Cancelar</x-ui.button>
                            </x-slot:actions>
                        </x-ui.form>
                    </x-ui.dialog>
                @endif
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    @if ($detailHref)
        <x-ui.tabs class="min-w-0" label="Secciones de la evidencia" size="md" :items="[
            ['label' => 'Detalle', 'href' => $detailHref],
            ['label' => 'Historial', 'href' => route('activity.evidence', ['id' => $head->id]), 'current' => true],
        ]" />
    @endif

    <x-ui.timeline variant="steps" label="Recorrido de la evidencia" :columns="4">
        <x-ui.timeline.item state="done" title="Borrador" :at="$steps['draftAt']" />
        <x-ui.timeline.item :state="$steps['sent'] ? 'done' : 'upcoming'" title="Enviada" :at="$steps['sentAt']" />
        <x-ui.timeline.item :state="$head->status === 'REJECTED' ? 'error' : ($steps['rejected'] ? 'done' : 'upcoming')" title="Rechazada" :at="$steps['rejectedAt']" />
        <x-ui.timeline.item :state="$head->status === 'ACCEPTED' ? 'current' : 'upcoming'" title="Aceptada" :at="$steps['acceptedAt']" :hint="$head->status === 'REJECTED' ? 'Pendiente de corregir' : null" />
    </x-ui.timeline>

    @if ($head->status === 'REJECTED' && $head->reasonRejection)
        <x-ui.alert tone="rejected" title="Motivo del rechazo" :quote="$head->reasonRejection->reason">
            @if ($lastRejection)
                <x-slot:meta>
                    <x-ui.person :name="$lastRejection->user?->name ?? $lastRejection->user_name" :surname="$lastRejection->user?->surname" :role="$presenter->roleLine($lastRejection)" size="sm" />
                    <x-ui.time :value="$lastRejection->created_at" inline short />
                </x-slot:meta>
            @endif
            @if ($isOwner && $uploadOpen)
                <x-slot:actions>
                    <x-ui.button variant="primary" icon="pen-line" :href="route('evidence.view', ['id' => $head->id])">{{ $fixLabel }}</x-ui.button>
                </x-slot:actions>
            @endif
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:gap-8">
        <x-ui.card title="Historial" :level="2" padding="sm">
            @include('activity.partials.log', [
                'entries' => $entries,
                'viewer' => $me,
                'label' => 'Historial de la evidencia',
            ])
        </x-ui.card>
        <aside aria-label="Datos y versiones" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Datos" :level="2">
                <x-ui.dl>
                    <x-ui.dl.item term="Registro" mono>{{ $registry }}</x-ui.dl.item>
                    <x-ui.dl.item term="Autor">
                        <x-ui.person :name="$head->user?->name" :surname="$head->user?->surname" size="sm" :href="$access->profileHref($me, $head->user_id)" />
                    </x-ui.dl.item>
                    <x-ui.dl.item term="Comité"><x-ui.committee-chip :committee="$head->committee" /></x-ui.dl.item>
                    <x-ui.dl.item term="Horas"><x-ui.hours :value="$head->hours" /></x-ui.dl.item>
                    <x-ui.dl.item term="Estado"><x-ui.evidence-status :status="$head->status"
                            size="sm" /></x-ui.dl.item>
                    <x-ui.dl.item term="Última versión"><x-ui.time :value="$head->created_at" inline short /></x-ui.dl.item>
                </x-ui.dl>
            </x-ui.card>
            <x-ui.card title="Versiones" :level="2">
                <x-slot:extra>
                    <x-ui.badge variant="count">{{ $versions->count() }}</x-ui.badge>
                </x-slot:extra>
                <ol class="grid gap-3">
                    @foreach ($versions as $version)
                        <li class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold">Versión {{ $version->versionNumber() }}</span>
                            <x-ui.evidence-status :status="$version->status" size="sm" />
                            <x-ui.committee-chip :committee="$version->committee" />
                            <x-ui.time :value="$version->created_at" inline short />
                        </li>
                    @endforeach
                </ol>
            </x-ui.card>
            @if ($threads->isNotEmpty())
                <x-ui.card title="Mensajes sobre esta evidencia" :level="2">
                    <ul class="grid gap-2">
                        @foreach ($threads as $thread)
                            <li class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('messages.show', $thread) }}">{{ $thread->subject }}</a>
                                <x-ui.badge :tone="$thread->isResolved() ? 'accepted' : 'pending'"
                                    size="sm">{{ $thread->isResolved() ? 'Resuelto' : 'Abierto' }}</x-ui.badge>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            @endif
        </aside>
    </div>
</x-layout.app>
