<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Cookie;

class DarkMode extends Component
{

    public $is_dark_mode;

    public function mount()
    {
        if(Cookie::get('dark_mode')){
            $this->is_dark_mode = "true";
        }else{
            $this->is_dark_mode = "false";
        }
    }

    public function toggle_css_mode()
    {
        if(Cookie::get('dark_mode')){
            Cookie::queue('dark_mode', false, 60*24*30*3);
        }else{
            Cookie::queue('dark_mode', true, 60*24*30*3);
        }

    }
    public function render()
    {
        return view('livewire.dark-mode');
    }
}
