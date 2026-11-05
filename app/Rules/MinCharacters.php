<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MinCharacters implements Rule
{
    use CountsVisibleCharacters;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $min;

    public function __construct($min)
    {
        $this->min = $min;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return $this->visibleLength($value) >= $this->min;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Debe tener un mínimo de '.$this->min.' caracteres.';
    }
}
