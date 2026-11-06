{{-- Confirmation to remove a default list of people --}}
<x-ui.dialog :id="$dialogId" variant="alert" title="Eliminar la lista" description="Esta acción no se puede deshacer.">
    <x-slot:trigger>
        <x-ui.button :icon-only="$iconOnly ?? false" icon="trash-2" :variant="$iconOnly ?? false ? 'ghost' : 'danger'" :size="$iconOnly ?? false ? 'sm' : 'md'" :label="'Eliminar ' . $defaultlist->name"
            aria-haspopup="dialog" aria-controls="{{ $dialogId }}" x-on:click="show">Eliminar</x-ui.button>
    </x-slot:trigger>
    <x-slot:footer>
        <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
        <form method="post" action="{{ route('secretary.defaultlist.remove') }}">
            @csrf
            <input type="hidden" name="_id" value="{{ $defaultlist->id }}">
            <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, eliminar</x-ui.button>
        </form>
    </x-slot:footer>
</x-ui.dialog>
