<?php

namespace App\View\Components;

use Illuminate\View\Component;

class attendeestatus extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $attendee;

    public function __construct($attendee)
    {
        $this->attendee = $attendee;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.attendeestatus');
    }
}
