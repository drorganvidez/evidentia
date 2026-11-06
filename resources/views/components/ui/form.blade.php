{{--
    Form with the CSRF token and the spoofed method, a busy state against the double submit and a summary of the
    validation errors that links each field. The summary is the only element of a page allowed to take the focus.
    <x-ui.form :action="route('evidence.store')" files :fields="['title' => 'Título']">
        <x-ui.field name="title" label="Título" required />
        <x-slot:actions><x-ui.button type="submit" variant="primary">Guardar</x-ui.button></x-slot:actions>
    </x-ui.form>
    <x-ui.form method="get" :action="route('activity.export')" download busy-label="Preparando">...</x-ui.form>
--}}
@props([
    'action' => '',
    'method' => 'post',
    'files' => false,
    'summary' => true,
    'summaryTitle' => 'Revisa el formulario',
    'fields' => [],
    'novalidate' => true,
    'busyLabel' => 'Guardando',
    'download' => false,
    'id' => null,
])
@php
    throw_if(blank($action), InvalidArgumentException::class, 'The form component needs an action.');
    $method = strtolower($method);
    $formMethod = $method === 'get' ? 'get' : 'post';
    $spoofed = in_array($method, ['put', 'patch', 'delete'], true) ? strtoupper($method) : null;
    $summaryId = ($id ?? 'form') . '-errors-title';

    // Readable names of the fields, keyed like the error bag (users[] -> users)
    $labels = [];
    foreach ($fields as $fieldName => $fieldLabel) {
        $labels[ui_error_key($fieldName)] = $fieldLabel;
    }

    // Never assume $errors: the components also render through Blade::render
    $bag = $errors ?? null;
    $items = [];
    if ($summary && $bag instanceof \Illuminate\Support\ViewErrorBag && $bag->any()) {
        foreach ($bag->getMessages() as $key => $messages) {
            $base = preg_replace('/\.\d+/', '', $key);
            $items[] = [
                'id' => ui_field_id(str_replace('.', '-', $base)),
                'label' => $labels[$key] ?? ($labels[$base] ?? $key),
                'message' => $messages[0] ?? '',
            ];
        }
    }
@endphp
<form method="{{ $formMethod }}" action="{{ $action }}"
    @if ($files) enctype="multipart/form-data" @endif
    @if ($novalidate) novalidate @endif
    @if ($id !== null) id="{{ $id }}" @endif x-data="busyForm"
    data-busy-label="{{ $busyLabel }}" @if ($download) data-download @endif x-on:submit="submit"
    x-bind:aria-busy="busy ? 'true' : 'false'" {{ $attributes->class(['grid grid-cols-[minmax(0,1fr)] gap-6']) }}>
    @if ($formMethod !== 'get')
        @csrf
    @endif
    @if ($spoofed !== null)
        @method($spoofed)
    @endif
    @if ($items !== [])
        <div role="group" aria-labelledby="{{ $summaryId }}" tabindex="-1" autofocus
            class="rounded-lg border border-rejected/30 bg-rejected-glass p-4 text-rejected-ink">
            <p id="{{ $summaryId }}" class="font-bold">{{ $summaryTitle }}</p>
            <ul class="mt-2 grid gap-1 text-md">
                @foreach ($items as $item)
                    <li>
                        <a href="#{{ $item['id'] }}" class="underline underline-offset-2">{{ $item['label'] }} ·
                            {{ $item['message'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="grid grid-cols-[minmax(0,1fr)] gap-5">{{ $slot }}</div>
    @isset($actions)
        <div
            {{ $actions->attributes->class(['flex flex-col gap-3 border-t border-rule pt-5 *:justify-center md:flex-row-reverse md:items-center']) }}>
            {{ $actions }}</div>
    @endisset
</form>
