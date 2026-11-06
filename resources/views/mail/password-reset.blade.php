{{-- Embedded once here: a component attribute is evaluated twice, and the mail would carry two copies --}}
@php($logo = isset($message) ? $message->embed(public_path('img/brand/mark-180.png')) : null)
<x-mail::message :logo="$logo">
# Hola, {{ $user->name }}

Hemos recibido una petición para restablecer la contraseña de tu cuenta de Evidentia.

<x-mail::button :url="route('password.custom_update', $token->token)">Elegir una contraseña nueva</x-mail::button>

El enlace caduca en 24 horas. Si no has pedido este cambio, no tienes que hacer nada.

Evidentia
</x-mail::message>
