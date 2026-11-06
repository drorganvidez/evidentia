{{--
    A new thread to one audience, optionally about an evidence, a minutes or a meeting request. With to=LECTURE and no
    object it is the old suggestions mailbox.
--}}
<x-layout.app :title="$title" nav-current="messages.mine" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Mis mensajes', 'href' => route('messages.mine')],
    ['label' => $title],
]">
    <x-ui.page-header :title="$title" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:gap-8">
        <x-ui.card :level="2">
            <x-ui.form :action="route('messages.store')" :fields="[
                'recipient' => 'Destinatario',
                'subject' => 'Asunto',
                'body' => 'Mensaje',
                'about_id' => 'Sobre',
            ]" busy-label="Enviando" id="message-form">
                @if ($about)
                    <input type="hidden" name="about_type" value="{{ $aboutType }}">
                    <input type="hidden" name="about_id" value="{{ $aboutId }}">
                @endif
                <x-ui.field name="recipient" label="Destinatario" type="select" :options="$recipients"
                    placeholder="Elige a quién escribes" :value="$preselected" required
                    hint="El profesorado puede leer e intervenir en cualquier hilo." />
                <x-ui.field name="subject" label="Asunto" :value="$subjectDefault" required :maxlength="255" />
                <x-ui.field name="body" label="Mensaje" type="textarea" :rows="8" :maxlength="20000"
                    required hint="Tu nombre y tu rol acompañan al mensaje." />
                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary" icon="send">Enviar</x-ui.button>
                    <x-ui.button variant="ghost" :href="$backHref">Cancelar</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </x-ui.card>
        @if ($about)
            @if ($aboutUrl)
                <x-ui.card title="Sobre" :level="2" :href="$aboutUrl" arrow>
                    <p class="font-semibold">{{ $aboutTitle }}</p>
                    @if ($about instanceof \App\Models\Evidence)
                        <p class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                            <x-ui.evidence-status :status="$about->status" size="sm" />
                            <x-ui.committee-chip :committee="$about->committee" />
                            <x-ui.hours :value="$about->hours" />
                        </p>
                    @endif
                </x-ui.card>
            @else
                <x-ui.card title="Sobre" :level="2">
                    <p class="font-semibold">{{ $aboutTitle }}</p>
                </x-ui.card>
            @endif
        @endif
    </div>
</x-layout.app>
