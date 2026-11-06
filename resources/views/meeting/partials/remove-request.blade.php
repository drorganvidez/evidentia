{{-- Confirmation to remove a meeting call; its signature sheet is only unpaired and no minutes change --}}
<x-ui.dialog :id="$dialogId" variant="alert" title="Eliminar la convocatoria"
    description="Su hoja de firmas se conserva sin convocatoria y ningún acta cambia.">
    <x-slot:trigger>
        <x-ui.button :icon-only="$iconOnly ?? false" icon="trash-2" :variant="$iconOnly ?? false ? 'ghost' : 'danger'" :size="$iconOnly ?? false ? 'sm' : 'md'" :label="'Eliminar ' . $meeting_request->title"
            aria-haspopup="dialog" aria-controls="{{ $dialogId }}" x-on:click="show">Eliminar</x-ui.button>
    </x-slot:trigger>
    <x-slot:footer>
        <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
        <form method="post" action="{{ route('secretary.meeting.manage.request.remove') }}">
            @csrf
            <input type="hidden" name="_id" value="{{ $meeting_request->id }}">
            <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, eliminar</x-ui.button>
        </form>
    </x-slot:footer>
</x-ui.dialog>
