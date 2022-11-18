<?php

namespace App\View\Components;

use Illuminate\View\Component;

class kanbantableoptionsstudent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $kanbantable;
    public $instance;

    public function __construct($kanbantable)
    {
        $this->kanbantable = $kanbantable;
        $this->instance = \Instantiation::instance();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.kanbantableoptionsstudent');
    }
}
