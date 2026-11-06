{{--
    Purified HTML (descriptions, biographies) with the prose styles. The consumer purifies: pass Html::clean($text).
    <x-ui.rich-text :html="Html::clean($evidence->description)" />
    <x-ui.rich-text :html="Html::clean($user->bio)" size="sm" :clamp="3" />
--}}
@props(['html' => '', 'size' => 'md', 'clamp' => null])
@php
    $clampClass = match ((int) $clamp) {
        2 => 'line-clamp-2',
        3 => 'line-clamp-3',
        4 => 'line-clamp-4',
        5 => 'line-clamp-5',
        6 => 'line-clamp-6',
        default => null,
    };
@endphp
<div {{ $attributes->class(array_filter(['prose-ui', $size === 'sm' ? 'text-sm' : 'text-base', $clampClass])) }}>
    {!! $html !!}</div>
