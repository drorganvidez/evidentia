<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Id extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $id;
    public $edit;

    public function __construct($id,$edit)
    {
        $this->id = $id;
        $this->edit = $edit;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.id');
    }
}
