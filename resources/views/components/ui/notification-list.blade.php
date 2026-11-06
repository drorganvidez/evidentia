{{--
    Notices of the user, the unread ones highlighted: a dot by tone, the title (a link with href), a text and the time.
    The unread state is also a hidden text, so the colour never stands alone.
    <x-ui.notification-list :items="[
        ['title' => 'Evidencia rechazada', 'text' => 'Montaje del photocall', 'at' => $at, 'href' => '/evidencias/33', 'unread' => true, 'tone' => 'rejected'],
    ]" />
    <x-ui.notification-list :items="[]" empty-text="No tienes avisos" />
    items takes an array or a collection; an Arrayable item is read through toArray().
--}}
@use('Illuminate\Contracts\Support\Arrayable')
@props(['items' => [], 'label' => 'Avisos', 'emptyText' => 'No tienes avisos'])
@php
    $items = collect($items)->map(fn($item) => $item instanceof Arrayable ? $item->toArray() : $item)->values()->all();
@endphp
@if ($items === [])
    <x-ui.empty icon="bell" :title="$emptyText" size="sm" :level="3" {{ $attributes }} />
@else
    <ol aria-label="{{ $label }}" {{ $attributes->class(['grid gap-2']) }}>
        @foreach ($items as $item)
            @php
                $unread = !empty($item['unread']);
                $href = $item['href'] ?? null;
            @endphp
            <li class="glass-2 grid grid-cols-[auto_minmax(0,1fr)] gap-3 rounded-lg px-4 py-3 text-md data-[unread]:bg-brand-soft/40"
                @if ($unread) data-unread @endif>
                <x-ui.dot :tone="$item['tone'] ?? 'draft'" class="mt-1.5" />
                <div class="min-w-0">
                    @if ($href !== null)
                        <a href="{{ $href }}"
                            class="font-semibold text-ink no-underline transition hover:text-brand-ink">
                            {{ $item['title'] ?? '' }}
                            @if ($unread)
                                <span class="sr-only">, sin leer</span>
                            @endif
                        </a>
                    @else
                        <span class="font-semibold text-ink">
                            {{ $item['title'] ?? '' }}
                            @if ($unread)
                                <span class="sr-only">, sin leer</span>
                            @endif
                        </span>
                    @endif
                    @if (!empty($item['text']))
                        <p class="text-sm text-ink-muted">{{ $item['text'] }}</p>
                    @endif
                    @if (!empty($item['at']))
                        <x-ui.time :value="$item['at']" inline short class="mt-1 block text-xs text-ink-muted" />
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
@endif
