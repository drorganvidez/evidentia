<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ViewEvidence extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $evidence;

    public function __construct($evidence)
    {
        $this->evidence = $evidence;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.view-evidence');
    }
}
