{{-- Create or edit a default list: its name and its people --}}
@php
    $editing = isset($edit);
    $people = $users
        ->map(
            fn($user) => [
                'value' => $user->id,
                'label' => trim($user->surname) . ', ' . trim($user->name),
                'hint' => $user->username,
            ],
        )
        ->values()
        ->all();
@endphp
<x-layout.app :title="$editing ? 'Editar lista' : 'Nueva lista'" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Listas', 'href' => route('secretary.defaultlist.list')],
    ['label' => $editing ? 'Editar' : 'Nueva'],
]">
    <x-ui.page-header :title="$editing ? $defaultlist->name : 'Nueva lista'">
        @if ($editing)
            <x-slot:actions>
                @include('defaultlist.partials.remove', [
                    'defaultlist' => $defaultlist,
                    'dialogId' => 'remove-list',
                ])
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.card>
        <x-ui.form :action="$route" id="defaultlist-form" :fields="['name' => 'Nombre', 'users' => 'Personas']">
            @if ($editing)
                <input type="hidden" name="_id" value="{{ $defaultlist->id }}">
            @endif

            <x-ui.field name="name" label="Nombre" :value="$defaultlist->name ?? null" required />
            <x-ui.picker name="users[]" label="Personas" :options="$people" :selected="$editing ? $defaultlist->users->modelKeys() : []" />

            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="users">Guardar lista</x-ui.button>
                <x-ui.button variant="ghost" :href="route('secretary.defaultlist.list')">Cancelar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
