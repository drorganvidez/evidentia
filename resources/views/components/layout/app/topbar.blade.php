{{--
    Topbar of the app layout: menu button (phone), the link back to the parent page, search, work stopwatch, bell, theme and the user menu.
    <x-layout.app.topbar :breadcrumbs="$breadcrumbs" :search="$search" :notifications="$notifications" :user="$user" />
    The menu button dispatches ui-drawer, which the drawer dialog listens to on the window, and mirrors its state in
    aria-expanded (dialogTrigger.js). The user menu also turns the keyboard shortcuts on and off ($store.shortcuts),
    as the drawer does on the phone.
--}}
@props(['breadcrumbs' => [], 'search' => null, 'notifications' => null, 'timer' => null, 'user' => null])
@php
    $fullName = $user !== null ? trim(($user['name'] ?? '') . ' ' . ($user['surname'] ?? '')) : null;
    $roles = $user !== null ? implode(' · ', $user['roles'] ?? []) : '';
@endphp
<header
    class="glass sticky top-3 z-20 flex min-h-13 items-center gap-2 rounded-lg px-2 py-1 md:top-5 md:min-h-14 md:gap-3 md:rounded-full md:py-2 md:pr-2 md:pl-5 print-hidden">
    <x-ui.button icon-only icon="menu" label="Abrir menú" variant="ghost" class="md:hidden" x-data="dialogTrigger"
        data-event="ui-drawer" aria-controls="drawer" aria-haspopup="dialog" aria-expanded="false"
        x-bind:aria-expanded="open ? 'true' : 'false'" x-on:click="show" />
    <x-ui.breadcrumbs :items="$breadcrumbs" class="flex-1" />
    <div class="ml-auto flex shrink-0 items-center gap-1 md:gap-2">
        @if ($search !== null)
            <x-ui.search :action="$search['action']" :placeholder="$search['placeholder'] ?? 'Buscar evidencias'" :name="$search['name'] ?? 'q'" id="field-q-topbar" />
        @endif
        @if ($timer !== null)
            <x-ui.work-timer :state="$timer" />
        @endif
        @if ($notifications !== null)
            <x-ui.bell :count="$notifications['count'] ?? 0" :href="$notifications['href']" />
        @endif
        <x-ui.theme-switch name="theme-topbar" class="hidden md:block" />
        @if ($user !== null)
            <div class="hidden md:block">
                <x-ui.menu label="Menú de usuario" id="user-menu">
                    <x-slot:trigger>
                        <button type="button" x-ref="button" aria-haspopup="menu" aria-controls="user-menu"
                            aria-expanded="false" x-bind:aria-expanded="open ? 'true' : 'false'" x-on:click="toggle"
                            x-on:keydown.down.prevent="openAndFocusFirst" x-on:keydown.up.prevent="openAndFocusLast"
                            class="grid size-9 place-items-center rounded-full transition hover:bg-glass-2 pointer-coarse:size-11">
                            <x-ui.avatar :name="$user['name'] ?? ''" :surname="$user['surname'] ?? null" />
                            <span class="sr-only">Menú de {{ $fullName }}</span>
                        </button>
                    </x-slot:trigger>
                    <li role="none" class="grid px-3 py-2 text-sm">
                        <span class="font-semibold text-ink">{{ $fullName }}</span>
                        @if ($roles !== '')
                            <span class="text-xs text-ink-muted">{{ $roles }}</span>
                        @endif
                    </li>
                    <x-ui.menu.item divider />
                    <x-ui.menu.item :href="$user['profileHref'] ?? url('/')" icon="user-round">Mi perfil</x-ui.menu.item>
                    <x-ui.menu.item icon="keyboard" :checked="true"
                        x-bind:aria-checked="$store.shortcuts.enabled ? 'true' : 'false'"
                        x-on:click="$store.shortcuts.toggle()">Atajos de teclado</x-ui.menu.item>
                    <x-ui.menu.item divider />
                    <x-ui.menu.item icon="log-out" method="post" :action="$user['logoutAction'] ?? url('/logout')">Salir</x-ui.menu.item>
                </x-ui.menu>
            </div>
        @endif
    </div>
</header>
