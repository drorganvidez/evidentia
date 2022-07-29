<?php

namespace App\Http\Livewire;

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
    public $instance_route;
    public $proofs;

    public $info;

    public $attachment;
    public $iteration;

    public $show_button = false;

    public $user_id;

    public $user;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function fix_database(){

        \Instantiation::set_default_connection();
        $instance_found = Instance::where('route', Cookie::get('instance'))->first();
        \Instantiation::set($instance_found);

    }

    public function boot()
    {
        $this->fix_database();
    }

    public function mount($evidence_id, $user_id)
    {
        $this->instance_route = Cookie::get('instance');
        $this->evidence_id = $evidence_id;
        $this->evidence = Evidence::find($evidence_id);
        $this->proofs = Evidence::find($this->evidence_id)->proofs->sortByDesc('created_at');
        $this->user_id = $user_id;
        $this->user = User::find($this->user_id);
    }

    public function upload()
    {
        /*
        $this->validate([
            'files.*' => 'max:1024', // 1MB Max
        ]);
        */

        $this->fix_database();

        foreach ($this->files as $file) {

            $name = $file->getClientOriginalName();
            $type = $file->getClientOriginalExtension();
            $size = $file->getSize();

            $path = $this->instance_route.'/proofs/'.$this->user->username.'/evidence_'.$this->evidence_id.'/';
            $full_path = $this->instance_route.'/proofs/'.$this->user->username.'/evidence_'.$this->evidence_id.'/'.$name;

            Storage::putFileAs($path, $file, $name);

            $file_entity = File::create([
                'name' => $name,
                'type' => $type,
                'route' => $full_path,
                'size' => $size,
            ]);

            $file_entity = \Stamp::compute_file($file_entity);
            $file_entity->save();

            $proof = Proof::create([
                'evidence_id' => $this->evidence_id,
                'file_id' => $file_entity->id
            ]);

            $this->evidence->autosaved = true;
            $this->evidence->save();


        }

        $this->proofs = Evidence::find($this->evidence_id)->proofs->sortByDesc('created_at');

        //clean up
        $this->attachment=null;
        $this->iteration++;

        $this->toggle_button();

        $this->emit('refreshComponent');

        $this->render();

    }

    public function download_file($file_id)
    {
        $file = File::find($file_id);
        return Storage::download($file->route);
    }

    public function delete_file($file_id)
    {
        $file = File::findOrFail($file_id);

        Storage::delete($file->route);

        $file->delete();

        $this->proofs = Evidence::find($this->evidence_id)->proofs->sortByDesc('created_at');
        $this->render();
    }

    public function toggle_button()
    {
        $this->show_button = !$this->show_button;
    }

    public function render()
    {

        return view('livewire.upload-files', [
            'proofs' => $this->proofs
        ]);


    }
}
