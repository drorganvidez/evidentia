{{--
    Attached files: an icon by type, the name, a readable size and a download button; an empty state without files.
    <x-ui.file-list :files="[['name' => 'informe-final.pdf', 'size' => 1258291, 'href' => '/files/1', 'type' => 'application/pdf']]" />
    <x-ui.file-list :files="$evidence->files" label="Pruebas de la evidencia" empty-text="No hay pruebas adjuntas" />
    A file is ['name', 'size' => bytes or null, 'href' => null, 'type' => mime type or extension, or null].
--}}
@props([
    'files' => [],
    'emptyText' => 'No hay pruebas adjuntas',
    'downloadLabel' => 'Descargar',
    'label' => 'Archivos adjuntos',
])
@php
    $files = array_values($files);
    $human = function (int|float|string|null $bytes): ?string {
        if ($bytes === null || $bytes === '') {
            return null;
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = max(0.0, (float) $bytes);
        $unit = 0;
        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }
        $decimals = $unit > 0 && $value < 10 && round($value, 1) !== round($value) ? 1 : 0;

        return number_format($value, $decimals, ',', '.') . ' ' . $units[$unit];
    };
    $iconFor = function (string $name, ?string $type): string {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $type = strtolower((string) $type);
        $images = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'avif', 'heic', 'bmp'];
        $archives = ['zip', 'rar', '7z', 'tar', 'gz', 'tgz', 'bz2'];

        return match (true) {
            str_starts_with($type, 'image/'),
            in_array($extension, $images, true),
            in_array($type, $images, true)
                => 'image',
            in_array($extension, $archives, true),
            in_array($type, $archives, true),
            str_contains($type, 'zip'),
            str_contains($type, 'compressed'),
            str_contains($type, 'x-tar')
                => 'file-archive',
            default => 'file-text',
        };
    };
@endphp
@if ($files === [])
    <x-ui.empty icon="paperclip" :title="$emptyText" size="sm" :level="3" {{ $attributes }} />
@else
    <ul aria-label="{{ $label }}" {{ $attributes->class(['grid gap-2']) }}>
        @foreach ($files as $file)
            @php
                $name = (string) ($file['name'] ?? '');
                $size = $human($file['size'] ?? null);
                $href = $file['href'] ?? null;
            @endphp
            <li class="glass-2 flex min-w-0 items-center gap-3 rounded-lg px-4 py-3 text-md">
                <x-ui.icon :name="$iconFor($name, $file['type'] ?? null)" class="size-5 text-ink-muted" />
                <span class="min-w-0 flex-1 truncate" title="{{ $name }}">{{ $name }}</span>
                @if ($size !== null)
                    <span
                        class="font-mono text-sm whitespace-nowrap text-ink-muted tabular-nums">{{ $size }}</span>
                @endif
                @if ($href !== null)
                    <x-ui.button icon-only icon="download" :label="$downloadLabel . ' ' . $name" :href="$href" variant="ghost"
                        size="sm" />
                @endif
            </li>
        @endforeach
    </ul>
@endif
