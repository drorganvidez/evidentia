{{--
    Stored HTML (descriptions, biographies) with the prose styles. The component purifies it with Html::clean, so a
    view cannot print it raw; an Htmlable (new HtmlString(...)) is trusted and printed as it comes.
    <x-ui.rich-text :html="$evidence->description" />
    <x-ui.rich-text :html="$user->bio" size="sm" :clamp="3" />
--}}
@use('Illuminate\Contracts\Support\Htmlable')
@props(['html' => '', 'size' => 'md', 'clamp' => null])
@php
    // Purifying the output of Html::clean again changes nothing, and the purifier is built once per request
    $markup = $html instanceof Htmlable ? $html->toHtml() : Html::clean(is_scalar($html) ? (string) $html : null);
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
    {!! $markup !!}</div>
