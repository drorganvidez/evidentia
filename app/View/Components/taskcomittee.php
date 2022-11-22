<?php

namespace App\View\Components;

use Illuminate\View\Component;

class taskcomittee extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.taskcomittee');
    }
}
