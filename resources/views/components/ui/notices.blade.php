{{--
    Stack of notices (session flash and runtime events) with the two live regions of the page. One per page.
    <x-ui.notices />                       reads success, error, warning and light from the session
    <x-ui.notices position="top" />        under the header of the public pages
    <x-ui.notices :messages="[['tone' => 'accepted', 'text' => 'Evidencia guardada', 'href' => $url, 'action' => 'Ver']]" />
    A controller adds a link to its flash with ->with('notice_link', ['href' => $url, 'action' => 'Ver la evidencia']).
    The texts travel escaped in data-messages; from JS use $dispatch('ui-notice', { tone, text, href, action }) or the
    store. Timed notices show the time left as a bar; a notice with a link stays until it is closed.
    The root is display: contents so it takes no row (nor gap) in the grid of the layout; the stack is fixed.
--}}
@props(['messages' => null, 'position' => 'end'])
@php
    $messages ??= array_values(
        array_filter([
            session('success') ? ['tone' => 'accepted', 'text' => (string) session('success')] : null,
            session('error') ? ['tone' => 'rejected', 'text' => (string) session('error')] : null,
            session('warning') ? ['tone' => 'pending', 'text' => (string) session('warning')] : null,
            session('light') ? ['tone' => 'notice', 'text' => (string) session('light')] : null,
        ]),
    );
    // An optional link for the first flash message (for instance «Ver la evidencia» after saving it)
    $link = session('notice_link');
    if ($messages !== [] && is_array($link) && filled($link['href'] ?? null) && !isset($messages[0]['href'])) {
        $messages[0]['href'] = (string) $link['href'];
        $messages[0]['action'] = (string) ($link['action'] ?? 'Ver');
    }
    $stack = match ($position) {
        'top'
            => 'fixed inset-x-3 top-3 z-40 grid gap-3 md:inset-x-auto md:top-5 md:left-1/2 md:w-96 md:-translate-x-1/2',
        default
            => 'fixed right-5 bottom-5 z-40 grid w-96 max-w-[calc(100vw-1.5rem)] gap-3 max-md:inset-x-3 max-md:right-3 max-md:bottom-24 max-md:w-auto',
    };
@endphp
<div x-data="notices" data-messages="{{ json_encode($messages, JSON_THROW_ON_ERROR) }}"
    x-on:ui-notice.window="receive" {{ $attributes->class(['contents']) }}>
    <div role="status" class="sr-only" x-text="statusText"></div>
    <div role="alert" class="sr-only" x-text="alertText"></div>
    <div role="region" aria-label="Avisos" class="{{ $stack }}">
        <template x-for="item in items" x-bind:key="item.id">
            <div role="group" x-bind:data-tone="item.tone" x-on:mouseenter="pause(item.id, 'pointer')"
                x-on:mouseleave="resume(item.id, 'pointer')" x-on:focusin="pause(item.id, 'focus')"
                x-on:focusout="resume(item.id, 'focus')"
                class="group glass relative flex items-start gap-3 overflow-hidden rounded-lg p-4 text-md text-ink animate-rise motion-reduce:animate-none data-[tone=accepted]:border-accepted/40 data-[tone=accepted]:bg-accepted-glass data-[tone=notice]:border-notice/40 data-[tone=notice]:bg-notice-glass data-[tone=pending]:border-pending/40 data-[tone=pending]:bg-pending-glass data-[tone=rejected]:border-rejected/40 data-[tone=rejected]:bg-rejected-glass">
                <x-lucide-circle-check x-show="item.tone === 'accepted'" class="size-5.5 shrink-0 text-accepted"
                    aria-hidden="true" />
                <x-lucide-circle-x x-show="item.tone === 'rejected'" class="size-5.5 shrink-0 text-rejected"
                    aria-hidden="true" />
                <x-lucide-triangle-alert x-show="item.tone === 'pending'" class="size-5.5 shrink-0 text-pending"
                    aria-hidden="true" />
                <x-lucide-info x-show="item.tone === 'notice'" class="size-5.5 shrink-0 text-notice"
                    aria-hidden="true" />
                <div class="grid min-w-0 flex-1 gap-1 pt-0.5">
                    <p x-text="item.text"></p>
                    <a x-show="item.href" x-bind:href="item.href" x-text="item.action"
                        class="justify-self-start font-semibold text-ink underline underline-offset-2 pointer-coarse:inline-flex pointer-coarse:min-h-11 pointer-coarse:items-center"></a>
                </div>
                <x-ui.button icon-only icon="x" label="Cerrar aviso" variant="ghost" size="sm"
                    x-on:click="dismiss(item.id)" class="-mt-1 -mr-1.5" />
                {{-- Time left before a timed notice closes; it stops with the countdown and is absent with reduced motion --}}
                <span x-show="item.timed" aria-hidden="true" x-bind:data-paused="item.paused ? 'true' : null"
                    class="absolute inset-x-0 bottom-0 h-0.75 origin-left bg-brand opacity-70 animate-countdown group-data-[tone=accepted]:bg-accepted group-data-[tone=notice]:bg-notice data-[paused=true]:[animation-play-state:paused] motion-reduce:hidden"></span>
            </div>
        </template>
    </div>
</div>
