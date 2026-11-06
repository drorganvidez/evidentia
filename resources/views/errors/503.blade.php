{{-- Maintenance page (php artisan down): the title as it is, no code, and only the way to try again --}}
@include('errors.layout', [
    'code' => '503',
    'icon' => 'wrench',
    'title' => 'En mantenimiento',
    'text' => 'Volvemos en unos minutos.',
    'action' => 'retry',
])
