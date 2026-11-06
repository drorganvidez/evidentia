@props(['logo' => null])
{{--
    Shell of every mail of Evidentia: the mark (embedded as cid when the template above passes it), the body and the
    footer of the university. Nothing remote; the mark is computed by the top-level template because an anonymous
    component never sees the variables of the view that uses it.
--}}
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
@if ($logo)
<img src="{{ $logo }}" class="logo" width="48" height="48" alt="">
@endif
Evidentia
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
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
