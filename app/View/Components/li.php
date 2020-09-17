<?php

namespace App\View\Components;

use Illuminate\Support\Str;
use Illuminate\View\Component;

class li extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $route;
    public $instance;
    public $icon;
    public $name;

    public $secondaries;

    public function __construct($route,$icon="",$name,$secondaries="")
    {
        $this->route = $route;
        $this->instance = \Instantiation::instance();
        $this->icon = $icon;
        $this->name = $name;
        $this->secondaries = $secondaries;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.li');
    }
}
