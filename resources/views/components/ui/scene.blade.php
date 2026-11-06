{{--
    Decorative scene of the public pages: pieces of the platform drawn with its components and tokens (an evidence with
    its status, the hours by origin, a meeting with its minutes, a bonus and committee chips). Made-up data, hidden from
    assistive technology and from print; nothing inside takes the focus.
    <x-ui.scene />                   beside the claim of the access page
    <x-ui.scene variant="sides" />   pieces without text in the margins of a one-column public page, from lg
    The hero follows its own width (container query): from 34rem the five pieces float slowly, without the reduced
    motion preference; narrower, as on the phone, only the evidence is left, as a deck.
--}}
@props(['variant' => 'hero'])
@php
    throw_unless(
        in_array($variant, ['hero', 'sides'], true),
        InvalidArgumentException::class,
        'The scene variant is hero or sides.',
    );
    $tile = 'grid shrink-0 place-items-center rounded-sm';
    $origins = [
        ['value' => 55, 'tone' => 'accepted', 'label' => 'Evidencias'],
        ['value' => 24, 'tone' => 'notice', 'label' => 'Reuniones'],
        ['value' => 14, 'tone' => 'pending', 'label' => 'Asistencia'],
        ['value' => 7, 'tone' => 'draft', 'label' => 'Bonos'],
    ];
@endphp
@if ($variant === 'hero')
    <div aria-hidden="true" data-scene="hero" {{ $attributes->class(['@container select-none print-hidden']) }}>
        <div
            class="grid grid-cols-[minmax(0,1fr)] gap-4 @min-[34rem]:grid-cols-[minmax(0,1.08fr)_minmax(0,1fr)] @min-[34rem]:gap-5">
            <div class="relative grid content-start gap-4 @max-[34rem]:mb-3">
                {{-- Narrow, two blank cards peek under the evidence, as a deck --}}
                <span
                    class="absolute inset-x-6 -bottom-3 top-6 rounded-lg glass-2 opacity-60 @min-[34rem]:hidden"></span>
                <span
                    class="absolute inset-x-3 -bottom-1.5 top-3 rounded-lg glass-2 opacity-85 @min-[34rem]:hidden"></span>

                <div
                    class="glass relative rounded-lg p-3.5 @min-[34rem]:-rotate-1 @min-[34rem]:motion-safe:animate-float">
                    <div class="flex items-start gap-3">
                        <span class="{{ $tile }} size-10 bg-brand-soft text-brand-soft-ink">
                            <x-lucide-file-text class="size-5" />
                        </span>
                        <span class="grid min-w-0 flex-1 gap-1">
                            <span class="flex items-center justify-between gap-2">
                                <span class="font-mono text-2xs text-ink-muted">EV-0142</span>
                                <x-ui.evidence-status status="ACCEPTED" size="sm" />
                            </span>
                            <span class="truncate text-md font-semibold text-ink">Taller de accesibilidad web</span>
                        </span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <x-ui.committee-chip name="Programa" :hue="270" />
                        <x-ui.hours :value="3.5" :decimals="1" size="sm" class="font-semibold text-ink" />
                    </div>
                </div>

                <div
                    class="glass rounded-lg p-3.5 @max-[34rem]:hidden @min-[34rem]:ml-10 @min-[34rem]:rotate-1 @min-[34rem]:motion-safe:animate-float [--float-delay:-3s] [--float-duration:7s]">
                    <div class="flex items-center gap-3">
                        <span class="{{ $tile }} size-10 bg-notice-soft text-notice-ink">
                            <x-lucide-handshake class="size-5" />
                        </span>
                        <span class="grid min-w-0 gap-0.5">
                            <span class="truncate text-md font-semibold text-ink">Reunión ordinaria 3</span>
                            <span class="truncate text-sm text-ink-muted">Comité de Comunicación</span>
                        </span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2 rounded-sm glass-2 px-3 py-1.5">
                        <span class="inline-flex items-center gap-2 text-sm font-medium text-ink">
                            <x-lucide-scroll-text class="size-4 text-ink-muted" />Acta
                        </span>
                        <x-ui.badge tone="accepted" size="sm">Firmada</x-ui.badge>
                    </div>
                </div>
            </div>

            <div class="grid content-start gap-4 @max-[34rem]:hidden">
                <div class="flex flex-wrap gap-2 @min-[34rem]:pl-3">
                    <x-ui.committee-chip name="Logística" :hue="30" />
                    <x-ui.committee-chip name="Igualdad" :hue="330" />
                </div>
                <div
                    class="glass rounded-lg p-3.5 @min-[34rem]:motion-safe:animate-float [--float-delay:-1.5s] [--float-duration:8s]">
                    <div class="mb-3 flex items-baseline justify-between gap-2">
                        <span class="text-sm font-semibold text-ink-muted">Mis horas</span>
                        <span class="font-mono text-2xl font-semibold tracking-tighter tabular-nums text-ink">14,5<span
                                class="ml-1 text-md font-medium text-ink-muted">h</span></span>
                    </div>
                    <x-ui.meter :segments="$origins" size="sm" />
                    <ul class="mt-3 grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-ink-muted">
                        @foreach ($origins as $origin)
                            <li class="flex items-center gap-1.5">
                                <x-ui.dot :tone="$origin['tone']" size="sm" />{{ $origin['label'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div
                    class="glass flex items-center gap-3 rounded-lg p-3 pr-4 @min-[34rem]:mr-4 @min-[34rem]:-rotate-1 @min-[34rem]:motion-safe:animate-float [--float-delay:-5s] [--float-duration:9s]">
                    <span
                        class="{{ $tile }} size-9 bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200">
                        <x-lucide-puzzle class="size-4.5" />
                    </span>
                    <span class="grid min-w-0 flex-1">
                        <span class="text-md font-semibold text-ink">Bono</span>
                        <span class="truncate text-sm text-ink-muted">Montaje del stand</span>
                    </span>
                    <span class="font-mono text-md font-semibold tabular-nums text-accepted-ink">+2 h</span>
                </div>
            </div>
        </div>
    </div>
@else
    @php
        $piece = 'glass absolute rounded-lg p-3 motion-safe:animate-float';
        $line = 'block h-2 rounded-full bg-rule';
    @endphp
    <div aria-hidden="true" data-scene="sides"
        {{ $attributes->class(['pointer-events-none fixed inset-0 -z-10 hidden overflow-hidden select-none print-hidden lg:block']) }}>
        {{-- An evidence, accepted --}}
        <div class="{{ $piece }} top-[22%] right-[calc(50%+19.5rem)] w-48 -rotate-6">
            <div class="flex items-center gap-2.5">
                <span class="{{ $tile }} size-8 bg-brand-soft text-brand-soft-ink">
                    <x-lucide-file-text class="size-4" />
                </span>
                <span class="grid flex-1 gap-1.5"><span class="{{ $line }} w-4/5"></span><span
                        class="{{ $line }} w-1/2"></span></span>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <span class="inline-flex h-5 w-18 items-center gap-1.5 rounded-full bg-accepted-soft px-2">
                    <x-ui.dot tone="accepted" size="sm" /><span
                        class="h-1.5 flex-1 rounded-full bg-accepted/40"></span>
                </span>
                <span class="h-2 w-8 rounded-full bg-rule"></span>
            </div>
        </div>

        {{-- The hours by origin --}}
        <div
            class="{{ $piece }} bottom-[18%] right-[calc(50%+20rem)] flex w-40 items-center gap-3 rotate-3 [--float-delay:-4s] [--float-duration:8s]">
            <x-ui.meter shape="ring" size="sm" :segments="$origins" class="shrink-0 [&_svg]:size-14" />
            <span class="grid flex-1 gap-2">
                @foreach ($origins as $origin)
                    <span class="flex items-center gap-1.5"><x-ui.dot :tone="$origin['tone']" size="sm" /><span
                            class="{{ $line }} flex-1"></span></span>
                @endforeach
            </span>
        </div>

        {{-- A meeting with its minutes signed --}}
        <div
            class="{{ $piece }} top-[26%] left-[calc(50%+19.5rem)] w-48 rotate-6 [--float-delay:-2s] [--float-duration:7s]">
            <div class="flex items-center gap-2.5">
                <span class="{{ $tile }} size-8 bg-notice-soft text-notice-ink">
                    <x-lucide-handshake class="size-4" />
                </span>
                <span class="grid flex-1 gap-1.5"><span class="{{ $line }} w-3/4"></span><span
                        class="{{ $line }} w-2/5"></span></span>
            </div>
            <div class="mt-3 flex items-center gap-2 rounded-sm glass-2 px-2 py-1.5">
                <x-lucide-scroll-text class="size-3.5 text-ink-muted" />
                <span class="{{ $line }} flex-1"></span>
                <x-ui.dot tone="accepted" size="sm" />
            </div>
        </div>

        {{-- A bonus and three committees --}}
        <div
            class="{{ $piece }} bottom-[20%] left-[calc(50%+20rem)] w-44 -rotate-3 [--float-delay:-5s] [--float-duration:9s]">
            <div class="flex items-center gap-2.5">
                <span class="{{ $tile }} size-8 bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-200">
                    <x-lucide-puzzle class="size-4" />
                </span>
                <span class="{{ $line }} flex-1"></span>
            </div>
            <div class="mt-3 flex gap-1.5">
                @foreach ([270, 30, 150] as $hue)
                    <span class="inline-flex flex-1 items-center gap-1 rounded-full glass-2 px-1.5 py-1">
                        <x-ui.dot :hue="$hue" size="sm" /><span
                            class="h-1.5 flex-1 rounded-full bg-rule"></span>
                    </span>
                @endforeach
            </div>
        </div>
    </div>
@endif
