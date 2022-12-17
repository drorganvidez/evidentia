<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckEndDate implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public $end_date;
    public $start_date;

    public function __construct($end_date, $start_date)
    {
        $this->end_date = $end_date;
        $this->start_date = $start_date;
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
        return  $this-> end_date >= $this->start_date;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Fecha fin debe ser posterior a fecha comienzo';
    }
}
