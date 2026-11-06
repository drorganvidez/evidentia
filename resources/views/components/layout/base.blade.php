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
    {{-- Visible status while the next page loads (pageProgress.js); decorative, the browser reports the load itself --}}
    <div x-data="pageProgress" x-bind:data-active="active ? 'true' : null" aria-hidden="true"
        class="group pointer-events-none fixed inset-x-0 top-0 z-50 h-0.75 print-hidden">
        <div
            class="h-full origin-left bg-linear-to-r from-aurora-500 to-cyan-400 opacity-0 shadow-glow-sm transition-opacity [transform:scaleX(0)] group-data-[active=true]:animate-progress group-data-[active=true]:opacity-100 motion-reduce:group-data-[active=true]:animate-none motion-reduce:group-data-[active=true]:[transform:scaleX(0.5)]">
        </div>
    </div>
    {{ $slot }}
</body>

</html>
