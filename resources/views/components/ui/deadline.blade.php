{{--
    Deadline card of the sidebar, or its inline line under the page title: label, time left, consumed bar and closing date.
    <x-ui.deadline label="Subida de evidencias" :closes-at="$closesAt" :opens-at="$opensAt" href="{{ route('evidence.list') }}" />
    <x-ui.deadline label="Subida de evidencias" :closes-at="$closesAt" variant="inline" verb="para subir evidencias" />
    The server prints the whole text (it works without JS); deadline.js refreshes it every minute. The inline
    variant uses ink because it sits under the page title, on the bare canvas where the aurora drifts.
    The root tag is printed from PHP because blade-formatter does not follow dynamic tags.
--}}
@use('Illuminate\Support\Carbon')
@props([
    'label' => null,
    'closesAt' => null,
    'opensAt' => null,
    'variant' => 'card',
    'verb' => null,
    'warnDays' => 3,
    'href' => null,
])
@php
    throw_if(blank($label), InvalidArgumentException::class, 'The deadline needs a label.');
    $now = Carbon::now();
    $closes = $closesAt === null || $closesAt === '' ? null : Carbon::parse($closesAt)->locale('es');
    $opens = $opensAt === null || $opensAt === '' ? null : Carbon::parse($opensAt)->locale('es');
    $seconds = $closes === null ? null : $closes->getTimestamp() - $now->getTimestamp();
    // Same rounding as deadline.js: days when a day or more is left, then hours, then minutes
    $left = static function (int $seconds): string {
        if ($seconds >= 86400) {
            $n = (int) round($seconds / 86400);

            return $n . ($n === 1 ? ' día' : ' días');
        }
        if ($seconds >= 3600) {
            $n = (int) round($seconds / 3600);

            return $n . ($n === 1 ? ' hora' : ' horas');
        }
        $n = max(1, (int) round($seconds / 60));

        return $n . ($n === 1 ? ' minuto' : ' minutos');
    };
    [$state, $tone, $remaining] = match (true) {
        $seconds === null => ['none', 'neutral', 'Sin plazo'],
        $seconds <= 0 => ['closed', 'draft', 'Plazo cerrado'],
        default => ['open', $seconds < (int) $warnDays * 86400 ? 'rejected' : 'pending', $left($seconds)],
    };
    $percent = null;
    if ($closes !== null && $opens !== null && $opens->lessThan($closes)) {
        $elapsed = $now->getTimestamp() - $opens->getTimestamp();
        $percent = (int) round(max(0, min(100, ($elapsed * 100) / ($closes->getTimestamp() - $opens->getTimestamp()))));
    }
    $isLink = $href !== null;
    $tag = $variant === 'inline' ? 'p' : ($isLink ? 'a' : 'div');
    // One merge so the consumer class is appended to the default one and the attribute order stays stable
    $root = $attributes->merge(
        array_filter([
            'class' => Arr::toCssClasses([
                'glass-2 grid gap-2 rounded-lg p-4 text-sm' => $variant !== 'inline',
                'text-ink no-underline transition hover:-translate-y-0.5' => $variant !== 'inline' && $isLink,
                'flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink' => $variant === 'inline',
            ]),
            'href' => $variant !== 'inline' ? $href : null,
            'role' => $tag === 'a' ? null : 'note',
            'x-data' => 'deadline',
            'data-closes-at' => $closes?->toIso8601String(),
            'data-warn-days' => (string) (int) $warnDays,
        ]),
    );
    $badgeClasses =
        'data-[tone=pending]:bg-pending-soft data-[tone=pending]:text-pending-ink data-[tone=rejected]:bg-rejected-soft data-[tone=rejected]:text-rejected-ink data-[tone=draft]:bg-draft-soft data-[tone=draft]:text-draft-ink';
    $closesVerb = $state === 'closed' ? 'Cerró el' : 'Cierra el';
@endphp
{!! '<' . $tag . ' ' . $root . '>' !!}
@if ($variant === 'inline')
    <x-ui.badge size="sm" :tone="$tone" :dot="false" x-text="remaining" x-bind:data-tone="tone"
        :data-tone="$tone" :class="$badgeClasses">{{ $remaining }}</x-ui.badge>
    <span>{{ $verb ?? $label }}@if ($closes !== null)
            · <span x-text="closesVerbLower">{{ mb_strtolower($closesVerb) }}</span>
            <x-ui.time :value="$closes" format="absolute" pattern="d/m \a \l\a\s H:i" />
        @endif
    </span>
@else
    <span class="flex items-center justify-between gap-3 font-semibold">
        <span>{{ $label }}</span>
        <x-ui.badge size="sm" :tone="$tone" :dot="false" x-text="remaining" x-bind:data-tone="tone"
            :data-tone="$tone" :class="$badgeClasses">{{ $remaining }}</x-ui.badge>
    </span>
    @if ($percent !== null)
        <x-ui.meter gradient :segments="[['value' => $percent, 'tone' => 'brand']]" size="sm" />
    @endif
    @if ($closes !== null)
        <span class="text-ink-muted"><span x-text="closesVerb">{{ $closesVerb }}</span>
            <x-ui.time :value="$closes" format="absolute" pattern="d/m · H:i" /></span>
    @endif
@endif
{!! '</' . $tag . '>' !!}
