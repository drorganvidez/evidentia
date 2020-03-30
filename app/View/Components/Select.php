<?php

namespace App\View\Components;

use Illuminate\Foundation\Application;
use Illuminate\View\Component;

class Select extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $col;
    public $label;
    public $attr;
    public $description;
    public $value;
    public $disabled;

    public $edit;
    public $id;
    public $class;

    public function __construct(Application $col,Application $label,Application $attr,Application $description=NULL,$value="", $disabled=false,$edit=false, $id="", $class="")
    {
        $this->col = $col;
        $this->label = $label;
        $this->attr = $attr;
        $this->description = $description;
        $this->value = $value;
        $this->disabled = $disabled;

        $this->edit = $edit;
        $this->id = $id;
        $this->class = $class;

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.select');
    }
}
