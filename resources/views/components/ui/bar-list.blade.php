{{--
    Ordered list of bars, already sorted by the server: a label (a link with href), a figure and a bar relative to max.
    <x-ui.bar-list label="Evidencias pendientes por comité" :items="[
        ['label' => 'Logística', 'value' => 12, 'href' => '/comites/7', 'hue' => 30],
        ['label' => 'Programa', 'value' => 9, 'hue' => 270],
    ]" />
    <x-ui.bar-list label="Horas por tipo" :items="$byType" value-format="hours" :max="20" />
    An item is ['label', 'value', 'text' => null, 'href' => null, 'tone' => null, 'hue' => null]; the bar is
    decorative and takes the tone colour, the dot the hue of the committee. items takes an array or a collection; an
    Arrayable item is read through toArray().
--}}
@use('Illuminate\Contracts\Support\Arrayable')
@props(['items' => [], 'max' => null, 'label' => null, 'valueFormat' => 'int'])
@php
    throw_if(blank($label), InvalidArgumentException::class, 'The bar-list component needs a label.');
    $items = collect($items)->map(fn($item) => $item instanceof Arrayable ? $item->toArray() : $item)->values()->all();
    $values = array_map(fn(array $item): float => max(0.0, (float) ($item['value'] ?? 0)), $items);
    $max = $max !== null ? max(0.0, (float) $max) : ($values === [] ? 0.0 : max($values));
    $format = fn(float $value): string => $valueFormat === 'hours'
        ? number_format($value, 2, ',', '.') . ' h'
        : number_format($value, 0, ',', '.');
    $fill = fn(?string $tone): string => match ($tone) {
        'accepted' => 'bg-accepted',
        'pending' => 'bg-pending',
        'rejected' => 'bg-rejected',
        'draft' => 'bg-draft',
        'notice' => 'bg-notice',
        default => 'bg-brand',
    };
@endphp
<ol aria-label="{{ $label }}" {{ $attributes->class(['grid gap-3']) }}>
    @foreach ($items as $index => $item)
        @php
            $width = $max > 0 ? (int) round((min($values[$index], $max) / $max) * 100) : 0;
            $hue = $item['hue'] ?? null;
            $tone = $item['tone'] ?? null;
            $href = $item['href'] ?? null;
        @endphp
        <li class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1.5 text-md">
            @if ($href !== null)
                <a href="{{ $href }}"
                    class="flex min-w-0 items-center gap-2 font-medium text-ink no-underline transition hover:text-brand-ink">
                    @if ($hue !== null || $tone !== null)
                        <x-ui.dot :hue="$hue" :tone="$tone ?? 'draft'" />
                    @endif
                    <span class="truncate">{{ $item['label'] ?? '' }}</span>
                </a>
            @else
                <span class="flex min-w-0 items-center gap-2 font-medium text-ink">
                    @if ($hue !== null || $tone !== null)
                        <x-ui.dot :hue="$hue" :tone="$tone ?? 'draft'" />
                    @endif
                    <span class="truncate">{{ $item['label'] ?? '' }}</span>
                </span>
            @endif
            <span class="font-mono font-semibold tabular-nums">{{ $item['text'] ?? $format($values[$index]) }}</span>
            <span class="col-span-full h-2 overflow-hidden rounded-full bg-rule" aria-hidden="true">
                <span class="block h-full rounded-full {{ $fill($tone) }} w-{{ $width }}/100"></span>
            </span>
        </li>
    @endforeach
</ol>
