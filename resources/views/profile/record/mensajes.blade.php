{{-- Section of the record: the threads in which the person wrote, with how many messages they wrote in each, ordered in the browser --}}
@php
    $columns = [
        ['label' => 'Asunto', 'key' => 'subject'],
        ['label' => 'Para', 'key' => 'audience'],
        ['label' => 'Escritos', 'key' => 'written', 'align' => 'end'],
        ['label' => 'Estado', 'key' => 'state'],
        ['label' => 'Último mensaje', 'key' => 'last_message_at', 'sorted' => 'desc'],
    ];
@endphp
<x-ui.table caption="Mensajes que ha escrito" :columns="$columns" show-caption sortable>
    @foreach ($threads as $thread)
        @php
            $href = route('messages.show', $thread);
        @endphp
        <x-ui.table.row :href="$href">
            <x-ui.table.cell label="Asunto" primary><a
                    href="{{ $href }}">{{ $thread->subject }}</a></x-ui.table.cell>
            <x-ui.table.cell label="Para" meta>{{ $thread->audienceLabel() }}</x-ui.table.cell>
            <x-ui.table.cell label="Escritos" numeric meta>{{ $thread->written_count }}</x-ui.table.cell>
            <x-ui.table.cell label="Estado" end><x-ui.badge :tone="$thread->isResolved() ? 'accepted' : 'pending'"
                    size="sm">{{ $thread->isResolved() ? 'Resuelto' : 'Abierto' }}</x-ui.badge></x-ui.table.cell>
            <x-ui.table.cell label="Último mensaje" meta><x-ui.time :value="$thread->last_message_at" inline short /></x-ui.table.cell>
        </x-ui.table.row>
    @endforeach
    @if ($threads->isEmpty())
        <x-slot:empty>
            <x-ui.empty icon="messages-square" title="No ha escrito mensajes" size="sm" />
        </x-slot:empty>
    @else
        <x-slot:footer>
            <p>{{ $plural($threads->count(), 'hilo', 'hilos') }}</p>
            <p>{{ $plural($totals['messages'], 'mensaje escrito', 'mensajes escritos') }}</p>
        </x-slot:footer>
    @endif
</x-ui.table>
