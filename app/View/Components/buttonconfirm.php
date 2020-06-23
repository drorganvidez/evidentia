<?php

namespace App\View\Components;

use Illuminate\View\Component;

class buttonconfirm extends Component
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
    public $description;
    public $type;
    public $name;

    public function __construct($id,$route,$title="Confirmación",$description="Este cambio no se puede deshacer.",$type="REMOVE",$name="Eliminar")
    {
        $this->id = $id;
        $this->route = $route;
        $this->instance = \Instantiation::instance();
        $this->title = $title;
        $this->description = $description;
        $this->type = $type;
        $this->name = $name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.buttonconfirm');
    }
}
