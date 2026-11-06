{{--
    Editor of the minutes: points with title, description, duration and agreements, reordered with buttons and removed in
    two steps. The hidden input receives the JSON the secretary controller reads from points_json:
    [{id, title, duration, description, agreements: [{id, description}]}]. Validates when the surrounding form submits.
    <x-ui.minutes-editor :points="$meeting_minutes->points" />
    <x-ui.minutes-editor :points="session('points', [])" :min-points="1" :error="$errors->first('points_json')" />
    Points may be arrays or models; a title may also come as point (DiaryPoint). The slot renders inside the Alpine scope.
--}}
@props([
    'name' => 'points_json',
    'points' => [],
    'label' => 'Puntos del acta',
    'hint' => null,
    'error' => null,
    'minPoints' => 0,
    'id' => null,
])
@php
    $id ??= ui_field_id($name);
    $toArray = fn($item): array => $item instanceof Illuminate\Contracts\Support\Arrayable
        ? $item->toArray()
        : (array) $item;
    $points = collect($points)
        ->map(function ($point) use ($toArray): array {
            $point = $toArray($point);
            $duration = $point['duration'] ?? null;

            return [
                'id' => isset($point['id']) ? (int) $point['id'] : null,
                'title' => trim((string) ($point['title'] ?? ($point['point'] ?? ''))),
                'duration' => is_numeric($duration) ? (int) $duration : null,
                'description' => trim((string) ($point['description'] ?? '')),
                'agreements' => collect($point['agreements'] ?? [])
                    ->map(function ($agreement) use ($toArray): array {
                        $agreement = is_string($agreement) ? ['description' => $agreement] : $toArray($agreement);

                        return [
                            'id' => isset($agreement['id']) ? (int) $agreement['id'] : null,
                            'description' => trim((string) ($agreement['description'] ?? '')),
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        })
        ->values()
        ->all();
    $error ??= ($errors ?? null)?->first($name);
    $describedBy = trim(($hint !== null ? $id . '-hint ' : '') . ($error !== null ? $id . '-error' : ''));
    $control =
        'w-full rounded-sm border border-field-edge bg-field px-3 py-2 text-control text-ink placeholder:text-ink-faint pointer-coarse:min-h-11';
@endphp
<div x-data="minutesEditor" data-points="{{ json_encode($points, JSON_THROW_ON_ERROR) }}"
    data-min="{{ max(0, (int) $minPoints) }}" data-id="{{ $id }}" id="{{ $id }}" tabindex="-1"
    role="group" aria-labelledby="{{ $id }}-label"
    {{ $attributes->class(['grid min-w-0 grid-cols-[minmax(0,1fr)] gap-4']) }}
    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
    <p id="{{ $id }}-label" class="text-sm font-semibold">{{ $label }}</p>
    @if ($hint !== null)
        <p id="{{ $id }}-hint" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
    <template x-for="(point, i) in points" x-bind:key="point.key">
        <fieldset class="glass-2 grid min-w-0 grid-cols-[minmax(0,1fr)] gap-4 rounded-lg p-4 md:p-5" data-point>
            <legend class="sr-only" x-text="pointLegend(i)"></legend>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="font-semibold" aria-hidden="true">Punto <span x-text="i + 1"></span></span>
                <span class="flex gap-0.5">
                    <x-ui.button icon-only icon="arrow-up" variant="ghost" size="sm" label="Subir"
                        data-action="up" x-on:click="movePoint(i, -1)" x-bind:disabled="isFirst(i)"
                        x-bind:aria-label="actionLabel('up', i)" x-bind:title="actionLabel('up', i)" />
                    <x-ui.button icon-only icon="arrow-down" variant="ghost" size="sm" label="Bajar"
                        data-action="down" x-on:click="movePoint(i, 1)" x-bind:disabled="isLast(i)"
                        x-bind:aria-label="actionLabel('down', i)" x-bind:title="actionLabel('down', i)" />
                    <x-ui.button icon-only icon="x" variant="ghost" size="sm" label="Quitar"
                        data-action="remove" x-show="!point.confirming" x-on:click="removePoint(i)"
                        x-bind:aria-label="actionLabel('remove', i)" x-bind:title="actionLabel('remove', i)" />
                    <x-ui.button variant="danger" size="sm" data-action="confirm" x-show="point.confirming"
                        x-on:click="removePoint(i)" x-bind:aria-label="actionLabel('confirm', i)"
                        x-bind:title="actionLabel('confirm', i)">Confirmar</x-ui.button>
                </span>
            </div>
            <div class="grid grid-cols-[minmax(0,1fr)] gap-1.5">
                <label class="grid gap-1.5">
                    <span class="text-sm font-semibold">Título</span>
                    <input type="text" x-model="point.title" data-field="title" x-on:input="clearError(i, 'title')"
                        x-bind:aria-invalid="point.errors.title ? 'true' : null"
                        x-bind:aria-describedby="describedBy(i, 'title')"
                        class="{{ $control }} aria-[invalid=true]:border-rejected">
                </label>
                <span x-bind:id="errorId(i, 'title')" x-show="point.errors.title" x-text="point.errors.title"
                    role="alert" class="text-sm font-medium text-rejected-ink"></span>
            </div>
            <div class="grid grid-cols-[minmax(0,1fr)] gap-4 md:grid-cols-[minmax(0,1fr)_8rem]">
                <label class="grid gap-1.5">
                    <span class="text-sm font-semibold">Descripción</span>
                    <textarea x-model="point.description" rows="3" data-field="description"
                        class="{{ $control }} field-sizing-content"></textarea>
                </label>
                <label class="grid content-start gap-1.5">
                    <span class="text-sm font-semibold">Duración (min)</span>
                    <input type="number" min="0" inputmode="numeric" x-model.number="point.duration"
                        data-field="duration" class="{{ $control }}">
                </label>
            </div>
            <fieldset class="grid min-w-0 grid-cols-[minmax(0,1fr)] gap-2">
                <legend class="text-sm font-semibold">Acuerdos</legend>
                <template x-for="(agreement, j) in point.agreements" x-bind:key="agreement.key">
                    <div class="grid grid-cols-[minmax(0,1fr)_auto] items-start gap-2">
                        <label class="grid min-w-0 gap-1">
                            <span class="sr-only" x-text="agreementLabel(i, j)"></span>
                            <textarea x-model="agreement.description" rows="2" data-field="agreement"
                                class="{{ $control }} field-sizing-content"></textarea>
                        </label>
                        <x-ui.button icon-only icon="x" variant="ghost" size="sm" label="Quitar acuerdo"
                            data-action="remove-agreement" x-on:click="removeAgreement(i, j)"
                            x-bind:aria-label="agreementAction(i, j)" x-bind:title="agreementAction(i, j)" />
                    </div>
                </template>
                <x-ui.button icon="plus" size="sm" data-action="add-agreement" x-on:click="addAgreement(i)"
                    class="justify-self-start">Añadir acuerdo</x-ui.button>
            </fieldset>
        </fieldset>
    </template>
    <x-ui.button icon="plus" data-action="add-point" x-on:click="addPoint" class="justify-self-start">Añadir
        punto</x-ui.button>
    @if ($error !== null)
        <p id="{{ $id }}-error" class="text-sm font-medium text-rejected-ink">{{ $error }}</p>
    @endif
    <input type="hidden" name="{{ $name }}" value="{{ json_encode($points, JSON_THROW_ON_ERROR) }}"
        x-bind:value="json">
    <p role="status" class="sr-only" x-text="announcement"></p>
    {{ $slot }}
</div>
