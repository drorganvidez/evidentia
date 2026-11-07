{{--
    Rows of a table of threads, for the inbox and for «Mis mensajes».
    @include('messages.partials.rows', ['threads' => $threads, 'me' => $me, 'withAuthor' => true])
--}}
@use('App\Support\Roles')
@foreach ($threads as $thread)
    @php
        $href = route('messages.show', $thread);
        $aboutUrl = $thread->about_title ? $thread->aboutUrl($me) : null;
    @endphp
    <x-ui.table.row :href="$href">
        <x-ui.table.cell label="Asunto" primary>
            <span class="flex items-center gap-1.5">
                @if ($thread->isUnreadFor($me))
                    <x-ui.dot tone="brand" />
                    <span class="sr-only">Sin leer.</span>
                @endif
                <a href="{{ $href }}">{{ $thread->subject }}</a>
            </span>
            @if ($thread->lastMessage)
                <span
                    class="mt-0.5 block max-w-[48ch] truncate text-sm text-ink-muted">{{ $thread->lastMessage->body }}</span>
            @endif
        </x-ui.table.cell>
        @if ($withAuthor)
            <x-ui.table.cell label="De">
                <x-ui.person :name="$thread->author?->name ?? $thread->author_name" :surname="$thread->author?->surname" :role="Roles::asLabel($thread->author_role)" size="sm" />
            </x-ui.table.cell>
        @endif
        <x-ui.table.cell label="Para" hide>
            <x-ui.badge variant="chip">{{ $thread->audienceLabel() }}</x-ui.badge>
        </x-ui.table.cell>
        <x-ui.table.cell label="Sobre" hide>
            @if ($aboutUrl)
                <a href="{{ $aboutUrl }}">{{ $thread->about_title }}</a>
            @elseif ($thread->about_title)
                {{ $thread->about_title }}
            @endif
        </x-ui.table.cell>
        <x-ui.table.cell label="Estado" end>
            <x-ui.badge :tone="$thread->isResolved() ? 'accepted' : 'pending'"
                size="sm">{{ $thread->isResolved() ? 'Resuelto' : 'Abierto' }}</x-ui.badge>
        </x-ui.table.cell>
        <x-ui.table.cell label="Última respuesta" meta>
            <x-ui.time :value="$thread->last_message_at" inline short />
        </x-ui.table.cell>
        <x-ui.table.cell actions>
            <x-ui.button icon-only icon="eye" :label="'Abrir ' . $thread->subject" :href="$href" variant="ghost" size="sm"
                dim />
        </x-ui.table.cell>
    </x-ui.table.row>
@endforeach
