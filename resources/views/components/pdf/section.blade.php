{{--
    Numbered section of a PDF document, as an h2 or an h3, optionally on a new page.
    <x-pdf.section :number="1" title="Datos de la reunión">…</x-pdf.section>
    <x-pdf.section number="2.1" title="Presupuesto" :level="3">…</x-pdf.section>
    <x-pdf.section :number="4" title="Desarrollo de la reunión" page-break>…</x-pdf.section>
    The heading is printed from PHP because blade-formatter does not follow dynamic tags.
--}}
@props(['number' => null, 'title' => null, 'pageBreak' => false, 'level' => 2])
@php
    throw_if(blank($title), InvalidArgumentException::class, 'The pdf section needs a title.');
    $heading = (int) $level === 3 ? 'h3' : 'h2';
    $numberHtml =
        $number !== null && $number !== '' ? '<span class="pdf-section__number">' . e($number) . '</span>' : '';
@endphp
<section {{ $attributes->class(['pdf-section', 'pdf-section--break' => $pageBreak]) }}>
    {!! '<' . $heading . ' class="pdf-section__title">' . $numberHtml !!}{{ $title }}{!! '</' . $heading . '>' !!}
    {{ $slot }}
</section>
