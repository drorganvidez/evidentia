{{--
    Background light of the Aurora direction: three slow blobs behind the page, hidden when printing.
    <x-ui.aurora />                  fixed behind the whole page (layouts)
    <x-ui.aurora intense />          brighter and faster blobs (public pages)
    <x-ui.aurora intense static />   absolute inside a frame that is "relative isolate overflow-hidden"
--}}
@props(['intense' => false, 'static' => false])
@php
    $position = $static ? 'absolute inset-0' : 'fixed inset-0';
@endphp
<div aria-hidden="true"
    {{ $attributes->class(['pointer-events-none -z-10 overflow-hidden print-hidden', $position, '[--blob-opacity:.8] dark:[--blob-opacity:.7]' => $intense]) }}>
    <span @class([
        'absolute -top-[22vw] -left-[14vw] size-[56vw] animate-drift-1 rounded-full bg-blob-1 opacity-(--blob-opacity) blur-aurora will-change-transform',
        '[animation-duration:18s]' => $intense,
    ])></span>
    <span @class([
        'absolute top-[6vh] -right-[10vw] size-[44vw] animate-drift-2 rounded-full bg-blob-2 opacity-(--blob-opacity) blur-aurora will-change-transform',
        '[animation-duration:22s]' => $intense,
    ])></span>
    <span @class([
        'absolute -bottom-[26vw] left-[22vw] size-[48vw] animate-drift-3 rounded-full bg-blob-3 opacity-(--blob-opacity) blur-aurora will-change-transform',
        '[animation-duration:26s]' => $intense,
    ])></span>
</div>
