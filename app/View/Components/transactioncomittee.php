<?php

namespace App\View\Components;

use Illuminate\View\Component;

class transactioncomittee extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public $transaction;

    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.transactioncomittee');
    }
}