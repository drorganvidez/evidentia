{{--
    Record of a person for the president and the lecturers (App\View\StudentRecord): the summary of their hours, their
    account, committees and accesses, and one section at a time (?tab=, linkable and without script) with every
    evidence, meeting, signature, attendance, bonus, annotation and message, the newest first.
--}}
@php
    $fullName = trim($user->name . ' ' . $user->surname);
    $plural = fn(int $count, string $one, string $many): string => $count . ' ' . ($count === 1 ? $one : $many);
    $tabs = collect(\App\View\StudentRecord::TABS)
        ->map(
            fn(string $label, string $key) => [
                'label' => $label,
                'href' =>
                    route(
                        'profiles.view',
                        array_filter(['id' => $user->id, 'tab' => $key === 'evidencias' ? null : $key]),
                    ) . '#secciones',
                'count' => $counts[$key],
                'current' => $tab === $key,
            ],
        )
        ->values()
        ->all();
    $roleNames = ['COORDINATOR' => 'Coordinación', 'SECRETARY' => 'Secretaría'];
@endphp
<x-layout.app :title="$fullName" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Alumnos', 'href' => route($prefix . '.user.list')],
    ['label' => $fullName],
]">
    <x-ui.page-header :title="$fullName">
        <x-slot:actions>
            <x-ui.button icon="user-cog" :href="route($prefix . '.user.management', ['id' => $user->id])">Gestionar la
                cuenta</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)] lg:gap-8">
        @include('profile.partials.summary', ['user' => $user, 'hours' => $hours])

        <x-ui.card title="Cuenta" :level="2" id="account-title">
            <x-ui.dl>
                <x-ui.dl.item term="Usuario" mono>{{ $account['username'] }}</x-ui.dl.item>
                <x-ui.dl.item term="Correo"><span class="break-all">{{ $account['email'] }}</span></x-ui.dl.item>
                <x-ui.dl.item term="Comités">
                    @if ($account['committees']->isEmpty())
                        Ninguno
                    @else
                        <ul class="grid gap-1.5">
                            @foreach ($account['committees'] as $item)
                                <li class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span>{{ $roleNames[$item['role']] }}</span>
                                    <x-ui.committee-chip :committee="$item['committee']" :href="route($prefix . '.committee.view', ['id' => $item['committee']->id])" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.dl.item>
                <x-ui.dl.item term="Listas">
                    @if ($account['lists']->isEmpty())
                        Ninguna
                    @else
                        <ul class="grid gap-1">
                            @foreach ($account['lists'] as $list)
                                <li>{{ $list->name }}@if ($list->secretary?->committee)
                                        <span class="text-ink-muted"> · {{ $list->secretary->committee->name }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-ui.dl.item>
                <x-ui.dl.item term="Último acceso">
                    @if ($account['lastLogin'])
                        <x-ui.time :value="$account['lastLogin']" />
                    @else
                        Nunca
                    @endif
                </x-ui.dl.item>
                <x-ui.dl.item term="Accesos" mono>{{ $account['logins'] }}</x-ui.dl.item>
                <x-ui.dl.item term="Última acción">
                    @if ($account['lastAction'])
                        <x-ui.time :value="$account['lastAction']" />
                    @else
                        Ninguna
                    @endif
                </x-ui.dl.item>
                @if ($account['blocked'])
                    <x-ui.dl.item term="Estado"><x-ui.badge tone="rejected"
                            size="sm">Bloqueada</x-ui.badge></x-ui.dl.item>
                @endif
            </x-ui.dl>
        </x-ui.card>
    </div>

    <x-ui.tabs id="secciones" class="min-w-0 scroll-mt-24" label="Secciones del expediente" size="md"
        :items="$tabs" />

    @include('profile.record.' . $tab)
</x-layout.app>
