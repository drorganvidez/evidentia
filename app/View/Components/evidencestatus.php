<?php

namespace App\View\Components;

use Illuminate\View\Component;

class evidencestatus extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $status;
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.evidencestatus');
    }
}
