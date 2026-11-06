{{--
    A thread: its messages in order, the answer box while it is open, and on the side what it is about, who takes
    part and its history.
--}}
@use('App\Support\Roles')
<x-layout.app :title="$thread->subject" :nav-current="$navCurrent" :breadcrumbs="$breadcrumbs">
    <x-ui.page-header :title="$thread->subject">
        <x-slot:eyebrow>
            <x-ui.badge :tone="$thread->isResolved() ? 'accepted' : 'pending'"
                size="sm">{{ $thread->isResolved() ? 'Resuelto' : 'Abierto' }}</x-ui.badge>
            <x-ui.badge variant="chip">Para {{ $thread->audienceLabel() }}</x-ui.badge>
        </x-slot:eyebrow>
        @canany(['resolve', 'reopen'], $thread)
            <x-slot:actions>
                @can('resolve', $thread)
                    <form method="post" action="{{ route('messages.resolve', $thread) }}">
                        @csrf
                        <x-ui.button type="submit" variant="primary" icon="circle-check">Marcar como resuelto</x-ui.button>
                    </form>
                @endcan
                @can('reopen', $thread)
                    <form method="post" action="{{ route('messages.reopen', $thread) }}">
                        @csrf
                        <x-ui.button type="submit" icon="rotate-ccw">Reabrir</x-ui.button>
                    </form>
                @endcan
            </x-slot:actions>
        @endcanany
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:gap-8">
        <div class="grid grid-cols-[minmax(0,1fr)] content-start gap-3">
            @foreach ($thread->messages as $message)
                <x-ui.message :author="[
                    'name' => $message->user?->name ?? $message->user_name,
                    'surname' => $message->user?->surname,
                    'role' => Roles::asLabel(
                        $message->role,
                        $message->role === $thread->recipient_role ? $thread->committee?->name : null,
                    ),
                    'href' => $access->profileHref($me, $message->user_id),
                ]" :at="$message->created_at" :mine="$message->user_id === $me->id"
                    :status="$loop->first ? ($thread->isResolved() ? 'resolved' : 'open') : null">{{ $message->body }}</x-ui.message>
            @endforeach
            @can('reply', $thread)
                <x-ui.card title="Responder" :level="2">
                    <x-ui.form :action="route('messages.reply', $thread)" :fields="['body' => 'Respuesta']" busy-label="Enviando" id="reply-form">
                        <x-ui.field name="body" label="Tu respuesta" type="textarea" :rows="4"
                            :maxlength="20000" required />
                        <x-slot:actions>
                            <x-ui.button type="submit" variant="primary" icon="send">Enviar</x-ui.button>
                        </x-slot:actions>
                    </x-ui.form>
                </x-ui.card>
            @else
                @if ($thread->isResolved())
                    <x-ui.alert tone="accepted" compact>Hilo resuelto.</x-ui.alert>
                @endif
            @endcan
        </div>
        <aside aria-label="Contexto del mensaje" class="grid grid-cols-[minmax(0,1fr)] content-start gap-6">
            @if ($thread->about_title)
                @if ($url = $thread->aboutUrl($me))
                    <x-ui.card title="Sobre" :level="2" :href="$url" arrow>
                        <p class="font-semibold">{{ $thread->about_title }}</p>
                    </x-ui.card>
                @else
                    <x-ui.card title="Sobre" :level="2">
                        <p class="font-semibold">{{ $thread->about_title }}</p>
                    </x-ui.card>
                @endif
            @endif
            <x-ui.card title="Participantes" :level="2">
                <ul class="grid gap-2">
                    @foreach ($participants as $person)
                        <li><x-ui.person :name="$person->name" :surname="$person->surname" size="sm" /></li>
                    @endforeach
                </ul>
            </x-ui.card>
            <x-ui.card title="Historial" :level="2" padding="sm">
                @include('activity.partials.log', [
                    'entries' => $log,
                    'viewer' => $me,
                    'label' => 'Historial del mensaje',
                ])
            </x-ui.card>
        </aside>
    </div>
</x-layout.app>
