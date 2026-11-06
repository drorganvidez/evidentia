{{--
    The detail of an evidence as the student sees it after a rejection: the header with its badges and deadline,
    the steps, the reason of the coordinator, the purified description, the proofs with the upload to correct them,
    the data and the history in the aside, and the deletion behind an alert dialog. Served at /_ui/detail.
--}}
@php
    $here = route('ui.page', 'detail');
    $home = route('ui.index');
    $now = now();
    $nav = [
        [
            'label' => null,
            'items' => [
                ['label' => 'Inicio', 'href' => $home, 'icon' => 'layout-dashboard'],
                ['label' => 'Mi perfil', 'href' => $here . '#perfil', 'icon' => 'user-round'],
                [
                    'label' => 'Mis mensajes',
                    'href' => $here . '#mensajes',
                    'icon' => 'message-square',
                    'count' => 1,
                    'countLabel' => 'sin leer',
                ],
            ],
        ],
        [
            'label' => 'Mis cosas',
            'items' => [
                ['label' => 'Nueva evidencia', 'href' => $here . '#nueva', 'icon' => 'plus'],
                ['label' => 'Mis evidencias', 'href' => $here, 'icon' => 'file-text', 'current' => true],
                ['label' => 'Mis reuniones', 'href' => $here . '#reuniones', 'icon' => 'handshake'],
                ['label' => 'Mis asistencias', 'href' => $here . '#asistencias', 'icon' => 'ticket'],
            ],
        ],
    ];
    $mobileNav = [
        ['label' => 'Inicio', 'href' => $home, 'icon' => 'layout-dashboard'],
        ['label' => 'Evidencias', 'href' => $here, 'icon' => 'file-text', 'current' => true],
        ['label' => 'Reuniones', 'href' => $here . '#reuniones', 'icon' => 'handshake'],
        ['label' => 'Asistencias', 'href' => $here . '#asistencias', 'icon' => 'ticket'],
        ['label' => 'Perfil', 'href' => $here . '#perfil', 'icon' => 'user-round'],
    ];
    $closesAt = $now->copy()->addDays(14);
    $deadline = [
        'label' => 'Subida de evidencias',
        'opensAt' => $now->copy()->subDays(30),
        'closesAt' => $closesAt,
        'href' => $here,
    ];
    $user = [
        'name' => 'Juan',
        'surname' => 'Pérez',
        'roles' => ['Estudiante'],
        'profileHref' => $here . '#perfil',
        'logoutAction' => $here,
    ];
    $files = [
        ['name' => 'informe-montaje.pdf', 'size' => 1258291, 'href' => $here . '#informe', 'type' => 'application/pdf'],
        ['name' => 'photocall-estructura.jpg', 'size' => 2457600, 'href' => $here . '#foto', 'type' => 'image/jpeg'],
        ['name' => 'material.zip', 'size' => 8912896, 'href' => $here . '#material', 'type' => 'application/zip'],
    ];
    $description = implode('', [
        '<p>Montaje del <strong>photocall</strong> del vestíbulo principal durante las dos tardes previas a la apertura, ',
        'con el equipo de Logística y dos voluntarios de Comunicación.</p>',
        '<ul><li>Estructura de aluminio de 3 × 2 m</li><li>Lona impresa con los patrocinadores</li><li>Dos focos LED</li></ul>',
        '<p>El material se devolvió al almacén el <em>24 de octubre</em>.</p>',
    ]);
    $reason = 'Faltan las fotos del montaje terminado y el albarán del material.';
@endphp
<x-layout.app title="Montaje del photocall" :nav="$nav" :mobile-nav="$mobileNav" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => $home],
    ['label' => 'Mis evidencias', 'href' => $here],
    ['label' => 'Montaje del photocall'],
]" :deadline="$deadline"
    :user="$user" :notifications="['count' => 1, 'href' => $here . '#avisos']" :search="['action' => $home]" subtitle="Jornadas InnoSoft 2026" version="4.0.0">
    <x-ui.page-header title="Montaje del photocall">
        <x-slot:eyebrow>
            <x-ui.evidence-status status="REJECTED" :reason="$reason" />
            <x-ui.committee-chip name="Logística" :id="7" icon="truck" />
            <x-ui.badge variant="chip" mono>EV-0033</x-ui.badge>
        </x-slot:eyebrow>
        <x-slot:meta>
            <x-ui.deadline label="Subida de evidencias" variant="inline" verb="para corregirla" :closes-at="$closesAt" />
        </x-slot:meta>
        <x-slot:actions>
            <x-ui.button variant="primary" icon="pencil" :href="$here . '#editar'">Corregir y volver a enviar</x-ui.button>
            <x-ui.dialog id="remove-33" variant="alert" title="Eliminar la evidencia"
                description="Se borrarán también sus pruebas. Esta acción no se puede deshacer." trigger="Eliminar"
                trigger-icon="trash-2">
                <x-slot:footer>
                    <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                    <form method="get" action="{{ $here }}">
                        <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, eliminar</x-ui.button>
                    </form>
                </x-slot:footer>
            </x-ui.dialog>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.timeline variant="steps" label="Recorrido de la evidencia" :columns="4">
        <x-ui.timeline.item state="done" title="Borrador" :at="$now->copy()->subDays(5)" />
        <x-ui.timeline.item state="done" title="Enviada" :at="$now->copy()->subDays(4)" />
        <x-ui.timeline.item state="error" title="Rechazada" :at="$now->copy()->subDays(2)" />
        <x-ui.timeline.item state="upcoming" title="Aceptada" hint="Pendiente de corregir" />
    </x-ui.timeline>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] lg:gap-8">
        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.alert tone="rejected" title="Tu coordinador necesita más pruebas" :quote="$reason">
                <x-slot:meta>
                    <x-ui.person name="Carlos" surname="Navarro" role="Coordinador de Logística" size="sm" />
                    <x-ui.time :value="$now->copy()->subDays(2)" inline short />
                </x-slot:meta>
                <x-slot:actions>
                    <x-ui.button variant="primary" icon="pencil" :href="$here . '#editar'">Corregir y volver a
                        enviar</x-ui.button>
                </x-slot:actions>
            </x-ui.alert>

            <x-ui.card title="Descripción" :level="2" halo>
                <x-ui.rich-text :html="Html::clean($description)" />
            </x-ui.card>

            <x-ui.card title="Pruebas" :level="2" id="editar">
                <x-slot:extra>
                    <x-ui.badge variant="count">{{ count($files) }}</x-ui.badge>
                </x-slot:extra>
                <div class="grid grid-cols-[minmax(0,1fr)] gap-6">
                    <x-ui.file-list :files="$files" label="Pruebas de la evidencia" />
                    <x-ui.form :action="$here" files id="proof-form" busy-label="Enviando" :fields="['evidence_files' => 'Pruebas']">
                        <x-ui.upload name="evidence_files[]" :url="route('ui.demo.upload')" :revert-url="route('ui.demo.revert')" :remove-url="route('ui.demo.remove', 'NAME')"
                            label="Arrastra aquí las fotos que faltan o pulsa para elegirlas" />
                        <x-slot:actions>
                            <x-ui.button type="submit" variant="primary" icon="send">Volver a enviar</x-ui.button>
                        </x-slot:actions>
                    </x-ui.form>
                </div>
            </x-ui.card>
        </div>

        <aside aria-label="Datos e historial" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Datos" :level="2">
                <x-ui.dl>
                    <x-ui.dl.item term="Registro" mono>EV-0033</x-ui.dl.item>
                    <x-ui.dl.item term="Comité"><x-ui.committee-chip name="Logística" :id="7"
                            icon="truck" /></x-ui.dl.item>
                    <x-ui.dl.item term="Horas"><x-ui.hours :value="1.75" /></x-ui.dl.item>
                    <x-ui.dl.item term="Estado"><x-ui.evidence-status status="REJECTED"
                            size="sm" /></x-ui.dl.item>
                    <x-ui.dl.item term="Subida"><x-ui.time :value="$now->copy()->subDays(4)" inline short /></x-ui.dl.item>
                    <x-ui.dl.item term="Participantes">
                        <ul class="grid gap-1.5">
                            <li><x-ui.person name="Juan" surname="Pérez" size="sm" /></li>
                            <li><x-ui.person name="Lucía" surname="Gómez" size="sm" /></li>
                        </ul>
                    </x-ui.dl.item>
                </x-ui.dl>
            </x-ui.card>

            <x-ui.card title="Historial" :level="2">
                <x-ui.timeline label="Historial de la evidencia">
                    <x-ui.timeline.item tone="rejected" title="Rechazada" :at="$now->copy()->subDays(2)">por Carlos
                        Navarro</x-ui.timeline.item>
                    <x-ui.timeline.item tone="pending" title="Enviada a revisión" :at="$now->copy()->subDays(4)">por Juan
                        Pérez</x-ui.timeline.item>
                    <x-ui.timeline.item tone="draft" title="Creada como borrador" :at="$now->copy()->subDays(5)">por Juan
                        Pérez</x-ui.timeline.item>
                </x-ui.timeline>
            </x-ui.card>
        </aside>
    </div>
</x-layout.app>
