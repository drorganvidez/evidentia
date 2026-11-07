{{--
    Attached files: an icon by type, the name, a readable size, a button that shows it in a modal when the browser can
    (preview: a PDF in a frame, an image as it is, with a link to open it in a new tab) and a download button; an
    empty state without files.
    <x-ui.file-list :files="[['name' => 'informe-final.pdf', 'size' => 1258291, 'href' => '/files/1', 'type' => 'application/pdf']]" />
    <x-ui.file-list :files="$proofs" label="Pruebas de la evidencia" empty-text="No hay pruebas adjuntas" />
    A file is ['name', 'size' => bytes or null, 'href' => null, 'preview' => url or null, 'type' => mime type or
    extension, or null]. The
    controller builds them, for instance $proofs = $evidence->proofs->map(fn ($proof) => ['name' => $proof->file->name,
    'size' => $proof->file->size, 'href' => route('proof.download', $proof->id), 'type' => $proof->file->type]).
    files takes an array or a collection; an Arrayable item (a model) is read through toArray().
--}}
@use('Illuminate\Contracts\Support\Arrayable')
@props([
    'files' => [],
    'emptyText' => 'No hay pruebas adjuntas',
    'downloadLabel' => 'Descargar',
    'previewLabel' => 'Ver',
    'label' => 'Archivos adjuntos',
])
@php
    $files = collect($files)->map(fn($file) => $file instanceof Arrayable ? $file->toArray() : $file)->values()->all();
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
                $preview = $file['preview'] ?? null;
            @endphp
            <li class="glass-2 flex min-w-0 items-center gap-3 rounded-lg px-4 py-3 text-md">
                <x-ui.icon :name="$iconFor($name, $file['type'] ?? null)" class="size-5 text-ink-muted" />
                <span class="min-w-0 flex-1 truncate" title="{{ $name }}">{{ $name }}</span>
                @if ($size !== null)
                    <span
                        class="font-mono text-sm whitespace-nowrap text-ink-muted tabular-nums">{{ $size }}</span>
                @endif
                @if ($preview !== null)
                    @php
                        $previewId = 'preview-' . substr(md5($preview), 0, 12);
                        $type = Str::lower((string) ($file['type'] ?? ''));
                        $isPdf =
                            in_array($type, ['pdf', 'application/pdf'], true) ||
                            Str::endsWith(Str::lower($name), '.pdf');
                    @endphp
                    <x-ui.dialog :id="$previewId" :title="$name" size="xl" data-preview>
                        <x-slot:trigger>
                            <x-ui.button icon-only icon="eye" :label="$previewLabel . ' ' . $name" variant="ghost" size="sm"
                                aria-haspopup="dialog" aria-controls="{{ $previewId }}" x-on:click="show" />
                        </x-slot:trigger>
                        {{-- Lazy: the frame or the image loads when the modal opens, not with the page --}}
                        @if ($isPdf)
                            <iframe src="{{ $preview }}" title="{{ $name }}" loading="lazy"
                                class="block h-[70dvh] w-full rounded-lg border border-rule bg-white"></iframe>
                        @else
                            <img src="{{ $preview }}" alt="{{ $name }}" loading="lazy"
                                class="mx-auto block h-auto max-h-[70dvh] w-auto max-w-full rounded-lg">
                        @endif
                        <x-slot:footer>
                            <x-ui.button :href="$preview" target="_blank" rel="noopener" icon="external-link"
                                variant="quiet">Abrir en otra pestaña</x-ui.button>
                            @if ($href !== null)
                                <x-ui.button :href="$href" icon="download">{{ $downloadLabel }}</x-ui.button>
                            @endif
                        </x-slot:footer>
                    </x-ui.dialog>
                @endif
                @if ($href !== null)
                    <x-ui.button icon-only icon="download" :label="$downloadLabel . ' ' . $name" :href="$href" variant="ghost"
                        size="sm" />
                @endif
            </li>
        @endforeach
    </ul>
@endif
