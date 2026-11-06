{{-- Public signature of a meeting: the data of its call and the credentials of the person who signs --}}
@php
    $request = $signature_sheet->meetingRequest;
@endphp
<x-layout.public title="Firmar asistencia">
    <x-ui.card title="Firmar asistencia" :level="1">
        <div class="grid grid-cols-[minmax(0,1fr)] gap-5">
            @if ($request)
                <x-ui.dl dense>
                    <x-ui.dl.item term="Reunión">{{ $request->title }}</x-ui.dl.item>
                    <x-ui.dl.item term="Fecha">
                        <x-ui.time :value="$request->datetime" format="absolute" pattern="l j \d\e F, H:i" :mono="false" />
                    </x-ui.dl.item>
                    @if (filled($request->place))
                        <x-ui.dl.item term="Lugar">{{ $request->place }}</x-ui.dl.item>
                    @endif
                    @if ($request->committee)
                        <x-ui.dl.item term="Comité"><x-ui.committee-chip :committee="$request->committee" /></x-ui.dl.item>
                    @endif
                </x-ui.dl>
            @else
                <x-ui.alert title="Hoja de firmas sin convocatoria" compact />
            @endif
            <x-ui.form :action="route('sign_p')" id="sign-form" busy-label="Firmando" :fields="['email' => 'Correo electrónico', 'password' => 'Contraseña']">
                <input type="hidden" name="signature_sheet" value="{{ $signature_sheet->random_identifier }}">
                <x-ui.field name="email" label="Correo electrónico" type="email" size="lg" icon="mail"
                    autocomplete="username" required />
                <x-ui.field name="password" label="Contraseña" type="password" size="lg" icon="lock"
                    autocomplete="current-password" required />
                <x-slot:actions>
                    <x-ui.button type="submit" variant="primary" size="lg" icon="file-signature"
                        block>Firmar</x-ui.button>
                </x-slot:actions>
            </x-ui.form>
        </div>
    </x-ui.card>
</x-layout.public>
