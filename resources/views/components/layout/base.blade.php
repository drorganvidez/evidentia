@props(['title' => null])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ? $title.' · '.config('app.name') : config('app.name') }}</title>
    <meta name="theme-color" media="(prefers-color-scheme: light)" content="#f3f7fe">
    <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#151922">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    {{-- The Vite client reads this meta to put the nonce on the styles it injects during development --}}
    <meta property="csp-nonce" nonce="{{ Vite::cspNonce() }}">
    {{-- Applies the stored theme before the first paint. Classic script, allowed by its nonce. --}}
    <script nonce="{{ Vite::cspNonce() }}">{!! file_get_contents(resource_path('js/theme-init.js')) !!}</script>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body {{ $attributes->merge(['class' => 'min-h-dvh bg-canvas font-sans text-ink antialiased']) }}>
    {{ $slot }}
</body>
</html>
