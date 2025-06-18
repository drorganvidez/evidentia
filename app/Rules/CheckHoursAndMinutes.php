<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckHoursAndMinutes implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $comparing;

    public function __construct($comparing)
    {
        $this->comparing = $comparing;
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
        $minutes = $value;

        if ($this->comparing == 0 && $value == 0) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Si no hay horas, al menos tiene que haber minutos en la duración';
    }
}
