{{-- Create or edit an hour bonus: its reason, its hours and the people who receive it, with the default lists --}}
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
    $presets = $defaultlists
        ->map(fn($list) => ['label' => trim($list->name), 'values' => $list->users->modelKeys()])
        ->values()
        ->all();
    $deadline = \Config::bonus_timestamp();
@endphp
<x-layout.app :title="$editing ? 'Editar bono' : 'Nuevo bono'" :breadcrumbs="[
    ['label' => 'Inicio', 'href' => route('home')],
    ['label' => 'Bonos', 'href' => route('secretary.bonus.list')],
    ['label' => $editing ? 'Editar' : 'Nuevo'],
]">
    <x-ui.page-header :title="$editing ? $bonus->reason : 'Nuevo bono'">
        @if ($deadline)
            <x-slot:meta>
                <x-ui.deadline label="Registro de bonos" variant="inline" :closes-at="\Carbon\Carbon::parse($deadline)" />
            </x-slot:meta>
        @endif
        @if ($editing)
            <x-slot:actions>
                @include('bonus.partials.remove', ['bono' => $bonus, 'dialogId' => 'remove-bonus'])
            </x-slot:actions>
        @endif
    </x-ui.page-header>

    <x-ui.card>
        <x-ui.form :action="$route" id="bonus-form" :fields="['reason' => 'Motivo', 'hours' => 'Horas', 'users' => 'Personas']">
            @if ($editing)
                <input type="hidden" name="_id" value="{{ $bonus->id }}">
            @endif

            <div class="grid grid-cols-[minmax(0,1fr)] gap-5 md:grid-cols-2">
                <x-ui.field name="reason" label="Motivo" :value="$bonus->reason ?? null" required />
                <x-ui.field name="hours" label="Horas" type="number" :value="$bonus->hours ?? null" min="0.5" max="99.99"
                    step="0.01" inputmode="decimal" suffix="h" required />
            </div>

            <x-ui.picker name="users[]" label="Personas" :options="$people" :selected="$editing ? $bonus->users->modelKeys() : []" :presets="$presets" />

            <x-slot:actions>
                <x-ui.button type="submit" variant="primary" icon="puzzle">Guardar bono</x-ui.button>
                <x-ui.button variant="ghost" :href="route('secretary.bonus.list')">Cancelar</x-ui.button>
            </x-slot:actions>
        </x-ui.form>
    </x-ui.card>
</x-layout.app>
