{{-- The releases and the open issues of the repository, read from the API of GitHub --}}
@php
    $tones = ['bug' => 'rejected', 'enhancement' => 'accepted', 'question' => 'pending'];
@endphp
<x-layout.app title="Actualizaciones" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Actualizaciones']]">
    <x-ui.page-header title="Actualizaciones" />

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)] lg:gap-8">
        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            @forelse ($releases as $release)
                <x-ui.card :title="$release->name ?: $release->tag_name ?? 'Versión'" :level="2" :id="'release-' . $loop->index">
                    <x-slot:extra>
                        <x-ui.time :value="$release->published_at" format="relative" class="text-sm text-ink-muted" />
                    </x-slot:extra>
                    <div class="grid gap-4">
                        @if (filled($release->body))
                            <div class="prose-ui text-sm">{!! nl2br(e($release->body), false) !!}</div>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            <x-ui.button size="sm" icon="external-link" :href="$release->html_url">Ver en
                                GitHub</x-ui.button>
                            <x-ui.button size="sm" icon="download" :href="$release->zipball_url">Código fuente
                                (zip)
                            </x-ui.button>
                            <x-ui.button size="sm" icon="download" :href="$release->tarball_url">Código fuente
                                (tar.gz)</x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <x-ui.card as="div">
                    <x-ui.empty icon="package" title="Aún no hay versiones publicadas" :level="2" />
                </x-ui.card>
            @endforelse
        </div>

        <aside aria-label="Issues abiertas" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            <x-ui.card title="Issues abiertas" :level="2" id="issues">
                <x-slot:extra>
                    <x-ui.badge variant="count">{{ count($issues) }}</x-ui.badge>
                </x-slot:extra>
                @if (count($issues) === 0)
                    <x-ui.empty icon="circle-check" title="No hay issues abiertas" size="sm" :level="3" />
                @else
                    <ul class="divide-y divide-rule">
                        @foreach ($issues as $issue)
                            <li class="grid gap-1.5 py-3">
                                <a href="{{ $issue->html_url }}" target="_blank" rel="noopener noreferrer"
                                    class="font-semibold text-ink hover:text-brand-ink">{{ $issue->title }}<span
                                        class="sr-only">(se abre en una pestaña nueva)</span></a>
                                <span class="flex flex-wrap items-center gap-1.5 text-sm text-ink-muted">
                                    @foreach ($issue->labels ?? [] as $label)
                                        <x-ui.badge :tone="$tones[$label->name] ?? 'draft'" size="sm">{{ $label->name }}</x-ui.badge>
                                    @endforeach
                                    <x-ui.time :value="$issue->created_at" format="relative" />
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </aside>
    </div>
</x-layout.app>
