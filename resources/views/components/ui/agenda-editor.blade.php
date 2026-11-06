{{--
    Editable agenda (orden del día): points to add, reorder and remove, serialised in a hidden input as the JSON array of
    strings the meeting request form reads from points_list. No drag and drop: buttons and Alt+arrows reorder.
    <x-ui.agenda-editor :points="session('points', [])" />
    <x-ui.agenda-editor :points="$points" hint="Un punto por línea" :error="$errors->first('points_list')" :max="20" />
    The points may be strings or anything with a point or title key. The slot renders inside the Alpine scope.
--}}
@props([
    'name' => 'points_list',
    'label' => 'Orden del día',
    'points' => [],
    'placeholder' => 'Añade un punto',
    'hint' => null,
    'error' => null,
    'max' => 50,
    'id' => null,
])
@php
    $id ??= 'field-' . Str::slug(str_replace(['[', ']', '_'], ' ', $name));
    $points = collect($points)
        ->map(function ($point): string {
            if (is_string($point) || is_numeric($point)) {
                return trim((string) $point);
            }
            $point = $point instanceof Illuminate\Contracts\Support\Arrayable ? $point->toArray() : (array) $point;

            return trim((string) ($point['point'] ?? ($point['title'] ?? '')));
        })
        ->filter(fn(string $point): bool => $point !== '')
        ->values()
        ->all();
    $error ??= ($errors ?? null)?->first($name);
    $describedBy = trim(($hint !== null ? $id . '-hint ' : '') . ($error !== null ? $id . '-error' : ''));
    $control =
        'rounded-sm border border-field-edge bg-field px-3 py-2 text-control text-ink placeholder:text-ink-faint pointer-coarse:min-h-11';
@endphp
<fieldset x-data="agendaEditor" data-points="{{ json_encode($points, JSON_THROW_ON_ERROR) }}"
    data-max="{{ max(1, (int) $max) }}" {{ $attributes->class(['grid min-w-0 grid-cols-[minmax(0,1fr)] gap-3']) }}
    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif
    @if ($error !== null) aria-invalid="true" @endif>
    <legend class="text-sm font-semibold">{{ $label }}</legend>
    @if ($hint !== null)
        <p id="{{ $id }}-hint" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
    <ol aria-label="Puntos del orden del día" class="grid grid-cols-[minmax(0,1fr)] gap-2">
        <template x-for="(point, index) in points" x-bind:key="point.key">
            <li
                class="glass-2 grid grid-cols-[auto_minmax(0,1fr)] items-center gap-x-3 gap-y-2 rounded-lg px-3 py-2 md:grid-cols-[auto_minmax(0,1fr)_auto]">
                <span class="w-5 text-center font-mono text-sm text-ink-muted tabular-nums" x-text="index + 1"></span>
                <label class="grid min-w-0">
                    <span class="sr-only" x-text="pointLabel(index)"></span>
                    <input type="text" x-model="point.text" x-on:keydown="onItemKeydown($event, index)"
                        class="w-full {{ $control }}">
                </label>
                <span class="col-span-2 flex justify-end gap-0.5 md:col-span-1">
                    <x-ui.button icon-only icon="arrow-up" variant="ghost" size="sm" label="Subir"
                        data-action="up" x-on:click="moveUp(index)" x-bind:disabled="isFirst(index)"
                        x-bind:aria-label="actionLabel('up', index)" x-bind:title="actionLabel('up', index)" />
                    <x-ui.button icon-only icon="arrow-down" variant="ghost" size="sm" label="Bajar"
                        data-action="down" x-on:click="moveDown(index)" x-bind:disabled="isLast(index)"
                        x-bind:aria-label="actionLabel('down', index)" x-bind:title="actionLabel('down', index)" />
                    <x-ui.button icon-only icon="x" variant="ghost" size="sm" label="Quitar"
                        data-action="remove" x-show="!point.confirming" x-on:click="remove(index)"
                        x-bind:aria-label="actionLabel('remove', index)" x-bind:title="actionLabel('remove', index)" />
                    <x-ui.button variant="danger" size="sm" data-action="confirm" x-show="point.confirming"
                        x-on:click="remove(index)" x-bind:aria-label="actionLabel('confirm', index)"
                        x-bind:title="actionLabel('confirm', index)">Confirmar</x-ui.button>
                </span>
            </li>
        </template>
    </ol>
    <div class="flex items-center gap-2">
        <label for="{{ $id }}-draft" class="sr-only">Nuevo punto</label>
        <input id="{{ $id }}-draft" type="text" x-ref="draft" data-field="draft" x-model="draft"
            x-on:keydown.enter.prevent="add" placeholder="{{ $placeholder }}"
            class="w-0 min-w-0 flex-1 {{ $control }}">
        <x-ui.button icon="plus" x-on:click="add">Añadir</x-ui.button>
    </div>
    @if ($error !== null)
        <p id="{{ $id }}-error" class="text-sm font-medium text-rejected-ink">{{ $error }}</p>
    @endif
    <input type="hidden" name="{{ $name }}" value="{{ json_encode($points, JSON_THROW_ON_ERROR) }}"
        x-bind:value="json">
    <p role="status" class="sr-only" x-text="announcement"></p>
    {{ $slot }}
</fieldset>
