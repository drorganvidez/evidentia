<?php

namespace App\View\Components;

use Illuminate\View\Component;

class incidencemanagecoordinator extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $incidence;
    public $instance;

    public function __construct($incidence)
    {
        $this->incidence = $incidence;
        $this->instance = \Instantiation::instance();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.incidencemanagecoordinator');
    }
}
