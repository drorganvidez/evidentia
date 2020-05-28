<?php

namespace App\View\Components;

use Illuminate\View\Component;

class evidenceoptionscoordinator extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $evidence;
    public $instance;

    public function __construct($evidence)
    {
        $this->evidence = $evidence;
        $this->instance = \Instantiation::instance();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.evidenceoptionscoordinator');
    }
}
