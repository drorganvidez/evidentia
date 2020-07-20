<?php

namespace App\View\Components;

use Illuminate\View\Component;

class reminder extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $title;
    public $datetime;

    public function __construct($title="",$datetime)
    {
        $this->title = $title;
        $this->datetime = $datetime;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.reminder');
    }
}
