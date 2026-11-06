{{-- Embedded once here: a component attribute is evaluated twice, and the mail would carry two copies --}}
@php($logo = isset($message) ? $message->embed(public_path('img/brand/mark-180.png')) : null)
<x-mail::message :logo="$logo">
{{-- Greeting --}}
# {{ filled($greeting ?? null) ? $greeting : 'Hola' }}

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach
{{-- Quoted text (a reason, a message) --}}
@if (! empty($panel ?? null))
<x-mail::panel>
{{ $panel }}
</x-mail::panel>

@endif
{{-- List (an agenda, some titles) --}}
@if (! empty($items ?? []))
@foreach ($items as $item)
- {{ $item }}
@endforeach

@endif
{{-- Action Button --}}
@isset($actionText)
<x-mail::button :url="$actionUrl" color="primary">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach
{{-- Salutation --}}
{{ filled($salutation ?? null) ? $salutation : 'Evidentia' }}

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
Si el botón no funciona, copia este enlace en el navegador <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
