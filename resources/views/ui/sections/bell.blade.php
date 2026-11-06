{{-- The bell of the topbar without unread notices, with three and past ninety-nine --}}
@php
    $here = route('ui.index') . '#ui-bell';
@endphp
<section id="ui-bell" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-bell-title">
    <h2 id="ui-bell-title" class="text-2xl font-bold tracking-tight">Campana</h2>

    <ul class="flex flex-wrap items-center gap-8">
        @foreach ([0, 3, 120] as $count)
            <li class="grid justify-items-center gap-2" data-demo="count-{{ $count }}">
                <x-ui.bell :count="$count" :href="$here" />
                <span class="font-mono text-2xs text-ink-muted">count={{ $count }}</span>
            </li>
        @endforeach
        <li class="grid justify-items-center gap-2 rounded-lg bg-slate-950 p-3" data-demo="dark" data-theme="dark">
            <x-ui.bell :count="7" :href="$here" class="text-slate-300 hover:text-slate-100" />
            <span class="font-mono text-2xs text-slate-300">oscuro</span>
        </li>
    </ul>
</section>
