{{-- Server errors for the lecturers: open, resolved or all, newest first, searchable by the code of the error page --}}
@php
    $base = route('lecture.errors');
    $tabs = collect(['abiertos' => 'Abiertos', 'resueltos' => 'Resueltos', 'todos' => 'Todos'])
        ->map(
            fn(string $label, string $key) => [
                'label' => $label,
                'href' => $base . '?estado=' . $key,
                'count' => $counts[$key],
                'current' => $state === $key,
            ],
        )
        ->values()
        ->all();
@endphp
<x-layout.app title="Errores" :breadcrumbs="[['label' => 'Inicio', 'href' => route('home')], ['label' => 'Errores']]">
    <x-ui.page-header title="Errores" />

    <x-ui.table caption="Errores del servidor" :columns="[
        ['label' => 'Error', 'key' => 'error'],
        ['label' => 'Veces', 'key' => 'count', 'align' => 'end'],
        ['label' => 'Última vez', 'key' => 'last'],
        ['label' => 'Estado', 'key' => 'state'],
    ]">
        <x-slot:toolbar>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <x-ui.tabs label="Filtrar por estado" :items="$tabs" />
                <x-ui.form method="get" :action="$base" :summary="false" busy-label="Buscando" id="error-code-form"
                    class="w-full md:w-72">
                    <input type="hidden" name="estado" value="todos">
                    <x-ui.field name="codigo" label="Código de la página de error" type="search" :value="$code"
                        placeholder="Código de la página de error" hide-label icon="search" autocomplete="off" />
                </x-ui.form>
            </div>
        </x-slot:toolbar>
        @foreach ($reports as $report)
            <x-ui.table.row :href="route('lecture.errors.show', $report->id)">
                <x-ui.table.cell label="Error" primary>
                    <a href="{{ route('lecture.errors.show', $report->id) }}">{{ $report->shortException() }}</a>
                    <span class="block text-sm text-ink-muted">{{ Str::limit($report->message, 120) }}</span>
                </x-ui.table.cell>
                <x-ui.table.cell label="Veces" numeric>{{ $report->occurrences }}</x-ui.table.cell>
                <x-ui.table.cell label="Última vez" meta><x-ui.time :value="$report->last_seen_at" inline short /></x-ui.table.cell>
                <x-ui.table.cell label="Estado">
                    @if ($report->resolved_at)
                        <x-ui.badge tone="accepted" size="sm">Resuelto</x-ui.badge>
                    @else
                        <x-ui.badge tone="rejected" size="sm">Abierto</x-ui.badge>
                    @endif
                </x-ui.table.cell>
            </x-ui.table.row>
        @endforeach
        @if ($reports->isEmpty())
            <x-slot:empty>
                <x-ui.empty icon="circle-check" :title="$code !== '' ? 'Ningún error con ese código' : 'Sin errores'" size="sm" />
            </x-slot:empty>
        @endif
        @if ($reports->hasPages())
            <x-slot:footer>
                <x-ui.pagination :paginator="$reports" label="Páginas de errores" />
            </x-slot:footer>
        @endif
    </x-ui.table>
</x-layout.app>
