{{--
    Form with the CSRF token and the spoofed method, a busy state against the double submit and a summary of the
    validation errors that links each field. The summary is the only element of a page allowed to take the focus.
    <x-ui.form :action="route('evidence.store')" files :fields="['title' => 'Título']">
        <x-ui.field name="title" label="Título" required />
        <x-slot:actions><x-ui.button type="submit" variant="primary">Guardar</x-ui.button></x-slot:actions>
    </x-ui.form>
    <x-ui.form method="get" :action="route('activity.export')" download busy-label="Preparando">...</x-ui.form>
    <x-ui.form :action="route('profile.password')" id="password-form" bag="password" :fields="['password' => 'Contraseña']">
    The summary reads the error bag named by bag (or the MessageBag given as bag) and, with fields, lists only their
    errors, so every form of a page shows its own: fields then names every key the form may fail on, hidden ones
    too, or their errors show nowhere. Each link goes to ui_field_id of the key without its last index (users.0 to field-users, items.0.title
    to field-items-0-title), once per target. Several forms on a page need an id each; focus-summary=false keeps the summary from taking
    the focus on load.
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
    'focusSummary' => true,
    'bag' => 'default',
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
    $viewErrors = $errors ?? null;
    $messages = match (true) {
        $bag instanceof \Illuminate\Contracts\Support\MessageBag => $bag,
        $viewErrors instanceof \Illuminate\Support\ViewErrorBag => $viewErrors->getBag(
            filled($bag) ? (string) $bag : 'default',
        ),
        default => null,
    };
    $items = [];
    if ($summary && $messages !== null) {
        foreach ($messages->getMessages() as $key => $list) {
            // Only the last index goes: users.0 belongs to users[] and items.0.title to items[0][title]
            $base = preg_replace('/\.\d+$/', '', $key);
            if ($labels !== [] && !isset($labels[$key]) && !isset($labels[$base])) {
                continue;
            }
            // users.0 and users.1 land on the same list: one link per target, with its first message
            $target = ui_field_id(str_replace('.', '-', $base));
            if (isset($items[$target])) {
                continue;
            }
            $items[$target] = [
                'id' => $target,
                'label' => $labels[$key] ?? ($labels[$base] ?? $key),
                'message' => $list[0] ?? '',
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
        <div role="group" aria-labelledby="{{ $summaryId }}" tabindex="-1"
            @if ($focusSummary) autofocus @endif
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
