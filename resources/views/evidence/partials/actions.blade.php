{{--
    What the author does with an evidence while the upload is open: edit a draft, take a rejected one back to draft
    and delete it with every version and proof, each change behind a confirmation that posts its own form.
    @include('evidence.partials.actions', ['evidence' => $evidence, 'compact' => true])
    compact draws icon buttons named after the evidence, for the rows of a table.
--}}
@php
    $compact ??= false;
    $open = !now()->gt(\Config::upload_evidences_timestamp() ?? now());
    $named = fn(string $verb): string => $compact ? $verb . ' ' . $evidence->title : $verb;
@endphp
@if ($open)
    @if ($evidence->status === 'DRAFT')
        <x-ui.button icon="pen-line" :icon-only="$compact" :label="$named('Editar')" :variant="$compact ? 'ghost' : 'primary'" :size="$compact ? 'sm' : 'md'"
            :href="route('evidence.edit', ['id' => $evidence->id])">Editar</x-ui.button>
    @endif

    @if ($evidence->status === 'REJECTED')
        <x-ui.dialog :id="'reedit-' . $evidence->id" variant="alert" title="Corregir la evidencia"
            description="Volverá a borrador y tu coordinador tendrá que revisarla de nuevo." :trigger="$named('Corregir')"
            trigger-icon="pen-line" :trigger-icon-only="$compact" :trigger-variant="$compact ? 'ghost' : 'primary'">
            <x-slot:footer>
                <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
                <form method="post" action="{{ route('evidence.reedit') }}">
                    @csrf
                    <input type="hidden" name="_id" value="{{ $evidence->id }}">
                    <x-ui.button variant="primary" type="submit" icon="pen-line" block>Sí, corregir</x-ui.button>
                </form>
            </x-slot:footer>
        </x-ui.dialog>
    @endif

    <x-ui.dialog :id="'remove-' . $evidence->id" variant="alert" title="Eliminar la evidencia"
        description="Se borrarán también sus ediciones anteriores y sus pruebas." :trigger="$named('Eliminar')"
        trigger-icon="trash-2" :trigger-icon-only="$compact" :trigger-variant="$compact ? 'ghost' : 'danger'">
        <x-slot:footer>
            <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
            <form method="post" action="{{ route('evidence.remove') }}">
                @csrf
                <input type="hidden" name="_id" value="{{ $evidence->id }}">
                <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, eliminar</x-ui.button>
            </form>
        </x-slot:footer>
    </x-ui.dialog>
@endif
