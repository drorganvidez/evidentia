{{-- Confirmation to remove minutes with their meeting, attendances, points, agreements and PDF --}}
<x-ui.dialog :id="$dialogId" variant="alert" title="Eliminar el acta"
    description="Se borrarán el acta, su PDF, la reunión con sus asistencias, los puntos y los acuerdos. La convocatoria y la hoja de firmas no cambian.">
    <x-slot:trigger>
        <x-ui.button :icon-only="$iconOnly ?? false" icon="trash-2" :variant="$iconOnly ?? false ? 'ghost' : 'danger'" :size="$iconOnly ?? false ? 'sm' : 'md'" :label="'Eliminar ' . $meeting_minutes->meeting?->title"
            aria-haspopup="dialog" aria-controls="{{ $dialogId }}" x-on:click="show">Eliminar</x-ui.button>
    </x-slot:trigger>
    <x-slot:footer>
        <x-ui.button x-on:click="close" autofocus>Cancelar</x-ui.button>
        <form method="post" action="{{ route('secretary.meeting.manage.minutes.remove') }}">
            @csrf
            <input type="hidden" name="_id" value="{{ $meeting_minutes->id }}">
            <x-ui.button variant="danger" type="submit" icon="trash-2" block>Sí, eliminar</x-ui.button>
        </form>
    </x-slot:footer>
</x-ui.dialog>
