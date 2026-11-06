@php
    // The same request id the error recorder kept with this occurrence and wrote in the log
    $reference = strtoupper(substr(app(\App\Services\Errors\ErrorRecorder::class)->reference(), 0, 8));
@endphp
@include('errors.layout', [
    'code' => '500',
    'title' => 'Algo ha fallado',
    'text' => 'El error ha quedado registrado y el profesorado ya está avisado.',
    'reference' => $reference,
])
