{{--
    First element of the body: hidden until it takes the focus, it jumps to the main content.
    The target carries tabindex="-1" so the focus really lands there (the layouts set it on <main id="main">).
    <x-ui.skip-link />
    <x-ui.skip-link target="#form">Saltar al formulario</x-ui.skip-link>
--}}
@props(['target' => '#main'])
<a href="{{ $target }}"
    {{ $attributes->class(['sr-only font-semibold focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:rounded-full focus:bg-brand focus:px-4 focus:py-2 focus:text-on-brand focus:shadow-glow']) }}>{{ $slot->isEmpty() ? 'Saltar al contenido' : $slot }}</a>
