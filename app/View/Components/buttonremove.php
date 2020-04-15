<?php

namespace App\View\Components;

use Illuminate\View\Component;

class buttonremove extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $id;
    public $route;
    public $instance;
    public $title;

    public function __construct($id,$route,$title="Confirmación")
    {
        $this->id = $id;
        $this->route = $route;
        $this->instance = \Instantiation::instance();
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.buttonremove');
    }
}
