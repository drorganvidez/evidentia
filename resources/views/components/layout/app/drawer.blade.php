{{--
    Drawer of the phone: the sidebar in full, the search, the theme with its labels, the switch of the keyboard
    shortcuts ($store.shortcuts) and the account as a flat list, inside a dialog that opens on the ui-drawer window
    event sent by the menu button of the topbar.
    <x-layout.app.drawer :nav="$nav" :deadline="$deadline" :subtitle="$subtitle" :user="$user" :search="$search" />
--}}
@props(['nav' => [], 'deadline' => null, 'subtitle' => null, 'user' => null, 'search' => null])
@php
    $fullName = $user !== null ? trim(($user['name'] ?? '') . ' ' . ($user['surname'] ?? '')) : null;
    $roles = $user !== null ? implode(' · ', $user['roles'] ?? []) : '';
    $entry =
        'flex w-full items-center gap-3 rounded-sm px-3 py-2.5 text-left text-md font-medium text-ink no-underline transition hover:bg-glass-2 pointer-coarse:min-h-11';
@endphp
<x-ui.dialog id="drawer" variant="drawer" title="Menú" x-on:ui-drawer.window="show" class="print-hidden">
    <div class="grid gap-6">
        @if ($search !== null)
            <x-ui.search :action="$search['action']" :placeholder="$search['placeholder'] ?? 'Buscar evidencias'" :name="$search['name'] ?? 'q'" id="field-q-drawer" :hide-on-phone="false"
                class="px-1" />
        @endif
        <div class="grid gap-6">
            <x-layout.app.sidebar :nav="$nav" :deadline="$deadline" :subtitle="$subtitle" :brand="false"
                :rail="false" />
        </div>
        <section aria-labelledby="drawer-theme-title" class="grid gap-2 border-t border-rule pt-4">
            <h3 id="drawer-theme-title" class="px-3 text-2xs font-bold tracking-widest text-ink-muted uppercase">Tema
            </h3>
            <div class="px-1">
                <x-ui.theme-switch name="theme-drawer" labels block />
            </div>
        </section>
        <div class="border-t border-rule px-1 pt-3">
            <x-ui.choice type="switch" name="shortcuts-drawer" label="Atajos de teclado" :checked="true"
                x-bind:checked="$store.shortcuts.enabled" x-on:change="$store.shortcuts.toggle()" />
        </div>
        @if ($user !== null)
            <section aria-labelledby="drawer-account-title" class="grid gap-2 border-t border-rule pt-4">
                <h3 id="drawer-account-title" class="px-3 text-2xs font-bold tracking-widest text-ink-muted uppercase">
                    Cuenta
                </h3>
                <x-ui.person :name="$user['name'] ?? ''" :surname="$user['surname'] ?? null" :role="$roles !== '' ? $roles : null" class="px-3 py-1" />
                <ul class="grid gap-0.75">
                    <li>
                        <a href="{{ $user['profileHref'] ?? url('/') }}" class="{{ $entry }}">
                            <x-lucide-user-round class="size-4.5" aria-hidden="true" />
                            Mi perfil
                        </a>
                    </li>
                    <li>
                        <form method="post" action="{{ $user['logoutAction'] ?? url('/logout') }}">
                            @csrf
                            <button type="submit" class="{{ $entry }}">
                                <x-lucide-log-out class="size-4.5" aria-hidden="true" />
                                Salir
                            </button>
                        </form>
                    </li>
                </ul>
            </section>
        @endif
    </div>
</x-ui.dialog>
