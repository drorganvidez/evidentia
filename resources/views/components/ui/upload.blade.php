{{--
    Upload zone with a real <input type="file">, the list of files with their progress and a hidden input per uploaded
    file carrying the server id. Talks to the upload endpoints with XMLHttpRequest and the CSRF token of the layout.
    <x-ui.upload :url="route('upload.process')" :revert-url="route('upload.revert')"
        :remove-url="route('upload.remove', 'NAME')" :files="Filepond::getFilesFromTemporaryFolder()->all()" />
    <x-ui.upload name="import" :url="route('upload.process')" accept=".xls,.xlsx" :multiple="false"
        label="Arrastra aquí la hoja de cálculo o pulsa para elegirla" hint="Excel (.xls o .xlsx)" />
    files accepts plain names or {name, size, id}; the label is bold up to its first " o ". The remove url keeps the
    placeholder NAME for the file name. hint="" drops the hint and max-total="0" lifts the total limit. Without JS
    the chosen files travel in the file input under the same name. Inside a form the submit waits, and says so,
    while a file is still uploading.
--}}
@props([
    'name' => 'uploaded[]',
    'url' => '',
    'revertUrl' => null,
    'removeUrl' => null,
    'accept' => 'image/*,.pdf,.zip,.rar,.7z,.tar,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp',
    'multiple' => true,
    'maxFiles' => null,
    'maxSize' => 52428800,
    'maxTotal' => 209715200,
    'files' => [],
    'label' => 'Arrastra aquí el documento o pulsa para elegirlo',
    'hint' => 'PDF, imágenes o ZIP · hasta 50 MB por archivo',
    'error' => null,
    'id' => null,
    'capture' => null,
])
@php
    throw_if(blank($url), InvalidArgumentException::class, 'The upload component needs a url.');

    $id ??= ui_field_id($name);
    $errorKey = ui_error_key($name);
    // A prop set to null falls back to its default, so an empty hint is the way to drop it
    $hint = filled($hint) ? $hint : null;
    // Never assume $errors: the components also render through Blade::render
    $bag = $errors ?? null;
    if ($error === null && $bag instanceof \Illuminate\Support\ViewErrorBag) {
        $error = $bag->first($errorKey) ?: ($bag->first($errorKey . '.*') ?: null);
    }

    // Files already on the server: plain names or {name, size, id}
    $normalized = [];
    foreach ($files as $file) {
        $normalized[] = is_array($file)
            ? [
                'name' => (string) ($file['name'] ?? ''),
                'size' => isset($file['size']) ? (int) $file['size'] : null,
                'id' => isset($file['id']) ? (string) $file['id'] : null,
            ]
            : ['name' => (string) $file, 'size' => null, 'id' => null];
    }
    $config = [
        'name' => $name,
        'url' => $url,
        'revertUrl' => $revertUrl,
        'removeUrl' => $removeUrl,
        'accept' => $accept,
        'multiple' => (bool) $multiple,
        'maxFiles' => $maxFiles !== null ? (int) $maxFiles : null,
        'maxSize' => $maxSize !== null ? (int) $maxSize : null,
        'maxTotal' => $maxTotal !== null ? (int) $maxTotal : null,
    ];

    $parts = preg_split('/\s+o\s+/u', $label, 2);
    $labelStrong = $parts[0];
    $labelRest = isset($parts[1]) ? ' o ' . $parts[1] : '';
    $describedBy = implode(
        ' ',
        array_filter([$error !== null ? $id . '-error' : null, $hint !== null ? $id . '-hint' : null]),
    );
@endphp
<div x-data="upload" data-config="{{ json_encode($config, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) }}"
    data-files="{{ json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) }}"
    x-bind:data-state="dragging ? 'dragging' : 'idle'" x-bind:aria-busy="uploading ? 'true' : 'false'"
    x-on:dragenter.prevent="onDragEnter" x-on:dragover.prevent="onDragOver" x-on:dragleave="onDragLeave"
    x-on:drop.prevent="onDrop" {{ $attributes->class(['group grid grid-cols-[minmax(0,1fr)] gap-3']) }}>
    <label for="{{ $id }}"
        class="relative grid min-h-32 cursor-pointer content-center justify-items-center gap-2 rounded-lg border-[1.5px] border-dashed border-aurora-500/45 bg-aurora-500/6 px-4 py-6 text-center transition focus-within:ring-4 focus-within:ring-aurora-500/12 hover:bg-aurora-500/10 group-data-[state=dragging]:bg-aurora-500/10 group-data-[state=dragging]:ring-4 group-data-[state=dragging]:ring-aurora-500/12">
        <span aria-hidden="true" class="grid size-12 place-items-center rounded-md bg-brand-soft text-brand-soft-ink">
            <x-lucide-upload class="size-6" />
        </span>
        <span class="text-md"><strong>{{ $labelStrong }}</strong>{{ $labelRest }}</span>
        @if ($hint !== null)
            <span id="{{ $id }}-hint" class="text-md text-ink-muted">{{ $hint }}</span>
        @endif
        <input id="{{ $id }}" type="file" name="{{ $name }}"
            class="absolute inset-0 size-full cursor-pointer opacity-0" x-ref="input" x-on:change="onPick"
            @if ($multiple) multiple @endif
            @if ($accept !== null) accept="{{ $accept }}" @endif
            @if ($capture !== null) capture="{{ $capture }}" @endif
            @if ($error !== null) aria-invalid="true" @endif
            @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
    </label>
    <ul role="list" aria-label="Archivos" x-show="files.length" x-cloak class="grid grid-cols-[minmax(0,1fr)] gap-2">
        <template x-for="file in files" x-bind:key="file.key">
            <li
                class="glass-2 grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-3 rounded-lg px-4 py-3 text-md">
                <x-lucide-file-text class="size-5 text-ink-muted" aria-hidden="true" />
                <div class="min-w-0">
                    <span class="block truncate font-medium" x-text="file.name" x-bind:title="file.name"></span>
                    <span class="font-mono text-sm text-ink-muted tabular-nums" x-text="file.sizeLabel"></span>
                    <progress max="100" x-bind:value="file.progress" x-show="file.status === 'uploading'"
                        x-bind:aria-label="file.progressLabel"
                        class="mt-1 block h-1.5 w-full appearance-none overflow-hidden rounded-full [&::-webkit-progress-bar]:bg-rule [&::-webkit-progress-value]:bg-brand [&::-moz-progress-bar]:bg-brand"></progress>
                    <span class="block text-sm"
                        x-bind:class="file.status === 'error' ? 'text-rejected-ink' : 'text-ink-muted'"
                        x-text="file.statusLabel"></span>
                </div>
                <div class="flex gap-1">
                    <x-ui.button icon-only icon="rotate-ccw" variant="ghost" size="sm" label="Reintentar"
                        x-show="file.status === 'error'" x-on:click="retry(file.key)"
                        x-bind:aria-label="file.retryLabel" x-bind:title="file.retryLabel" />
                    <x-ui.button icon-only icon="x" variant="ghost" size="sm" label="Quitar"
                        x-on:click="remove(file.key)" x-bind:aria-label="file.removeLabel"
                        x-bind:title="file.removeLabel" />
                </div>
                <template x-if="file.status === 'done'">
                    <input type="hidden" x-bind:name="hiddenName" x-bind:value="file.serverId">
                </template>
            </li>
        </template>
    </ul>
    <p role="status" class="sr-only" x-text="summary"></p>
    <p role="alert" x-show="problem" x-cloak class="flex items-center gap-1.5 text-sm text-rejected-ink">
        <x-lucide-circle-alert class="size-4" aria-hidden="true" /><span x-text="problem"></span>
    </p>
    @if ($error !== null)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">
            <x-lucide-circle-alert class="size-4" aria-hidden="true" />{{ $error }}
        </p>
    @endif
</div>
