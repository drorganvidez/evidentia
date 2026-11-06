{{-- One server error: what, where, how many times, its latest occurrences with the code each person saw, and its trace --}}
<x-layout.app :title="$report->shortException()" :nav-current="$prefix . '.errors'" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Errores', 'href' => route($prefix . '.errors')],
    ['label' => $report->shortException()],
]">
    <x-ui.page-header :title="$report->shortException()">
        <x-slot:eyebrow>
            @if ($report->resolved_at)
                <x-ui.badge tone="accepted">Resuelto</x-ui.badge>
            @else
                <x-ui.badge tone="rejected">Abierto</x-ui.badge>
            @endif
            <x-ui.badge variant="chip" mono>{{ $report->occurrences }}
                {{ $report->occurrences === 1 ? 'vez' : 'veces' }}</x-ui.badge>
        </x-slot:eyebrow>
        <x-slot:actions>
            @if ($report->resolved_at)
                <form method="post" action="{{ route($prefix . '.errors.reopen', $report->id) }}">
                    @csrf
                    <x-ui.button type="submit" icon="rotate-ccw">Reabrir</x-ui.button>
                </form>
            @else
                <form method="post" action="{{ route($prefix . '.errors.resolve', $report->id) }}">
                    @csrf
                    <x-ui.button type="submit" variant="primary" icon="circle-check">Marcar como resuelto</x-ui.button>
                </form>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Mensaje" :level="2">
                <p class="break-words">{{ $report->message ?: 'Sin mensaje' }}</p>
            </x-ui.card>

            <x-ui.card title="Últimas veces" :level="2">
                <ul class="grid gap-3">
                    @foreach ($report->latestOccurrences as $occurrence)
                        <li class="grid gap-1 border-b border-rule pb-3 last:border-0 last:pb-0">
                            <span class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <x-ui.badge variant="chip" mono>{{ $occurrence->code() }}</x-ui.badge>
                                <x-ui.time :value="$occurrence->occurred_at" inline short />
                                @if ($occurrence->user_name)
                                    <span class="text-sm">{{ $occurrence->user_name }}</span>
                                @endif
                            </span>
                            <span class="font-mono text-xs break-all text-ink-muted">{{ $occurrence->method }}
                                {{ $occurrence->url }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <x-ui.card title="Traza" :level="2">
                <pre tabindex="0" role="region" aria-label="Traza del error"
                    class="overflow-x-auto font-mono text-xs leading-relaxed whitespace-pre text-ink-muted">{{ $report->trace }}</pre>
            </x-ui.card>
        </div>

        <aside aria-label="Datos del error" class="grid content-start gap-6">
            <x-ui.card title="Datos" :level="2">
                <x-ui.dl>
                    <x-ui.dl.item term="Clase" mono><span
                            class="break-all">{{ $report->exception }}</span></x-ui.dl.item>
                    <x-ui.dl.item term="Fichero" mono><span
                            class="break-all">{{ $report->relativeFile() }}:{{ $report->line }}</span></x-ui.dl.item>
                    <x-ui.dl.item term="Primera vez"><x-ui.time :value="$report->first_seen_at" inline short /></x-ui.dl.item>
                    <x-ui.dl.item term="Última vez"><x-ui.time :value="$report->last_seen_at" inline short /></x-ui.dl.item>
                    @if ($report->resolved_at)
                        <x-ui.dl.item term="Resuelto"><x-ui.time :value="$report->resolved_at" inline short /></x-ui.dl.item>
                    @endif
                </x-ui.dl>
            </x-ui.card>
        </aside>
    </div>
</x-layout.app>
