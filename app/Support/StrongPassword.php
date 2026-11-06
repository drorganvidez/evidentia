<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

/**
 * The rule of every new password: at least eight characters with an uppercase and a lowercase letter, a number and a
 * symbol. resources/js/components/passwordRules.js checks the same list while the person types.
 */
final class StrongPassword
{
    public const MIN = 8;

    public static function rule(): Password
    {
        return Password::min(self::MIN)->letters()->mixedCase()->numbers()->symbols();
    }
}
