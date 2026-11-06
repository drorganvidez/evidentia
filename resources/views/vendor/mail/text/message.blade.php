@props(['logo' => null])
<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="config('app.url')">
            Evidentia
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            Evidentia · Universidad de Sevilla · ETSII
            Correo automático. No respondas a este mensaje, entra en la aplicación.
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
