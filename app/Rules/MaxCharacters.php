<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MaxCharacters implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public $max;

    public function __construct($max)
    {
        $this->max = $max;
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
        return strlen(trim(strip_tags(trim($value)))) <= $this->max;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Debe tener un máximo de '.$this->max.' caracteres.';
    }
}
