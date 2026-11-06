{{--
    HTML document of the new UI. The layouts (app, public) build on it.
    <x-layout.base title="Mis evidencias">...</x-layout.base>
    <x-layout.base entry="resources/js/public.js">...</x-layout.base>
--}}
@props(['title' => null, 'entry' => 'resources/js/app.js'])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' · ' . config('app.name') : config('app.name') }}</title>
    <meta name="theme-color" media="(prefers-color-scheme: light)" content="#f3f7fe">
    <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#151922">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    {{-- The Vite client reads this meta to put the nonce on the styles it injects during development --}}
    <meta property="csp-nonce" nonce="{{ Vite::cspNonce() }}">
    {{-- Applies the stored theme before the first paint. Classic script, allowed by its nonce. --}}
    <script nonce="{{ Vite::cspNonce() }}">
        {!! file_get_contents(resource_path('js/theme-init.js')) !!}
    </script>
    @fonts
    @vite(['resources/css/app.css', $entry])
    {{ $head ?? '' }}
</head>

<body {{ $attributes->merge(['class' => 'min-h-dvh bg-canvas font-sans text-ink antialiased']) }}>
    <a href="#contenido"
        class="sr-only z-50 rounded-full bg-brand px-4 py-2 font-semibold text-on-brand focus:not-sr-only focus:fixed focus:top-4 focus:left-4">Saltar
        al contenido</a>
    {{ $slot }}
</body>

</html>
