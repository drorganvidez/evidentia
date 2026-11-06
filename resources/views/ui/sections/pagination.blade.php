{{-- Pagination: the window around the current page at the start, in the middle and at the end, a simple paginator and a single page --}}
@use('Illuminate\Pagination\LengthAwarePaginator')
@use('Illuminate\Pagination\Paginator')
@php
    $options = ['path' => route('ui.index'), 'pageName' => 'pagina', 'fragment' => 'ui-pagination'];
    $page = fn(int $current): LengthAwarePaginator => new LengthAwarePaginator(
        range(1, 10),
        128,
        10,
        $current,
        $options,
    );
    $simple = new Paginator(range(1, 11), 10, 2, $options);
    $single = new LengthAwarePaginator(range(1, 6), 6, 10, 1, $options);
@endphp
<section id="ui-pagination" class="glass grid grid-cols-[minmax(0,1fr)] gap-6 rounded-xl p-6 md:p-8"
    aria-labelledby="ui-pagination-title">
    <h2 id="ui-pagination-title" class="text-2xl font-bold tracking-tight">Paginación</h2>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-3 lg:grid-cols-2">
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">página 1 de 13 · summary</span>
            <x-ui.pagination :paginator="$page(1)" label="Paginación en la primera página"
                summary="Mostrando de la 1 a la 10 de 128 anotaciones" />
        </div>
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">página 7 de 13</span>
            <x-ui.pagination :paginator="$page(7)" label="Paginación en la página 7" />
        </div>
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">página 7 de 13 · window=2</span>
            <x-ui.pagination :paginator="$page(7)" label="Paginación con ventana de dos páginas" :window="2" />
        </div>
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">página 13 de 13</span>
            <x-ui.pagination :paginator="$page(13)" label="Paginación en la última página" />
        </div>
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">Paginator simple · página 2</span>
            <x-ui.pagination :paginator="$simple" label="Paginación simple" />
        </div>
        <div class="glass-2 grid gap-3 rounded-md p-4">
            <span class="font-mono text-2xs text-ink-muted">una sola página · summary</span>
            <x-ui.pagination :paginator="$single" label="Paginación de una sola página" summary="6 anotaciones" />
        </div>
    </div>
</section>
