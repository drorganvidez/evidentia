<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Input extends Component
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
    public $placeholder;
    public $value;
    public $type;
    public $disabled;
    public $step;
    public $required;

    public $edit;
    public $id;
    public $class;

    public function __construct($col,$label="&nbsp;",$attr,$description="",$placeholder="",$value="",$type="text", $disabled=false,$edit=false, $step="", $id="", $class="", $required=true)
    {
        $this->col = $col;
        $this->label = $label;
        $this->attr = $attr;
        $this->description = $description;
        $this->placeholder = $placeholder;
        $this->value = $value;
        $this->type = $type;
        $this->disabled = $disabled;
        $this->step = $step;
        $this->required = $required;

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
        return view('components.input');
    }
}
