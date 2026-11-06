{{-- The brand in its three sizes, with and without subtitle, mark only, over light and dark, and the intense aurora --}}
@php
    $subtitle = 'Jornadas InnoSoft 2026';
@endphp
<section id="ui-brand" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-brand-title">
    <h2 id="ui-brand-title" class="text-2xl font-bold tracking-tight">Marca</h2>

    <div class="grid gap-4">
        <div class="flex flex-wrap items-center gap-6">
            <span class="w-20 font-mono text-xs text-ink-muted">sm · md · lg</span>
            <x-ui.brand size="sm" href="#ui-brand" />
            <x-ui.brand href="#ui-brand" />
            <x-ui.brand size="lg" href="#ui-brand" />
        </div>
        <div class="flex flex-wrap items-center gap-6">
            <span class="w-20 font-mono text-xs text-ink-muted">subtitle</span>
            <x-ui.brand size="sm" :subtitle="$subtitle" href="#ui-brand" />
            <x-ui.brand :subtitle="$subtitle" href="#ui-brand" />
            <x-ui.brand size="lg" :subtitle="$subtitle" href="#ui-brand" />
        </div>
        <div class="flex flex-wrap items-center gap-6">
            <span class="w-20 font-mono text-xs text-ink-muted">mark-only</span>
            <x-ui.brand size="sm" mark-only href="#ui-brand" />
            <x-ui.brand mark-only href="#ui-brand" />
            <x-ui.brand size="lg" mark-only href="#ui-brand" />
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="glass-2 flex flex-wrap items-center justify-between gap-6 rounded-lg p-6">
            <x-ui.brand :subtitle="$subtitle" href="#ui-brand" />
            <span class="font-mono text-xs text-ink-muted">claro</span>
        </div>
        {{-- The semantic tokens live on :root, so the dark block overrides the two text tokens it uses --}}
        <div data-theme="dark"
            class="flex flex-wrap items-center justify-between gap-6 rounded-lg bg-slate-950 p-6 [--ink:var(--color-slate-100)] [--ink-muted:var(--color-slate-300)]">
            <x-ui.brand :subtitle="$subtitle" href="#ui-brand" />
            <span class="font-mono text-xs text-ink-muted">oscuro</span>
        </div>
    </div>

    <div class="relative isolate overflow-hidden rounded-xl border border-glass-edge bg-canvas p-6 md:p-10">
        <x-ui.aurora intense static />
        <div class="glass flex flex-wrap items-center justify-between gap-6 rounded-lg p-6">
            <x-ui.brand size="lg" :subtitle="$subtitle" href="#ui-brand" />
            <span class="font-mono text-xs text-ink-muted">aurora intense static</span>
        </div>
    </div>
</section>
