<?php

namespace App\View\Components;

use Illuminate\View\Component;

class meetingcomittee extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $meeting;

    public function __construct($meeting)
    {
        $this->meeting = $meeting;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.meetingcomittee');
    }
}
