{{--
    Checkboxes under one legend with a shared hint and error, plus a block that depends on what is ticked.
    <x-ui.checkbox-group name="roles" legend="Roles" :options="$roles" :selected="$user->roles->pluck('rol')->all()" :columns="2">
        <x-slot:dependent>
            <div x-show="hasAny('COORDINATOR', 'SECRETARY')" x-cloak>...select of the committee...</div>
        </x-slot:dependent>
    </x-ui.checkbox-group>
    options is a list of {value, label, hint}; the inputs are named name[]. selected takes an array, a collection or models
    (by their key) and rules the first load only: once a failed validation of any form of the page brings the input
    back, old() decides and a missing key means nothing ticked; inside a GET x-ui.form old() is never read. The server
    decides the initial state of the dependent block, so it works without JS: add x-cloak only when it renders hidden.
--}}
@props([
    'name' => '',
    'legend' => '',
    'options' => [],
    'selected' => [],
    'hint' => null,
    'error' => null,
    'columns' => 1,
    'required' => false,
    'id' => null,
])
@aware(['method' => null])
@php
    throw_if(blank($name), InvalidArgumentException::class, 'The checkbox group needs a name.');
    throw_if(blank($legend), InvalidArgumentException::class, 'The checkbox group needs a legend.');
    throw_if($options === [], InvalidArgumentException::class, 'The checkbox group needs options.');

    $base = str_ends_with($name, '[]') ? substr($name, 0, -2) : $name;
    $inputName = $base . '[]';
    $errorKey = ui_error_key($base);
    $id ??= ui_field_id($base);

    // Never assume $errors: the components also render through Blade::render
    $bag = $errors ?? null;
    if ($error === null && $bag instanceof \Illuminate\Support\ViewErrorBag) {
        $error = $bag->first($errorKey) ?: ($bag->first($errorKey . '.*') ?: null);
    }

    // After a failed validation the input that came back decides: with nothing ticked the key is not even sent
    if (strtolower((string) $method) !== 'get' && request()->hasSession() && request()->session()->hasOldInput()) {
        $selected = old($errorKey, []);
    }
    // An array, a collection or models (compared by their key): every value is compared as a string
    $selected = collect($selected instanceof \Illuminate\Database\Eloquent\Model ? [$selected] : $selected)
        ->map(
            fn($item): string => (string) match (true) {
                $item instanceof \Illuminate\Database\Eloquent\Model => $item->getKey(),
                $item instanceof \BackedEnum => $item->value,
                default => $item,
            },
        )
        ->filter(fn(string $item): bool => $item !== '')
        ->values()
        ->all();

    $describedBy = implode(
        ' ',
        array_filter([$error !== null ? $id . '-error' : null, $hint !== null ? $id . '-hint' : null]),
    );
    $columnsClass = match ((int) $columns) {
        2 => 'grid gap-2 md:grid-cols-2',
        default => 'grid gap-2',
    };
@endphp
<fieldset x-data="checkboxGroup" data-name="{{ $inputName }}" id="{{ $id }}" tabindex="-1"
    {{ $attributes->class(['grid min-w-0 grid-cols-[minmax(0,1fr)] gap-3']) }}
    @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
    <legend class="text-sm font-semibold">{{ $legend }}
        @if ($required)
            <span aria-hidden="true" class="text-rejected-ink after:content-['*']"></span>
            <span class="sr-only">(obligatorio)</span>
        @endif
    </legend>
    @if ($hint !== null)
        <p id="{{ $id }}-hint" class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
    <div class="{{ $columnsClass }}">
        @foreach ($options as $option)
            <x-ui.choice :name="$inputName" :value="$option['value']" :label="$option['label']" :hint="$option['hint'] ?? null" :checked="in_array((string) $option['value'], $selected, true)"
                :id="$id . '-' . Str::slug((string) $option['value'])" x-on:change="sync" />
        @endforeach
    </div>
    @isset($dependent)
        {{ $dependent }}
    @endisset
    @if ($error !== null)
        <p id="{{ $id }}-error" class="flex items-center gap-1.5 text-sm text-rejected-ink">
            <x-lucide-circle-alert class="size-4" aria-hidden="true" />{{ $error }}
        </p>
    @endif
</fieldset>
