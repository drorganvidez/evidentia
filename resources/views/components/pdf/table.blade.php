{{--
    Table of a PDF document: columns and rows from arrays, key-value pairs with a row header, or hand-made rows in the
    slot. Styled by the stylesheet of pdf.document (.pdf-table).
    <x-pdf.table :columns="['Rol', 'Apellidos', 'Nombre']" :rows="$attendees" />
    <x-pdf.table key-value :rows="['Fecha' => '21/10/2026', 'Lugar' => 'Aula A2.11']" />
    <x-pdf.table :columns="['Identificador', 'Acuerdo']" compact header-tone="plain"><tr><td>…</td><td>…</td></tr></x-pdf.table>
--}}
@props([
    'columns' => null,
    'rows' => null,
    'keyValue' => false,
    'compact' => false,
    'headerTone' => 'brand',
])
@php
    $classes = [
        'pdf-table',
        'pdf-table--compact' => $compact,
        'pdf-table--key-value' => $keyValue,
        $headerTone === 'plain' ? 'pdf-table--plain' : 'pdf-table--brand',
    ];
@endphp
<table {{ $attributes->class($classes) }}>
    @if (!empty($columns))
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th scope="col">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
    @endif
    <tbody>
        @if ($keyValue)
            @foreach ($rows ?? [] as $key => $value)
                <tr>
                    <th scope="row">{{ is_array($value) ? $value[0] ?? '' : $key }}</th>
                    <td>{{ is_array($value) ? $value[1] ?? '' : $value }}</td>
                </tr>
            @endforeach
        @else
            @foreach ($rows ?? [] as $row)
                <tr>
                    @foreach ((array) $row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        @endif
        {{ $slot }}
    </tbody>
</table>
