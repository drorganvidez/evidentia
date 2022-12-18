<?php

namespace App\View\Components;

use Illuminate\View\Component;

class issuecardoptionsstudent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $issuecard;
    public $instance;

    public function __construct($issuecard)
    {
        $this->issuecard = $issuecard;
        $this->instance = \Instantiation::instance();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.issuecardoptionsstudent');
    }
}