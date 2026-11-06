@php
    $icons = [
        'check',
        'clock',
        'x',
        'file-text',
        'upload',
        'download',
        'users',
        'user-round',
        'calendar',
        'handshake',
        'ticket',
        'list-checks',
        'pen-line',
        'trash-2',
        'eye',
        'eye-off',
        'search',
        'bell',
        'log-out',
        'settings',
        'menu',
        'chevron-right',
        'chevron-down',
        'arrow-left',
        'plus',
        'copy',
        'triangle-alert',
        'circle-check',
        'circle-x',
        'info',
        'circle-help',
        'shield-check',
        'puzzle',
        'network',
        'shuffle',
        'import',
        'key-round',
        'user-check',
        'user-cog',
        'layout-dashboard',
        'message-square',
        'history',
        'file-signature',
        'scroll-text',
        'github',
        'sun',
        'moon',
        'monitor',
    ];
@endphp
<section id="ui-icons" class="glass grid gap-6 rounded-xl p-6 md:p-8" aria-labelledby="ui-icons-title">
    <h2 id="ui-icons-title" class="text-2xl font-bold tracking-tight">Iconos</h2>
    <ul class="flex flex-wrap gap-3">
        @foreach ($icons as $icon)
            <li class="glass-2 grid w-24 justify-items-center gap-2 rounded-md p-3 text-ink-muted">
                <x-ui.icon :name="$icon" class="size-5" />
                <span class="font-mono text-2xs">{{ $icon }}</span>
            </li>
        @endforeach
    </ul>
    <p class="flex flex-wrap items-center gap-4 text-sm text-ink-muted">
        <span class="inline-flex items-center gap-1.5"><x-lucide-check class="size-4" aria-hidden="true" />size-4</span>
        <span class="inline-flex items-center gap-1.5"><x-lucide-check class="size-4.5"
                aria-hidden="true" />size-4.5</span>
        <span class="inline-flex items-center gap-1.5"><x-lucide-check class="size-5" aria-hidden="true" />size-5</span>
        <span class="inline-flex items-center gap-1.5"><x-lucide-check class="size-5.5"
                aria-hidden="true" />size-5.5</span>
        <span class="inline-flex items-center gap-1.5"><x-lucide-check class="size-6" aria-hidden="true" />size-6</span>
        <span class="inline-flex items-center gap-1.5 text-accepted-ink"><x-ui.icon name="circle-check" label="Aceptada"
                class="size-5" />con etiqueta</span>
    </p>
</section>
