{{--
    The app layout with demo data at the three widths. Each frame is a srcdoc document (the policy forbids framing a
    URL), so its media queries follow the frame width: sidebar at 1280, rail at 768, bottom bar and drawer at 400.
    The document waits in data-srcdoc until the frame scrolls into view (lazyFrame.js) and comes without the aurora:
    three extra shells painting twelve blurred blobs would weigh on every visit to the other sections.
--}}
@php
    // Without the aurora: twelve blurred blobs animating in three frames would weigh on every visit to the page
    $shell = view('ui.pages.shell', ['aurora' => false])->render();
    $frames = [['Escritorio', 1280, 'w-[1280px]'], ['Tableta', 768, 'w-[768px]'], ['Teléfono', 400, 'w-[400px]']];
@endphp
<section id="ui-layout" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-layout-title">
    <h2 id="ui-layout-title" class="text-2xl font-bold tracking-tight">Layout</h2>

    <p class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
        <span class="font-mono text-2xs text-ink-muted">x-layout.app · nav · mobileNav · deadline · user · notifications
            · search</span>
        <a href="{{ route('ui.page', 'shell') }}"
            class="font-semibold text-brand-ink pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:items-center">Abrir
            el shell en su propia
            página</a>
    </p>

    @foreach ($frames as [$label, $width, $widthClass])
        <div class="grid grid-cols-[minmax(0,1fr)] gap-2">
            <span class="font-mono text-2xs text-ink-muted">{{ $label }} · {{ $width }} px</span>
            <div x-data="lazyFrame" role="region" aria-label="Shell a {{ $width }} px" tabindex="0"
                class="overflow-x-auto rounded-xl border border-rule bg-canvas">
                <iframe title="Shell a {{ $width }} px" data-srcdoc="{{ $shell }}"
                    data-demo="shell-{{ $width }}"
                    class="{{ $widthClass }} h-[44rem] max-w-none rounded-xl"></iframe>
            </div>
        </div>
    @endforeach
</section>
