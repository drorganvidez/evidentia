{{--
    Breadcrumbs of the topbar: the last item is the current page, the previous ones link back and hide on the phone.
    <x-ui.breadcrumbs :items="[['label' => 'Inicio', 'href' => '/'], ['label' => 'Mis evidencias', 'href' => '/evidencias'], ['label' => 'Todas']]" />
--}}
@props(['items' => [], 'label' => 'Migas de pan'])
@php
    $items = array_values($items);
    $last = count($items) - 1;
@endphp
<nav aria-label="{{ $label }}" {{ $attributes->class(['min-w-0']) }}>
    <ol class="flex items-center gap-2 overflow-hidden text-md font-medium whitespace-nowrap text-ink-muted">
        @foreach ($items as $index => $item)
            @if ($index === $last)
                <li aria-current="page" class="truncate font-semibold text-ink">{{ $item['label'] }}</li>
            @else
                <li class="hidden items-center gap-2 md:inline-flex">
                    <a href="{{ $item['href'] }}"
                        class="inline-flex min-h-6 items-center text-ink-muted no-underline transition hover:text-ink">{{ $item['label'] }}</a>
                    <x-lucide-chevron-right class="size-4 opacity-60" aria-hidden="true" />
                </li>
            @endif
        @endforeach
    </ol>
</nav>
