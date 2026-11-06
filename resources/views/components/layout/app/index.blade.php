{{--
    Shell of the signed-in app: sidebar (rail on tablets), topbar, notices, main, footer, the bottom bar and the drawer
    of the phone. The page puts its own page-header inside the slot.
    <x-layout.app title="Mis evidencias" :nav="$nav" :breadcrumbs="$breadcrumbs" :deadline="$deadline"
        :notifications="['count' => 3, 'href' => '/avisos']" :search="['action' => route('evidence.list')]">
        <x-ui.page-header title="Mis evidencias" />
        ...
    </x-layout.app>
    nav is a list of groups ['label' => 'Gestión'|null, 'items' => [['label', 'href', 'icon', 'current', 'count', 'countLabel', 'mobile']]];
    mobileNav defaults to the first five items flagged mobile; user to the signed-in person; flash to the session.
    main points at the page-title of the page-header only when the slot has that id; labelled-by gives another id or,
    with false, drops the reference. aurora=false drops the background light (the catalogue frames several shells at
    once and the blurred blobs are costly to paint).
--}}
@props([
    'title' => null,
    'breadcrumbs' => [],
    'nav' => [],
    'mobileNav' => null,
    'deadline' => null,
    'user' => null,
    'notifications' => null,
    'search' => null,
    'subtitle' => null,
    'version' => null,
    'footerLinks' => null,
    'flash' => null,
    'labelledBy' => true,
    'aurora' => true,
])
@php
    // Never a dangling reference: main names the title only when the page renders it
    $titleId = is_string($labelledBy) && $labelledBy !== '' ? $labelledBy : 'page-title';
    $labelled = $labelledBy !== false && str_contains((string) $slot, 'id="' . e($titleId) . '"');
    if ($user === null && Auth::check()) {
        $account = Auth::user();
        $roleNames = [
            'STUDENT' => 'Estudiante',
            'COORDINATOR' => 'Coordinador',
            'SECRETARY' => 'Secretario',
            'REGISTER_COORDINATOR' => 'Coordinador de registro',
            'PRESIDENT' => 'Presidente',
            'LECTURE' => 'Profesor',
        ];
        $user = [
            'name' => (string) $account->name,
            'surname' => (string) $account->surname,
            'roles' => $account->roles
                ->map(fn($role) => $roleNames[$role->rol] ?? (string) $role->slug)
                ->values()
                ->all(),
            'profileHref' => Route::has('profile.view') ? route('profile.view') : url('/'),
            'logoutAction' => Route::has('logout') ? route('logout') : url('/logout'),
        ];
    }
    // Bottom bar of the phone: the first five items flagged mobile, in the order of the groups. The groups and their
    // items may be arrays or collections.
    $mobileNav = collect(
        $mobileNav ??
            collect($nav)
                ->flatMap(fn($group) => collect($group['items'] ?? [])->all())
                ->filter(fn($item) => (bool) ($item['mobile'] ?? false))
                ->take(5),
    )
        ->values()
        ->all();
@endphp
<x-layout.base :title="$title" class="pb-safe-nav md:pb-0">
    <x-slot:head>{{ $head ?? '' }}</x-slot:head>
    <x-ui.skip-link />
    @if ($aurora)
        <x-ui.aurora />
    @endif

    <div
        class="mx-auto grid min-h-dvh max-w-shell grid-cols-1 gap-3 p-3 md:grid-cols-[5rem_minmax(0,1fr)] md:gap-6 md:p-5 lg:grid-cols-[17rem_minmax(0,1fr)]">
        <aside aria-label="Barra lateral"
            class="glass sticky top-5 z-10 hidden h-[calc(100dvh-2.5rem)] flex-col gap-8 overflow-y-auto rounded-xl px-2 py-6 md:flex lg:px-5 print-hidden">
            <x-layout.app.sidebar :nav="$nav" :deadline="$deadline" :subtitle="$subtitle" />
        </aside>

        {{-- minmax(0, 1fr): a long breadcrumb never widens the column past the window --}}
        <div class="grid min-w-0 grid-cols-[minmax(0,1fr)] content-start gap-3 md:gap-6">
            <x-layout.app.topbar :breadcrumbs="$breadcrumbs" :search="$search" :notifications="$notifications" :user="$user" />
            <x-ui.notices :messages="$flash" />
            <main id="main" tabindex="-1"
                @if ($labelled) aria-labelledby="{{ $titleId }}" @endif
                class="grid content-start gap-6 px-0 pt-2 pb-4 outline-none md:gap-8 md:px-2 md:pb-10">
                {{ $slot }}
            </main>
            <x-ui.footer :version="$version" :links="$footerLinks" />
        </div>
    </div>

    @if ($mobileNav !== [])
        <x-ui.nav variant="bar" :items="$mobileNav" />
    @endif
    <x-layout.app.drawer :nav="$nav" :deadline="$deadline" :subtitle="$subtitle" :user="$user"
        :search="$search" />
</x-layout.base>
