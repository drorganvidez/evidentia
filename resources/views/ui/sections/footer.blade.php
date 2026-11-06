{{-- The page footer with the version pill, without version and in its compact form for the public pages --}}
<section id="ui-footer" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-footer-title">
    <h2 id="ui-footer-title" class="text-2xl font-bold tracking-tight">Pie</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-4">
        <div class="grid gap-2" data-demo="full">
            <span class="font-mono text-2xs text-ink-muted">version · versionHref · links · meta</span>
            <x-ui.footer version="4.0.0" :version-href="route('ui.index') . '#ui-footer'" class="mt-0" />
        </div>

        <div class="grid gap-2" data-demo="plain">
            <span class="font-mono text-2xs text-ink-muted">sin versión</span>
            <x-ui.footer :version="false" class="mt-0" />
        </div>

        <div class="grid gap-2" data-demo="compact">
            <span class="font-mono text-2xs text-ink-muted">compact · páginas públicas</span>
            <div class="rounded-lg border border-rule bg-canvas">
                <x-ui.footer compact version="4.0.0" />
            </div>
        </div>
    </div>
</section>
