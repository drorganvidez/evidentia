{{-- Cards: title row with extra content, footer, linked card with arrow, halo header, compact and bare paddings, own header --}}
@php
    $files = [
        ['name' => 'informe-final.pdf', 'size' => '1,2 MB'],
        ['name' => 'captura-photocall.png', 'size' => '640 kB'],
        ['name' => 'guion-presentacion.docx', 'size' => '88 kB'],
    ];
@endphp
<section id="ui-card" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-card-title">
    <h2 id="ui-card-title" class="text-2xl font-bold tracking-tight">Tarjetas</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4 lg:grid-cols-2">
        <div class="grid content-start gap-2" data-demo="extra">
            <span class="font-mono text-2xs text-ink-muted">title · extra</span>
            <x-ui.card title="Pruebas" :level="3">
                <x-slot:extra>
                    <span class="text-sm text-ink-muted">3 archivos</span>
                </x-slot:extra>
                <ul class="grid gap-2 text-sm">
                    @foreach ($files as $file)
                        <li class="flex min-w-0 items-center gap-3">
                            <x-lucide-file-text aria-hidden="true" class="size-4.5 shrink-0 text-ink-muted" />
                            <span class="min-w-0 flex-1 truncate font-medium">{{ $file['name'] }}</span>
                            <span class="font-mono text-xs text-ink-muted tabular-nums">{{ $file['size'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>

        <div class="grid content-start gap-2" data-demo="footer">
            <span class="font-mono text-2xs text-ink-muted">footer</span>
            <x-ui.card title="Acta de la reunión 3" :level="3">
                <p class="text-ink-muted">Firmada por la secretaría el 21 de octubre.</p>
                <x-slot:footer class="flex flex-wrap items-center justify-between gap-3 text-sm">
                    <x-ui.time :value="now()->subDays(16)" inline short />
                    <a href="{{ route('ui.index') }}#ui-card" class="font-semibold text-brand-ink">Ver el acta</a>
                </x-slot:footer>
            </x-ui.card>
        </div>

        <div class="grid content-start gap-2" data-demo="link">
            <span class="font-mono text-2xs text-ink-muted">href · arrow</span>
            <x-ui.card title="Mis horas" :level="3" href="{{ route('ui.index') }}#ui-card" arrow>
                <p class="flex flex-wrap items-baseline gap-x-3">
                    <x-ui.hours :value="10.5" size="xl" class="font-bold tracking-tighter" />
                    <span class="text-sm text-ink-muted">de 20 h</span>
                </p>
            </x-ui.card>
        </div>

        <div class="grid content-start gap-2" data-demo="halo">
            <span class="font-mono text-2xs text-ink-muted">halo · extra</span>
            <x-ui.card title="Montaje del photocall" :level="3" halo>
                <x-slot:extra>
                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-pending-ink">
                        <span aria-hidden="true" class="size-2.25 rounded-full bg-pending"></span>
                        Pendiente de revisión
                    </span>
                </x-slot:extra>
                <p class="flex flex-wrap items-center gap-x-2 text-sm text-ink-muted">
                    <x-ui.hours :value="1.75" size="sm" />
                    <span aria-hidden="true">·</span>
                    <x-ui.time :value="now()->subHours(6)" inline short />
                </p>
            </x-ui.card>
        </div>

        <div class="grid content-start gap-2" data-demo="compact">
            <span class="font-mono text-2xs text-ink-muted">padding="sm" · as="article"</span>
            <x-ui.card title="Resumen de la semana" :level="3" padding="sm" as="article">
                <p class="text-sm">Tres evidencias aceptadas y una pendiente de revisión.</p>
            </x-ui.card>
        </div>

        <div class="grid content-start gap-2" data-demo="bare">
            <span class="font-mono text-2xs text-ink-muted">padding="none" · aria-label</span>
            <x-ui.card padding="none" aria-label="Archivos de la evidencia">
                <ul class="divide-y divide-rule text-sm">
                    @foreach ($files as $file)
                        <li class="flex min-w-0 items-center gap-3 px-5 py-3">
                            <span class="min-w-0 flex-1 truncate font-medium">{{ $file['name'] }}</span>
                            <span class="font-mono text-xs text-ink-muted tabular-nums">{{ $file['size'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>

        <div class="grid content-start gap-2 lg:col-span-2" data-demo="header">
            <span class="font-mono text-2xs text-ink-muted">slot header</span>
            <x-ui.card :level="3">
                <x-slot:header class="items-center">
                    <span class="inline-flex items-center gap-3">
                        <span aria-hidden="true"
                            class="grid size-9 place-items-center rounded-full bg-linear-135 from-aurora-400 to-cyan-400 text-xs font-bold text-white">JP</span>
                        <span class="grid leading-tight">
                            <span class="font-semibold">Juan Pérez</span>
                            <span class="text-xs text-ink-muted">como coordinador de Programa</span>
                        </span>
                    </span>
                    <x-ui.time :value="now()->subDays(3)" inline short class="text-sm text-ink-muted" />
                </x-slot:header>
                <p>He revisado el acta y falta la firma de la secretaría. ¿Puedes subir la versión firmada?</p>
            </x-ui.card>
        </div>
    </div>
</section>
