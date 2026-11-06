{{-- Confirmation to remove an hour bonus; the hours it gave its people go with it --}}
<x-ui.dialog :id="$dialogId" variant="alert" title="Eliminar el bono"
    description="Se borrarán las horas que el bono da a sus personas.">
    <x-slot:trigger>
        <x-ui.button :icon-only="$iconOnly ?? false" icon="trash-2" :variant="$iconOnly ?? false ? 'ghost' : 'danger'" :size="$iconOnly ?? false ? 'sm' : 'md'" :label="'Eliminar ' . $bono->reason"
            aria-haspopup="dialog" aria-controls="{{ $dialogId }}" x-on:click="show">Eliminar</x-ui.button>
    </x-slot:trigger>
    <x-slot:footer>
        <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
        <form method="post" action="{{ route('secretary.bonus.remove') }}">
            @csrf
            <input type="hidden" name="_id" value="{{ $bono->id }}">
            <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, eliminar</x-ui.button>
        </form>
    </x-slot:footer>
</x-ui.dialog>
