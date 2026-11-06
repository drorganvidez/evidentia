@php
    // Literal class names so Tailwind can see them.
    $families = [
        'aurora' => [
            'bg-aurora-50',
            'bg-aurora-100',
            'bg-aurora-200',
            'bg-aurora-300',
            'bg-aurora-400',
            'bg-aurora-500',
            'bg-aurora-600',
            'bg-aurora-700',
            'bg-aurora-800',
            'bg-aurora-900',
            'bg-aurora-950',
        ],
        'slate' => [
            'bg-slate-50',
            'bg-slate-100',
            'bg-slate-200',
            'bg-slate-300',
            'bg-slate-400',
            'bg-slate-500',
            'bg-slate-600',
            'bg-slate-700',
            'bg-slate-800',
            'bg-slate-900',
            'bg-slate-950',
        ],
        'cyan' => [
            'bg-cyan-50',
            'bg-cyan-100',
            'bg-cyan-200',
            'bg-cyan-300',
            'bg-cyan-400',
            'bg-cyan-500',
            'bg-cyan-600',
            'bg-cyan-700',
            'bg-cyan-800',
            'bg-cyan-900',
            'bg-cyan-950',
        ],
        'rose' => [
            'bg-rose-50',
            'bg-rose-100',
            'bg-rose-200',
            'bg-rose-300',
            'bg-rose-400',
            'bg-rose-500',
            'bg-rose-600',
            'bg-rose-700',
            'bg-rose-800',
            'bg-rose-900',
            'bg-rose-950',
        ],
        'success' => [
            'bg-success-50',
            'bg-success-100',
            'bg-success-200',
            'bg-success-300',
            'bg-success-400',
            'bg-success-500',
            'bg-success-600',
            'bg-success-700',
            'bg-success-800',
            'bg-success-900',
            'bg-success-950',
        ],
        'warning' => [
            'bg-warning-50',
            'bg-warning-100',
            'bg-warning-200',
            'bg-warning-300',
            'bg-warning-400',
            'bg-warning-500',
            'bg-warning-600',
            'bg-warning-700',
            'bg-warning-800',
            'bg-warning-900',
            'bg-warning-950',
        ],
        'danger' => [
            'bg-danger-50',
            'bg-danger-100',
            'bg-danger-200',
            'bg-danger-300',
            'bg-danger-400',
            'bg-danger-500',
            'bg-danger-600',
            'bg-danger-700',
            'bg-danger-800',
            'bg-danger-900',
            'bg-danger-950',
        ],
        'info' => [
            'bg-info-50',
            'bg-info-100',
            'bg-info-200',
            'bg-info-300',
            'bg-info-400',
            'bg-info-500',
            'bg-info-600',
            'bg-info-700',
            'bg-info-800',
            'bg-info-900',
            'bg-info-950',
        ],
    ];
    $steps = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
    $semantic = [
        ['canvas', 'bg-canvas text-ink'],
        ['glass', 'bg-glass text-ink'],
        ['glass-2', 'bg-glass-2 text-ink'],
        ['field', 'bg-field text-ink'],
        ['brand', 'bg-brand text-on-brand'],
        ['brand-lo', 'bg-brand-lo text-on-brand'],
        ['brand-soft', 'bg-brand-soft text-brand-soft-ink'],
        ['accepted-soft', 'bg-accepted-soft text-accepted-ink'],
        ['pending-soft', 'bg-pending-soft text-pending-ink'],
        ['rejected-soft', 'bg-rejected-soft text-rejected-ink'],
        ['draft-soft', 'bg-draft-soft text-draft-ink'],
        ['notice-soft', 'bg-notice-soft text-notice-ink'],
        ['accepted-glass', 'bg-accepted-glass text-accepted-ink'],
        ['pending-glass', 'bg-pending-glass text-pending-ink'],
        ['rejected-glass', 'bg-rejected-glass text-rejected-ink'],
        ['notice-glass', 'bg-notice-glass text-notice-ink'],
    ];
    $type = [
        ['text-6xl', 'Lo que haces en las jornadas, cuenta.', '6xl · 750'],
        ['text-5xl font-bold tracking-tighter', 'Cabecera de página', '5xl · 750'],
        ['text-4xl font-bold tracking-tighter', 'Detalle de una evidencia', '4xl · 750'],
        ['text-3xl font-bold tracking-tight', 'Convocatoria del comité', '3xl · 750'],
        ['text-2xl font-semibold tracking-tight', 'Horas acreditadas', '2xl · 650'],
        ['text-xl font-semibold tracking-tight', 'Actas pendientes de firma', 'xl · 650'],
        ['text-lg font-semibold', 'Título de una tarjeta', 'lg · 650'],
        ['text-base', 'Texto corriente de la aplicación, quince píxeles con interlineado holgado.', 'base · 450'],
        ['text-control', 'Texto de un control de formulario, dieciséis píxeles', 'control · 450'],
        ['text-md font-medium', 'Etiquetas de formulario y botones', 'md · 550'],
        ['text-sm text-ink-muted', 'Texto secundario y metadatos', 'sm · 450'],
        ['text-xs font-semibold tracking-wider uppercase text-ink-muted', 'Cabecera de tabla', 'xs · 650'],
        ['text-2xs font-semibold', 'Contadores y etiquetas pequeñas', '2xs · 650'],
    ];
    $radii = [
        ['rounded-xs', 'xs'],
        ['rounded-sm', 'sm'],
        ['rounded-md', 'md'],
        ['rounded-lg', 'lg'],
        ['rounded-xl', 'xl'],
        ['rounded-full', 'full'],
    ];
@endphp
<section id="ui-tokens" class="grid gap-8" aria-labelledby="ui-tokens-title">
    <h2 id="ui-tokens-title" class="px-2 text-2xl font-bold tracking-tight">Tokens</h2>

    <div class="glass grid gap-6 rounded-xl p-6 md:p-8">
        <h3 class="text-lg font-bold tracking-tight">Color</h3>
        <div class="grid gap-4">
            @foreach ($families as $family => $classes)
                <div class="grid gap-2">
                    <span class="font-mono text-xs text-ink-muted">{{ $family }}</span>
                    <ul class="grid grid-cols-6 gap-1.5 sm:grid-cols-11">
                        @foreach ($classes as $i => $class)
                            <li class="grid gap-1">
                                <span class="{{ $class }} h-9 rounded-sm ring-1 ring-rule"></span>
                                <span class="font-mono text-2xs text-ink-muted tabular-nums">{{ $steps[$i] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    <div class="glass grid gap-6 rounded-xl p-6 md:p-8">
        <h3 class="text-lg font-bold tracking-tight">Tokens semánticos</h3>
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($semantic as [$name, $class])
                <li class="{{ $class }} grid h-20 content-end rounded-md border border-rule p-3">
                    <span class="font-mono text-xs">{{ $name }}</span>
                </li>
            @endforeach
        </ul>
        <p class="flex flex-wrap gap-x-6 gap-y-2">
            <span class="text-ink">ink</span>
            <span class="text-ink-muted">ink-muted</span>
            <span class="text-brand-ink">brand-ink</span>
            <a href="#ui-tokens"
                class="text-brand-ink underline decoration-brand-ink/40 underline-offset-3 hover:decoration-brand-ink pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:items-center">enlace</a>
        </p>
    </div>

    <div class="glass grid gap-6 rounded-xl p-6 md:p-8">
        <h3 class="text-lg font-bold tracking-tight">Tipografía</h3>
        <ul class="grid gap-4">
            @foreach ($type as [$class, $sample, $token])
                <li
                    class="grid items-baseline gap-1 border-b border-rule pb-4 last:border-0 last:pb-0 md:grid-cols-[minmax(0,1fr)_8rem] md:gap-6">
                    <span class="{{ $class }}">{{ $sample }}</span>
                    <span class="font-mono text-xs text-ink-muted tabular-nums md:text-right">{{ $token }}</span>
                </li>
            @endforeach
            <li class="grid items-baseline gap-1 md:grid-cols-[minmax(0,1fr)_8rem] md:gap-6">
                <span class="font-mono text-xl font-semibold tracking-tight tabular-nums">12,5 h · 0123456789 · 20,00
                    h</span>
                <span class="font-mono text-xs text-ink-muted tabular-nums md:text-right">mono · 650</span>
            </li>
        </ul>
    </div>

    <div class="grid gap-8 lg:grid-cols-2">
        <div class="glass grid gap-6 rounded-xl p-6 md:p-8">
            <h3 class="text-lg font-bold tracking-tight">Radios</h3>
            <ul class="flex flex-wrap gap-4">
                @foreach ($radii as [$class, $name])
                    <li class="grid gap-2 text-center">
                        <span class="{{ $class }} block size-16 border border-glass-edge bg-brand-soft"></span>
                        <span class="font-mono text-xs text-ink-muted">{{ $name }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="glass grid gap-6 rounded-xl p-6 md:p-8">
            <h3 class="text-lg font-bold tracking-tight">Cristal</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="glass grid h-32 content-end rounded-lg p-4">
                    <span class="font-mono text-xs text-ink-muted">glass</span>
                </div>
                <div class="glass-2 grid h-32 content-end rounded-lg p-4">
                    <span class="font-mono text-xs text-ink-muted">glass-2</span>
                </div>
                <div class="grid h-32 content-end rounded-lg bg-brand p-4 text-on-brand shadow-glow">
                    <span class="font-mono text-xs">shadow-glow</span>
                </div>
                <div class="grid h-32 content-end rounded-lg bg-glass-2 p-4 shadow-raised">
                    <span class="font-mono text-xs text-ink-muted">shadow-raised</span>
                </div>
                <div class="glass halo-aurora grid h-32 content-end rounded-lg p-4">
                    <span class="font-mono text-xs text-ink-muted">halo-aurora</span>
                </div>
                <div class="grid h-32 content-end rounded-lg border border-field-edge bg-field p-4">
                    <span class="font-mono text-xs text-ink-muted">field · field-edge</span>
                </div>
            </div>
        </div>
    </div>

    <div class="glass grid gap-6 rounded-xl p-6 md:p-8">
        <h3 class="text-lg font-bold tracking-tight">Puntos de estado</h3>
        <ul class="flex flex-wrap gap-3">
            <li
                class="inline-flex items-center gap-2 rounded-full bg-accepted-soft py-1.25 pr-3 pl-2 text-sm font-semibold text-accepted-ink">
                <span class="size-2.25 rounded-full bg-accepted orb-glow [--orb:var(--accepted)]"></span>Aceptada
            </li>
            <li
                class="inline-flex items-center gap-2 rounded-full bg-pending-soft py-1.25 pr-3 pl-2 text-sm font-semibold text-pending-ink">
                <span
                    class="size-2.25 animate-pulse-orb rounded-full bg-pending [--orb:var(--pending)]"></span>Pendiente
            </li>
            <li
                class="inline-flex items-center gap-2 rounded-full bg-rejected-soft py-1.25 pr-3 pl-2 text-sm font-semibold text-rejected-ink">
                <span class="size-2.25 rounded-full bg-rejected orb-glow [--orb:var(--rejected)]"></span>Rechazada
            </li>
            <li
                class="inline-flex items-center gap-2 rounded-full bg-draft-soft py-1.25 pr-3 pl-2 text-sm font-semibold text-draft-ink">
                <span class="size-2.25 rounded-full bg-draft orb-glow [--orb:var(--draft)]"></span>Borrador
            </li>
            <li
                class="inline-flex items-center gap-2 rounded-full bg-notice-soft py-1.25 pr-3 pl-2 text-sm font-semibold text-notice-ink">
                <span class="size-2.25 rounded-full bg-notice orb-glow [--orb:var(--notice)]"></span>Aviso
            </li>
            @foreach ([0, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330] as $hue)
                <li
                    class="glass-2 inline-flex items-center gap-2 rounded-full py-1 pr-2.5 pl-2 text-sm font-medium text-ink">
                    <span class="size-2 rounded-full committee-dot hue-{{ $hue }}"></span>{{ $hue }}
                </li>
            @endforeach
        </ul>
    </div>
</section>
