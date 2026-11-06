{{--
    Links to the pages of a Laravel paginator: numbers with a window around the current page (the first and the last
    always among them) for a LengthAwarePaginator, only previous and next for a simple Paginator. On the phone the
    numbers give way to «Página 7 de 13» between the first, previous, next and last links. With a single page only
    the summary prints. Same semantics as the tailwind view of Laravel, so it can become Paginator::defaultView later.
    <x-ui.pagination :paginator="$evidences" summary="Mostrando de la 21 a la 30 de 128" />
    <x-ui.pagination :paginator="$log" label="Paginación del registro" :window="2" />
--}}
@use('Illuminate\Contracts\Pagination\LengthAwarePaginator')
@use('Illuminate\Contracts\Pagination\Paginator')
@props(['paginator' => null, 'label' => 'Paginación', 'window' => 1, 'summary' => null])
@php
    throw_if(
        !$paginator instanceof Paginator,
        InvalidArgumentException::class,
        'The pagination component needs a paginator.',
    );
    $lengthAware = $paginator instanceof LengthAwarePaginator;
    $current = $paginator->currentPage();
    $last = $lengthAware ? $paginator->lastPage() : null;
    $position = $last !== null ? 'Página ' . $current . ' de ' . $last : 'Página ' . $current;
    $edgeClasses =
        'grid size-8 place-items-center rounded-full text-ink-muted no-underline transition hover:bg-glass-2 hover:text-ink md:hidden pointer-coarse:size-11';
    $edgeOffClasses = 'grid size-8 place-items-center rounded-full text-ink-faint md:hidden pointer-coarse:size-11';
    // Page numbers to print: the first, the last and the window around the current one; a single missing
    // page is printed in place of an ellipsis
    $elements = [];
    if ($lengthAware && $last > 1) {
        $window = max(0, (int) $window);
        $pages = [1, $last];
        for ($page = $current - $window; $page <= $current + $window; $page++) {
            if ($page >= 1 && $page <= $last) {
                $pages[] = $page;
            }
        }
        $pages = array_values(array_unique($pages));
        sort($pages);
        $previous = null;
        foreach ($pages as $page) {
            if ($previous !== null && $page - $previous === 2) {
                $elements[] = $previous + 1;
            } elseif ($previous !== null && $page - $previous > 2) {
                $elements[] = null;
            }
            $elements[] = $page;
            $previous = $page;
        }
    }
@endphp
@if (!$paginator->hasPages())
    @if ($summary !== null)
        <span {{ $attributes->class(['text-sm text-ink-muted']) }}>{{ $summary }}</span>
    @endif
@else
    <nav aria-label="{{ $label }}" {{ $attributes->class(['flex flex-wrap items-center gap-3']) }}>
        @if ($summary !== null)
            <span class="text-sm text-ink-muted max-md:hidden">{{ $summary }}</span>
        @endif
        @if ($lengthAware)
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="{{ $edgeOffClasses }}">
                    <x-lucide-chevrons-left aria-hidden="true" class="size-4" />
                </span>
            @else
                <a href="{{ $paginator->url(1) }}" aria-label="Primera página" title="Primera página"
                    class="{{ $edgeClasses }}">
                    <x-lucide-chevrons-left aria-hidden="true" class="size-4" />
                </a>
            @endif
        @endif
        @if ($paginator->onFirstPage())
            <span aria-disabled="true"
                class="grid size-8 place-items-center rounded-full text-ink-faint pointer-coarse:size-11">
                <x-lucide-chevron-left aria-hidden="true" class="size-4" />
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Página anterior"
                title="Página anterior"
                class="grid size-8 place-items-center rounded-full text-ink-muted no-underline transition hover:bg-glass-2 hover:text-ink pointer-coarse:size-11">
                <x-lucide-chevron-left aria-hidden="true" class="size-4" />
            </a>
        @endif
        @if ($elements !== [])
            <ul class="hidden gap-1 md:inline-flex">
                @foreach ($elements as $element)
                    @if ($element === null)
                        <li aria-hidden="true"
                            class="grid size-8 place-items-center text-ink-muted before:content-['…'] pointer-coarse:size-11">
                        </li>
                    @else
                        <li>
                            <a href="{{ $paginator->url($element) }}"
                                aria-label="{{ $element === $current ? 'Página ' . $element : 'Ir a la página ' . $element }}"
                                @if ($element === $current) aria-current="page" @endif
                                class="grid size-8 place-items-center rounded-full text-sm font-semibold text-ink-muted no-underline transition current:bg-brand-soft current:text-brand-soft-ink hover:bg-glass-2 hover:text-ink pointer-coarse:size-11">{{ $element }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
        <span @class([
            'font-mono text-sm text-ink-muted tabular-nums',
            'md:hidden' => $elements !== [],
        ])>{{ $position }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Página siguiente"
                title="Página siguiente"
                class="grid size-8 place-items-center rounded-full text-ink-muted no-underline transition hover:bg-glass-2 hover:text-ink pointer-coarse:size-11">
                <x-lucide-chevron-right aria-hidden="true" class="size-4" />
            </a>
        @else
            <span aria-disabled="true"
                class="grid size-8 place-items-center rounded-full text-ink-faint pointer-coarse:size-11">
                <x-lucide-chevron-right aria-hidden="true" class="size-4" />
            </span>
        @endif
        @if ($lengthAware)
            @if ($current >= $last)
                <span aria-disabled="true" class="{{ $edgeOffClasses }}">
                    <x-lucide-chevrons-right aria-hidden="true" class="size-4" />
                </span>
            @else
                <a href="{{ $paginator->url($last) }}" aria-label="Última página" title="Última página"
                    class="{{ $edgeClasses }}">
                    <x-lucide-chevrons-right aria-hidden="true" class="size-4" />
                </a>
            @endif
        @endif
    </nav>
@endif
