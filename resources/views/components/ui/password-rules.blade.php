{{--
    Requirements of a new password, ticked while the person types (passwordRules.js); the submit button of the form
    stays disabled until all of them hold. The server checks the same rule (App\Support\StrongPassword).
    <x-ui.password-rules for="field-password" confirm="field-password-confirmation" />
    <x-ui.password-rules for="field-password" confirm="field-password-confirmation" optional />   the field may stay empty
--}}
@props(['for' => null, 'confirm' => null, 'optional' => false])
@php
    throw_if(blank($for) || blank($confirm), InvalidArgumentException::class, 'The password rules need both fields.');
    $rules = [
        ['longEnough', 'Al menos ' . \App\Support\StrongPassword::MIN . ' caracteres'],
        ['hasUpper', 'Una letra mayúscula'],
        ['hasLower', 'Una letra minúscula'],
        ['hasNumber', 'Un número'],
        ['hasSymbol', 'Un símbolo, como ! ? # o %'],
        ['matches', 'Las dos contraseñas coinciden'],
    ];
@endphp
<div x-data="passwordRules" data-for="{{ $for }}" data-confirm="{{ $confirm }}"
    data-min="{{ \App\Support\StrongPassword::MIN }}" @if ($optional) data-optional @endif
    {{ $attributes->class(['grid gap-2 rounded-lg bg-glass-2 px-4 py-3']) }}>
    <p class="text-sm font-semibold" id="{{ $for }}-rules-title">La nueva contraseña necesita</p>
    <ul class="grid gap-1.5 text-sm sm:grid-cols-2" aria-labelledby="{{ $for }}-rules-title">
        @foreach ($rules as [$check, $label])
            <li class="flex items-center gap-2" x-bind:data-ok="{{ $check }} ? 'true' : null">
                <x-lucide-circle-check x-show="{{ $check }}" class="size-4 shrink-0 text-accepted"
                    aria-hidden="true" />
                <x-lucide-circle x-show="!{{ $check }}" class="size-4 shrink-0 text-ink-muted"
                    aria-hidden="true" />
                <span>{{ $label }}</span>
                <span class="sr-only" x-text="{{ $check }} ? 'cumplido' : 'pendiente'"></span>
            </li>
        @endforeach
    </ul>
    <p role="status" class="sr-only" x-text="summary"></p>
</div>
