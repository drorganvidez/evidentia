{{--
    Back link of the topbar: one step up to the parent of the current page, or nothing on a page of the menu.
    <x-ui.breadcrumbs :items="[['label' => 'Inicio', 'href' => '/'], ['label' => 'Mis evidencias', 'href' => '/evidencias'], ['label' => 'Montaje del photocall']]" />
    renders «← Mis evidencias». The first item (Inicio) is the first entry of the menu and the last one repeats the h1,
    so a full trail only showed what the sidebar and the title already say. Only the nearest item in between with a
    link stays. items may be an array or a collection (of arrays or Arrayable items). The label shrinks with an
    ellipsis and under a coarse pointer the link measures 44 px.
--}}
@props(['items' => []])
@php
    // An array, a collection or anything Arrayable, with each item as an array
    $items = collect($items)
        ->map(fn($item) => $item instanceof \Illuminate\Contracts\Support\Arrayable ? $item->toArray() : $item)
        ->values();
    $parent = $items
        ->slice(1, -1)
        ->filter(fn($item) => filled($item['href'] ?? null))
        ->last();
@endphp
<div {{ $attributes->class(['min-w-0']) }}>
    @if ($parent !== null)
        <a href="{{ $parent['href'] }}" data-back
            class="-ml-1 inline-flex min-h-9 max-w-full min-w-0 items-center gap-1.5 rounded-full pr-3 pl-2 text-md font-medium text-ink-muted no-underline transition hover:bg-glass-2 hover:text-ink pointer-coarse:min-h-11">
            <x-lucide-arrow-left class="size-4 shrink-0" aria-hidden="true" />
            <span class="truncate"><span class="sr-only">Volver a </span>{{ $parent['label'] }}</span>
        </a>
    @endif
</div>
