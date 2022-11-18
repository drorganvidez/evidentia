<?php

namespace App\View\Components;

use Illuminate\View\Component;

class kanbantablecomittee extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $kanbantable;

    public function __construct($kanbantable)
    {
        $this->kanbantable = $kanbantable;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.kanbantablecomittee');
    }
}
