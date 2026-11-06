{{--
    Stack of notices (session flash and runtime events) with the two live regions of the page. One per page.
    <x-ui.notices />                       reads success, error, warning and light from the session
    <x-ui.notices position="top" />        under the header of the public pages
    <x-ui.notices :messages="[['tone' => 'accepted', 'text' => 'Evidencia guardada']]" />
    The texts travel escaped in data-messages; from JS use $dispatch('ui-notice', { tone, text }) or the store.
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
    $stack = match ($position) {
        'top'
            => 'fixed inset-x-3 top-3 z-40 grid gap-3 md:inset-x-auto md:top-5 md:left-1/2 md:w-96 md:-translate-x-1/2',
        default
            => 'fixed right-5 bottom-5 z-40 grid w-96 max-w-[calc(100vw-1.5rem)] gap-3 max-md:inset-x-3 max-md:right-3 max-md:bottom-24 max-md:w-auto',
    };
@endphp
<div x-data="notices" data-messages="{{ json_encode($messages, JSON_THROW_ON_ERROR) }}"
    x-on:ui-notice.window="receive" {{ $attributes }}>
    <div role="status" class="sr-only" x-text="statusText"></div>
    <div role="alert" class="sr-only" x-text="alertText"></div>
    <div role="region" aria-label="Avisos" class="{{ $stack }}">
        <template x-for="item in items" x-bind:key="item.id">
            <div role="group" x-bind:data-tone="item.tone" x-on:mouseenter="pause(item.id)"
                x-on:mouseleave="resume(item.id)" x-on:focusin="pause(item.id)" x-on:focusout="resume(item.id)"
                class="glass flex items-start gap-3 rounded-lg p-4 text-md text-ink animate-rise motion-reduce:animate-none data-[tone=accepted]:border-accepted/40 data-[tone=accepted]:bg-accepted-glass data-[tone=notice]:border-notice/40 data-[tone=notice]:bg-notice-glass data-[tone=pending]:border-pending/40 data-[tone=pending]:bg-pending-glass data-[tone=rejected]:border-rejected/40 data-[tone=rejected]:bg-rejected-glass">
                <x-lucide-circle-check x-show="item.tone === 'accepted'" class="size-5.5 shrink-0 text-accepted"
                    aria-hidden="true" />
                <x-lucide-circle-x x-show="item.tone === 'rejected'" class="size-5.5 shrink-0 text-rejected"
                    aria-hidden="true" />
                <x-lucide-triangle-alert x-show="item.tone === 'pending'" class="size-5.5 shrink-0 text-pending"
                    aria-hidden="true" />
                <x-lucide-info x-show="item.tone === 'notice'" class="size-5.5 shrink-0 text-notice"
                    aria-hidden="true" />
                <p class="min-w-0 flex-1 pt-0.5" x-text="item.text"></p>
                <x-ui.button icon-only icon="x" label="Cerrar aviso" variant="ghost" size="sm"
                    x-on:click="dismiss(item.id)" class="-mt-1 -mr-1.5" />
            </div>
        </template>
    </div>
</div>
