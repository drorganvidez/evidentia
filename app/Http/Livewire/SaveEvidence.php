<?php

namespace App\Http\Livewire;

use App\Models\Evidence;
use App\Models\Instance;
use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class SaveEvidence extends Component
{
    public $evidence_temp;
    public $evidence_temp_id;
    public $committees;
    public $route_draft;
    public $route_publish;

    protected $listeners = [
        'saveTitle' => 'saveTitle',
        'saveHours' => 'saveHours',
        'saveCommittee' => 'saveCommittee',
        'saveDescription' => 'saveDescription'
        ];

    public function mount($evidence_temp, $evidence_temp_id, $committees, $route_draft, $route_publish){
        $this->evidence_temp_id = $evidence_temp_id;
        $this->evidence_temp = $evidence_temp;
        $this->committees = $committees;
        $this->route_draft = $route_draft;
        $this->route_publish = $route_publish;
    }

    public function boot()
    {
        \Instantiation::set_default_connection();
        $instance_found = Instance::where('route', Cookie::get('instance'))->first();
        \Instantiation::set($instance_found);
    }

    public function saveTitle($title)
    {
        $this->evidence_temp->title = $title;
        $this->evidence_temp->autosaved = true;
        $this->evidence_temp->save();
    }

    public function saveHours($hours)
    {
        $this->evidence_temp->hours = $hours;
        $this->evidence_temp->autosaved = true;
        $this->evidence_temp->save();
    }

    public function saveCommittee($committee_id)
    {
        $this->evidence_temp->committee_id = $committee_id;
        $this->evidence_temp->autosaved = true;
        $this->evidence_temp->save();
    }

    public function saveDescription($description)
    {
        $this->evidence_temp->description = $description;
        $this->evidence_temp->autosaved = true;
        $this->evidence_temp->save();
    }

    public function render()
    {
        return view('livewire.save-evidence');
    }
}
