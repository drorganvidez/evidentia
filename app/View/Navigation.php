<?php

namespace App\View;

use App\Models\User;
use Carbon\Carbon;

/**
 * Navigation of the signed-in person for x-layout.app: the groups of the sidebar by role, the bottom bar of the
 * phone (fixed by main role) and the deadline card of the current section. The components never read roles nor
 * deadlines; this class turns them into the arrays they expect.
 */
final class Navigation
{
    private const SECRETARY_MEETINGS = [
        'secretary.meeting.manage.request.create', 'secretary.meeting.manage.request.list',
        'secretary.meeting.manage.signaturesheet.create', 'secretary.meeting.manage.signaturesheet.list',
        'secretary.meeting.manage.minutes.list', 'secretary.meeting.manage.minutes.create',
        'secretary.meeting.manage.minutes.create.step1', 'secretary.meeting.manage.minutes.create.step2',
        'secretary.meeting.manage.minutes.create.step3', 'secretary.meeting.manage.minutes.edit',
        'secretary.meeting.manage.signaturesheet.view', 'secretary.meeting.manage.request.edit',
        'secretary.meeting.manage.signaturesheet.edit',
    ];

    /**
     * @return array{nav: list<array{label: ?string, items: list<array<string, mixed>>}>, mobileNav: list<array<string, mixed>>, deadline: ?array<string, mixed>}
     */
    public function for(User $user, ?string $route): array
    {
        $roles = $user->roles->pluck('rol')->all();
        $has = fn (string $role): bool => in_array($role, $roles, true);
        $item = fn (string $label, string $name, string $icon, array $secondaries = [], array $extra = []): array => [
            'label' => $label,
            'href' => route($name),
            'icon' => $icon,
            'current' => $route !== null && in_array($route, [$name, ...$secondaries], true),
            'route' => $name,
        ] + $extra;

        $nav = [[
            'label' => null,
            'items' => [
                $item('Inicio', 'home', 'layout-dashboard'),
                $item('Mi perfil', 'profile.view', 'user-round'),
            ],
        ]];

        // Everybody but a lecturer on their own has evidences, meetings and (students) attendances
        if (array_diff($roles, ['LECTURE']) !== []) {
            $mine = [];
            if ($this->open(\Config::upload_evidences_timestamp())) {
                $mine[] = $item('Nueva evidencia', 'evidence.create', 'plus');
            }
            $mine[] = $item('Mis evidencias', 'evidence.list', 'file-text', ['evidence.view', 'evidence.edit']);
            $mine[] = $item('Mis reuniones', 'meeting.list', 'handshake');
            if ($has('STUDENT')) {
                $mine[] = $item('Mis asistencias', 'attendee.list', 'ticket');
            }
            $nav[] = ['label' => 'Mis cosas', 'items' => $mine];
        }

        $committee = $user->coordinator?->committee;
        if ($has('COORDINATOR') && $committee !== null) {
            $pending = $committee->evidencesPending()->where('user_id', '!=', $user->id)->count();
            $nav[] = ['label' => 'Comité', 'items' => [
                $item('Revisar evidencias', 'coordinator.evidence.list.all', 'list-checks', [
                    'coordinator.evidence.list.pending', 'coordinator.evidence.list.accepted',
                    'coordinator.evidence.list.rejected', 'coordinator.evidence.view',
                ], $pending > 0 ? ['count' => $pending, 'countLabel' => 'pendientes'] : []),
            ]];
        }

        if ($has('SECRETARY') && $user->secretary?->committee !== null) {
            $nav[] = ['label' => 'Reuniones y bonos', 'items' => [
                $item('Reuniones', 'secretary.meeting.manage', 'handshake', self::SECRETARY_MEETINGS),
                $item('Bonos', 'secretary.bonus.list', 'puzzle', ['secretary.bonus.create', 'secretary.bonus.edit']),
                $item('Listas', 'secretary.defaultlist.list', 'users', ['secretary.defaultlist.create', 'secretary.defaultlist.edit']),
            ]];
        }

        if ($has('REGISTER_COORDINATOR')) {
            $items = [];
            if ($this->open(\Config::attendee_timestamp())) {
                $items[] = $item('Eventbrite', 'registercoordinator.token', 'key-round');
            }
            $items[] = $item('Eventos', 'registercoordinator.event.list', 'calendar', ['registercoordinator.event.create', 'registercoordinator.event.edit']);
            $items[] = $item('Asistencias', 'registercoordinator.attendee.list', 'user-check', ['attendee.create', 'attendee.edit']);
            $nav[] = ['label' => 'Asistencias y eventos', 'items' => $items];
        }

        if ($has('PRESIDENT') || $has('LECTURE')) {
            $p = $has('LECTURE') ? 'lecture' : 'president';
            $items = [
                $item('Configurar curso', "$p.config", 'settings'),
                $item('Alumnos', "$p.user.list", 'user-cog', ["$p.user.management", 'profiles.view', 'profiles.view.evidence']),
                $item('Evidencias', "$p.evidence.list", 'list-checks'),
                $item('Reuniones', "$p.meeting.list", 'handshake'),
                $item('Comités', "$p.committee.list", 'network'),
            ];
            if ($has('LECTURE')) {
                $items[] = $item('Aleatorizar', 'randomize.randomize', 'shuffle');
            }
            $items[] = $item('Integridad', "$p.integrity", 'shield-check');
            if ($has('LECTURE')) {
                $items[] = $item('Importar', 'lecture.import', 'import');
            }
            $items[] = $item('Exportar', "$p.export", 'download');
            $nav[] = ['label' => 'Gestión', 'items' => $items];
        }

        return [
            'nav' => $this->strip($nav),
            'mobileNav' => $this->mobile($nav, $roles),
            'deadline' => $this->deadline($roles, $route),
        ];
    }

    /** Bottom bar of the phone: five fixed destinations of the main role, Perfil always the last. */
    private function mobile(array $nav, array $roles): array
    {
        $p = in_array('LECTURE', $roles, true) ? 'lecture' : 'president';
        $bars = [
            'COORDINATOR' => ['home', 'coordinator.evidence.list.all', 'evidence.list', 'meeting.list'],
            'SECRETARY' => ['home', 'secretary.meeting.manage', 'secretary.bonus.list', 'secretary.defaultlist.list'],
            'REGISTER_COORDINATOR' => ['home', 'registercoordinator.event.list', 'registercoordinator.attendee.list', 'evidence.list'],
            'LECTURE' => ['home', "$p.evidence.list", "$p.user.list", "$p.meeting.list"],
            'PRESIDENT' => ['home', "$p.evidence.list", "$p.user.list", "$p.meeting.list"],
            'STUDENT' => ['home', 'evidence.list', 'meeting.list', 'attendee.list'],
        ];
        $short = [
            'home' => 'Inicio', 'evidence.list' => 'Evidencias', 'meeting.list' => 'Reuniones',
            'attendee.list' => 'Asistencias', 'coordinator.evidence.list.all' => 'Revisar',
            'secretary.meeting.manage' => 'Reuniones', 'secretary.bonus.list' => 'Bonos',
            'secretary.defaultlist.list' => 'Listas', 'registercoordinator.event.list' => 'Eventos',
            'registercoordinator.attendee.list' => 'Asistencias', "$p.evidence.list" => 'Evidencias',
            "$p.user.list" => 'Alumnos', "$p.meeting.list" => 'Reuniones',
        ];

        $main = collect(array_keys($bars))->first(fn (string $role): bool => in_array($role, $roles, true)) ?? 'STUDENT';
        $items = collect($nav)->flatMap(fn (array $group): array => $group['items'])->keyBy('route');

        $bar = collect($bars[$main])
            ->map(fn (string $route) => $items->get($route))
            ->filter()
            ->values();
        // A missing destination (a coordinator without committee) is filled with the other items, in group order
        foreach ($items as $route => $candidate) {
            if ($bar->count() >= 4) {
                break;
            }
            if ($route !== 'profile.view' && ! $bar->contains('route', $route)) {
                $bar->push($candidate);
            }
        }
        $bar->push($items->get('profile.view'));

        return $bar->map(fn (array $entry): array => ['label' => $short[$entry['route']] ?? ($entry['route'] === 'profile.view' ? 'Perfil' : $entry['label'])] + $this->clean($entry))
            ->values()
            ->all();
    }

    /** Deadline card of the current section, or the first that concerns the person; null when unset or closed. */
    private function deadline(array $roles, ?string $route): ?array
    {
        $sections = [
            'upload' => ['Subida de evidencias', \Config::upload_evidences_timestamp(), 'evidence.list'],
            'validate' => ['Validación de evidencias', \Config::validate_evidences_timestamp(), 'coordinator.evidence.list.all'],
            'bonus' => ['Registro de bonos', \Config::bonus_timestamp(), 'secretary.bonus.list'],
            'meetings' => ['Registro de reuniones', \Config::meetings_timestamp(), 'secretary.meeting.manage'],
            'attendees' => ['Registro de asistencias', \Config::attendee_timestamp(), 'registercoordinator.attendee.list'],
        ];

        $section = match (true) {
            $route === null => null,
            str_starts_with($route, 'evidence.'), in_array($route, ['meeting.list', 'attendee.list'], true) => 'upload',
            str_starts_with($route, 'coordinator.') => 'validate',
            str_starts_with($route, 'secretary.bonus.') => 'bonus',
            str_starts_with($route, 'secretary.') => 'meetings',
            str_starts_with($route, 'registercoordinator.'), in_array($route, ['attendee.create', 'attendee.edit'], true) => 'attendees',
            default => null,
        };
        $section ??= match (true) {
            array_intersect($roles, ['STUDENT', 'COORDINATOR', 'SECRETARY', 'REGISTER_COORDINATOR', 'PRESIDENT']) !== [] => 'upload',
            default => null,
        };

        if ($section === null) {
            return null;
        }

        [$label, $closesAt, $href] = $sections[$section];

        return $this->open($closesAt)
            ? ['label' => $label, 'closesAt' => Carbon::parse($closesAt), 'opensAt' => null, 'href' => route($href)]
            : null;
    }

    /** Strict reading for the menu: a deadline without date is not open (as the old menus did). */
    private function open(mixed $closesAt): bool
    {
        return $closesAt !== null && Carbon::now()->lt(Carbon::parse($closesAt));
    }

    private function strip(array $nav): array
    {
        return array_map(fn (array $group): array => [
            'label' => $group['label'],
            'items' => array_map(fn (array $entry): array => $this->clean($entry), $group['items']),
        ], $nav);
    }

    private function clean(array $entry): array
    {
        unset($entry['route']);

        return $entry;
    }
}
