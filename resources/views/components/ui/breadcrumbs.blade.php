{{--
    Breadcrumbs of the topbar: the last item is the current page, the previous ones link back and hide on the phone.
    <x-ui.breadcrumbs :items="[['label' => 'Inicio', 'href' => '/'], ['label' => 'Mis evidencias', 'href' => '/evidencias'], ['label' => 'Todas']]" />
    Every item shrinks with an ellipsis. The current one, which repeats the h1, takes the room the links leave (flex-1)
    and never drops below a readable minimum; only then the links shrink. Below lg only the parent comes before it.
--}}
@props(['items' => [], 'label' => 'Migas de pan'])
@php
    $items = array_values($items);
    $last = count($items) - 1;
    $current = 'min-w-16 flex-1 truncate font-semibold text-ink';
@endphp
<nav aria-label="{{ $label }}" {{ $attributes->class(['min-w-0']) }}>
    <ol class="flex items-center gap-2 overflow-hidden text-md font-medium whitespace-nowrap text-ink-muted">
        @foreach ($items as $index => $item)
            @if ($index === $last)
                <li aria-current="page" class="{{ $current }}">{{ $item['label'] }}</li>
            @else
                <li @class([
                    'hidden min-w-0 items-center gap-2',
                    $index === $last - 1 ? 'md:inline-flex' : 'lg:inline-flex',
                ])>
                    <a href="{{ $item['href'] }}"
                        class="inline-flex min-h-6 min-w-0 items-center text-ink-muted no-underline transition hover:text-ink"><span
                            class="truncate">{{ $item['label'] }}</span></a>
                    <x-lucide-chevron-right class="size-4 shrink-0 opacity-60" aria-hidden="true" />
                </li>
            @endif
        @endforeach
    </ol>
</nav>
