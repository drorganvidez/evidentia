{{--
    Summary of a person for their profile and for the management: name and roles, the computed hours by origin, the
    level of involvement and the summary of their work in the course.
    @include('profile.partials.summary', ['user' => $user, 'hours' => $hours, 'own' => true])
    own adds the way to the form when the summary is missing.
--}}
@php
    $own ??= false;
    $roleNames = [
        'STUDENT' => 'Estudiante',
        'COORDINATOR' => 'Coordinador',
        'SECRETARY' => 'Secretario',
        'REGISTER_COORDINATOR' => 'Coordinador de registro',
        'PRESIDENT' => 'Presidente',
        'LECTURE' => 'Profesor',
    ];
@endphp
<x-ui.card :title="trim($user->name . ' ' . $user->surname)" :level="2" id="summary-title">
    <x-slot:header class="items-center">
        <div class="flex min-w-0 items-center gap-3">
            <x-ui.avatar :name="$user->name" :surname="$user->surname" size="lg" />
            <div class="grid min-w-0 gap-1">
                <h2 id="summary-title" class="min-w-0 text-lg font-bold tracking-tight">
                    {{ trim($user->name . ' ' . $user->surname) }}</h2>
                <ul class="flex flex-wrap gap-1.5" aria-label="Roles">
                    @foreach ($user->roles as $role)
                        <li><x-ui.badge variant="chip"
                                size="sm">{{ $roleNames[$role->rol] ?? $role->slug }}</x-ui.badge>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </x-slot:header>
    <div class="grid gap-5">
        <div class="grid gap-3">
            <p class="flex flex-wrap items-baseline justify-between gap-x-4">
                <span class="text-sm font-semibold text-ink-muted">Horas computadas</span>
                <x-ui.hours :value="$hours['total']" size="xl" class="font-bold tracking-tighter" />
            </p>
            <x-ui.meter :segments="$hours['segments']" legend label="Horas computadas por origen" />
        </div>
        @unless ($user->hasRole('LECTURE'))
            <div class="grid gap-2 border-t border-rule pt-4">
                <p class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                    <span class="text-sm font-semibold text-ink-muted">Resumen del trabajo</span>
                </p>
                @if (filled($user->biography))
                    <x-ui.rich-text :html="\App\View\PlainText::toHtml($user->biography)" size="sm" />
                @else
                    <x-ui.alert tone="pending" title="Sin resumen del trabajo" compact>
                        @if ($own)
                            <x-slot:actions>
                                <x-ui.button size="sm" icon="pen-line" href="#biography-form">Escribir el
                                    resumen</x-ui.button>
                            </x-slot:actions>
                        @endif
                    </x-ui.alert>
                @endif
            </div>
        @endunless
    </div>
</x-ui.card>
