{{-- Confirmation to remove a signature sheet with its signatures; its meeting call and the minutes do not change --}}
<x-ui.dialog :id="$dialogId" variant="alert" title="Eliminar la hoja de firmas"
    description="Se borrarán sus firmas para siempre. Su convocatoria y las actas no cambian.">
    <x-slot:trigger>
        <x-ui.button :icon-only="$iconOnly ?? false" icon="trash-2" :variant="$iconOnly ?? false ? 'ghost' : 'danger'" :size="$iconOnly ?? false ? 'sm' : 'md'" :label="'Eliminar ' . $signature_sheet->title"
            aria-haspopup="dialog" aria-controls="{{ $dialogId }}" x-on:click="show">Eliminar</x-ui.button>
    </x-slot:trigger>
    <x-slot:footer>
        <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
        <form method="post" action="{{ route('secretary.meeting.manage.signaturesheet.remove') }}">
            @csrf
            <input type="hidden" name="_id" value="{{ $signature_sheet->id }}">
            <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, eliminar</x-ui.button>
        </form>
    </x-slot:footer>
</x-ui.dialog>
