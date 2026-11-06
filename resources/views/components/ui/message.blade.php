{{--
    One message of a thread: the author with their role, the time, an optional status badge and the body in plain
    text (the slot, with its line breaks). html is reserved for the purified body of the stored messages, which
    rich-text will print in a later phase; until then only the slot is shown.
    <x-ui.message :author="['name' => 'Lucía', 'surname' => 'Pérez', 'role' => 'como estudiante', 'href' => '/usuarios/4']" :at="$message->created_at" status="open">{{ $message->body }}</x-ui.message>
    <x-ui.message :author="$author" :at="$at" mine>Acabo de adjuntar las fotos.</x-ui.message>
--}}
@props(['author' => [], 'at' => null, 'mine' => false, 'html' => null, 'status' => null])
@php
    throw_if(
        blank($author['name'] ?? null) || $at === null,
        InvalidArgumentException::class,
        'The message component needs an author with a name and a time.',
    );
    $name = trim((string) $author['name']);
    $surname = trim((string) ($author['surname'] ?? ''));
    $fullName = trim($name . ' ' . $surname);
    [$tone, $text] = match ($status) {
        'open' => ['pending', 'Abierto'],
        'resolved' => ['accepted', 'Resuelto'],
        default => [null, null],
    };
@endphp
<article aria-label="Mensaje de {{ $fullName }}"
    {{ $attributes->class(['glass-2 grid gap-3 rounded-lg p-4 text-md', 'border-l-2 border-l-brand/40' => $mine]) }}>
    <header class="flex flex-wrap items-center justify-between gap-2">
        <x-ui.person :name="$name" :surname="$surname" :role="$author['role'] ?? null" :href="$author['href'] ?? null" :src="$author['src'] ?? null"
            size="sm" />
        <span class="flex flex-wrap items-center gap-2">
            @if ($tone !== null)
                <x-ui.badge :tone="$tone" size="sm">{{ $text }}</x-ui.badge>
            @endif
            <x-ui.time :value="$at" inline short class="text-xs text-ink-muted" />
        </span>
    </header>
    <p class="whitespace-pre-line">{!! trim($slot->toHtml()) !!}</p>
</article>
