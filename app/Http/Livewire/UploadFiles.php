<?php

namespace App\Http\Livewire;

use App\Http\Services\EvidenceService;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Instance;
use App\Models\Proof;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadFiles extends Component
{

    use WithFileUploads;

    public $files = [];

    public $evidence_id;
    public $evidence;
    public $proofs;

    public $info;

    public $attachment;
    public $iteration;

    public $show_button = false;

    public $user_id;

    public $user;

    protected $listeners = ['refreshComponent' => '$refresh'];

    private $evidence_service;

    public function mount($evidence_id)
    {
        $this->evidence_id = $evidence_id;
        $this->evidence = Evidence::find($evidence_id);
        $this->user_id = $this->evidence->user->id;
        $this->user = User::find($this->user_id);
        $this->evidence_service = new EvidenceService();
    }

    public function upload()
    {

        /*
        $this->validate([
            'files.*' => 'max:1024', // 1MB Max
        ]);
        */

        foreach ($this->files as $file) {

            $evidence_service = new EvidenceService();
            $evidence_service->upload_file($file, \Instantiation::instance(), $this->user, $this->evidence);

        }

        //clean up
        $this->attachment=null;
        $this->iteration++;

        $this->toggle_button();

        $this->evidence->autosaved = true;
        $this->evidence->save();

    }

    public function download_file($file_id)
    {
        $file = File::find($file_id);
        return Storage::download($file->route);
    }

    public function delete_file($file_id)
    {
        $file = File::find($file_id);
        Storage::delete($file->route);
        $file->delete();
    }

    public function toggle_button()
    {
        $this->show_button = !$this->show_button;
    }

    public function render()
    {

        $this->proofs = Evidence::find($this->evidence_id)->proofs->sortByDesc('created_at');

        return view('livewire.upload-files', [
            'proofs' => collect()
        ]);


    }
}
