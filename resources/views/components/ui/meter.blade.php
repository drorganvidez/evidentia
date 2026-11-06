{{--
    Stacked bar or ring of percentages coloured by status tone, without a single inline style.
    <x-ui.meter :segments="[['value' => 68, 'tone' => 'brand']]" gradient label="68 % del plazo consumido" />
    <x-ui.meter :segments="$byType" legend label="Horas computadas: 4,00 en evidencias, 2,50 en reuniones" />
    <x-ui.meter shape="ring" :segments="$byStatus" value="48 %" label="48 % de las horas declaradas aceptadas" />
    A segment is ['value' => 0..100, 'tone' => accepted|pending|rejected|draft|notice|brand, 'label' => null, 'text' => null];
    the legend prints the label with its text, or the percentage when there is no text. segments takes an array or a
    collection; an Arrayable segment is read through toArray().
--}}
@use('Illuminate\Contracts\Support\Arrayable')
@props([
    'segments' => [],
    'shape' => 'bar',
    'label' => null,
    'size' => 'md',
    'track' => true,
    'gradient' => false,
    'value' => null,
    'legend' => false,
])
@php
    $segments = collect($segments)
        ->map(fn($segment) => $segment instanceof Arrayable ? $segment->toArray() : $segment)
        ->values()
        ->all();
    // Every value is clamped to 0..100 and the whole is scaled down when the sum exceeds 100; the
    // rounding remainder goes to the largest fractions so the integer widths add up exactly.
    $raw = array_map(fn(array $segment): float => max(0.0, min(100.0, (float) ($segment['value'] ?? 0))), $segments);
    $sum = array_sum($raw);
    $scaled = $sum > 100 ? array_map(fn(float $v): float => ($v * 100) / $sum, $raw) : $raw;
    $values = array_map(fn(float $v): int => (int) floor($v), $scaled);
    $fractions = array_map(fn(float $v): float => $v - floor($v), $scaled);
    arsort($fractions);
    $remainder = (int) round(min($sum, 100)) - array_sum($values);
    foreach (array_keys($fractions) as $index) {
        if ($remainder <= 0) {
            break;
        }
        $values[$index]++;
        $remainder--;
    }
    $tones = array_map(fn(array $segment): string => (string) ($segment['tone'] ?? 'brand'), $segments);
    $fill = fn(string $tone): string => match ($tone) {
        'accepted' => 'bg-accepted',
        'pending' => 'bg-pending',
        'rejected' => 'bg-rejected',
        'draft' => 'bg-draft',
        'notice' => 'bg-notice',
        default => 'bg-brand',
    };
    $stroke = fn(string $tone): string => match ($tone) {
        'accepted' => 'stroke-accepted',
        'pending' => 'stroke-pending',
        'rejected' => 'stroke-rejected',
        'draft' => 'stroke-draft',
        'notice' => 'stroke-notice',
        default => 'stroke-brand',
    };
    // With a label the meter is an image; without one the text next to it carries the meaning.
    $aria = $label !== null ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true'];
@endphp
@if ($shape === 'ring')
    @php
        $offset = 0;
    @endphp
    <span
        {{ $attributes->class(['relative grid place-items-center drop-shadow-[0_6px_14px_var(--glow)] print:drop-shadow-none']) }}>
        <svg viewBox="0 0 100 100" class="{{ $size === 'sm' ? 'size-19' : 'size-23' }}"
            {{ $attributes->only([])->merge($aria) }}>
            @if ($track)
                <circle r="42" cx="50" cy="50" fill="none" stroke-width="15" class="stroke-rule" />
            @endif
            @foreach ($values as $index => $v)
                @if ($v > 0)
                    <circle r="42" cx="50" cy="50" fill="none" stroke-width="15" pathLength="100"
                        stroke-dasharray="{{ $v }} {{ 100 - $v }}"
                        stroke-dashoffset="{{ -$offset }}" transform="rotate(-90 50 50)"
                        class="{{ $stroke($tones[$index]) }}" />
                @endif
                @php
                    $offset += $v;
                @endphp
            @endforeach
        </svg>
        @if ($value !== null)
            <span aria-hidden="true"
                class="absolute font-mono font-bold tracking-tighter tabular-nums {{ $size === 'sm' ? 'text-sm' : 'text-base' }}">{{ $value }}</span>
        @endif
    </span>
@else
    @php
        $bar = ($legend ? $attributes->only([]) : $attributes)
            ->class(['flex gap-1 overflow-hidden rounded-full', $size === 'sm' ? 'h-1.5' : 'h-3', 'bg-rule' => $track])
            ->merge($aria);
        $single = count($values) === 1;
    @endphp
    @if ($legend)
        <div {{ $attributes->class(['grid gap-2']) }}>
            <div {{ $bar }}>
                @foreach ($values as $index => $v)
                    @if ($v > 0)
                        <span
                            class="block h-full shrink-0 rounded-full w-{{ $v }}/100 {{ $single && $gradient ? 'bg-linear-to-r from-aurora-500 to-cyan-400 shadow-glow-sm' : $fill($tones[$index]) }}"></span>
                    @endif
                @endforeach
            </div>
            <ul class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
                @foreach ($segments as $index => $segment)
                    <li class="inline-flex items-center gap-1.5 text-ink-muted">
                        <span aria-hidden="true"
                            class="size-2 shrink-0 rounded-full {{ $fill($tones[$index]) }}"></span>
                        {{ $segment['label'] ?? '' }}
                        <span
                            class="font-mono font-semibold tabular-nums whitespace-nowrap text-ink">{{ $segment['text'] ?? $values[$index] . ' %' }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div {{ $bar }}>
            @foreach ($values as $index => $v)
                @if ($v > 0)
                    <span
                        class="block h-full shrink-0 rounded-full w-{{ $v }}/100 {{ $single && $gradient ? 'bg-linear-to-r from-aurora-500 to-cyan-400 shadow-glow-sm' : $fill($tones[$index]) }}"></span>
                @endif
            @endforeach
        </div>
    @endif
@endif
