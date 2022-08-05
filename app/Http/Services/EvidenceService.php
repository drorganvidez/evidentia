<?php

namespace App\Http\Services;

use App\Http\Resources\EvidenceResource;
use App\Models\Committee;
use App\Models\Evidence;
use App\Models\File;
use App\Models\Proof;
use App\Rules\CheckHoursAndMinutes;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use ZipArchive;

class EvidenceService extends Service
{

    public function __construct()
    {
        parent::__construct(Evidence::class, EvidenceResource::class);

        $this->rules = [
            'title' => 'required|min:5|max:255',
            'hours' => ['required_without:minutes','nullable','numeric','sometimes','max:99',new CheckHoursAndMinutes(request()->input('minutes'))],
            'minutes' => ['required_without:hours','nullable','numeric','sometimes','max:60',new CheckHoursAndMinutes(request()->input('hours'))],
            'description' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
        ];

    }

    public function calculate_stamp($id) : void
    {
        $evidence = Evidence::find($id);
        $evidence = \Stamp::compute_evidence($evidence);
        $evidence->save();
    }

    /**
     * @throws ReflectionException
     */
    public function get_evidences_by_committee_and_status($committee_query, $status) : JsonResource
    {
        $committee = Committee::where(['name' => $committee_query])->first();

        if($committee == null){

            if($status == 'DRAFT'){
                $evidences = Evidence::where([
                    'user_id' => Auth::id(),
                    'status' => 'DRAFT',
                    'temp' => false,
                    'last' => true
                ])->get();
            }else{

                $evidences = Evidence::where([
                    'user_id' => Auth::id(),
                    'status' => $status
                ])->get();

                if($status == 'PENDING'){
                    $guest_evidences = Evidence::where([
                        'guest_id' => Auth::id(),
                        'status' => 'PENDING'
                    ])->get();
                    $evidences = $evidences->concat($guest_evidences);
                }

                if($status == 'ACCEPTED'){
                    $guest_evidences = Evidence::where([
                        'guest_id' => Auth::id(),
                        'status' => 'ACCEPTED'
                    ])->get();
                    $evidences = $evidences->concat($guest_evidences);
                }

                if($status == 'REJECTED'){
                    $guest_evidences = Evidence::where([
                        'guest_id' => Auth::id(),
                        'status' => 'REJECTED'
                    ])->get();
                    $evidences = $evidences->concat($guest_evidences);
                }

                $evidences = $evidences->sortByDesc('updated_at');

            }

        } else {

            if($status == 'DRAFT'){
                $evidences = Evidence::where([
                    'user_id' => Auth::id(),
                    'committee_id' => $committee->id,
                    'status' => 'DRAFT',
                    'temp' => false,
                    'last' => true
                ])->get()->sortByDesc('updated_at');
            } else {
                $evidences = Evidence::where([
                    'user_id' => Auth::id(),
                    'committee_id' => $committee->id,
                    'status' => $status
                ])->get();

                if($status == 'PENDING'){
                    $guest_evidences = Evidence::where([
                        'guest_id' => Auth::id(),
                        'status' => 'PENDING',
                        'committee_id' => $committee->id,
                    ])->get();
                    $evidences = $evidences->concat($guest_evidences);
                }

                if($status == 'ACCEPTED'){
                    $guest_evidences = Evidence::where([
                        'guest_id' => Auth::id(),
                        'status' => 'ACCEPTED',
                        'committee_id' => $committee->id,
                    ])->get();
                    $evidences = $evidences->concat($guest_evidences);
                }

                if($status == 'REJECTED'){
                    $guest_evidences = Evidence::where([
                        'guest_id' => Auth::id(),
                        'status' => 'REJECTED',
                        'committee_id' => $committee->id,
                    ])->get();
                    $evidences = $evidences->concat($guest_evidences);
                }

                $evidences = $evidences->sortByDesc('updated_at');

            }

        }

        return $this->transform_to_resource_collection($evidences);

    }

    public function get_all_evidences_by_committee(Committee $committee)
    {
        $evidences = collect();

        $evidences_pending = Evidence::where([
            'committee_id' => $committee->id,
            'last' => true,
            'temp' => false,
            'status' => 'PENDING'
            ])->get();

        $evidences_accepted = Evidence::where([
            'committee_id' => $committee->id,
            'last' => true,
            'temp' => false,
            'status' => 'ACCEPTED'
        ])->get();

        $evidences_rejected = Evidence::where([
            'committee_id' => $committee->id,
            'last' => true,
            'temp' => false,
            'status' => 'REJECTED'
        ])->get();

        $evidences = $evidences->concat($evidences_pending)->concat($evidences_accepted)->concat($evidences_rejected);

        $evidences = $evidences->sortByDesc('updated_at');

        return $this->transform_to_resource_collection($evidences);

    }

    public function recursive_delete_evidence($evidence)
    {

        $evidence_previous = Evidence::find($evidence->points_to);

        // we delete all files
        $this->delete_files($evidence);
        Storage::deleteDirectory(\Instantiation::instance().'/proofs/'.Auth::user()->username.'/evidence_'.$evidence->id);
        $evidence->delete();

        if($evidence_previous != null)
        {
            $this->recursive_delete_evidence($evidence_previous);
        }
    }

    public function upload_file($file, $instance, Authenticatable $user, Evidence $evidence)
    {
        $name = $file->getClientOriginalName();
        $type = $file->getClientOriginalExtension();
        $size = $file->getSize();

        $path = $instance.'/proofs/'.$user->username.'/evidence_'.$evidence->id.'/';
        $full_path = $instance.'/proofs/'.$user->username.'/evidence_'.$evidence->id.'/'.$name;

        Storage::putFileAs($path, $file, $name);

        $file_entity = File::create([
            'name' => $name,
            'type' => $type,
            'route' => $full_path,
            'size' => $size,
        ]);

        $file_entity = \Stamp::compute_file($file_entity);
        $file_entity->save();

        Proof::create([
            'evidence_id' => $evidence->id,
            'file_id' => $file_entity->id
        ]);
    }

    public function delete_files($evidence)
    {
        foreach($evidence->proofs as $proof)
        {
            $proof->file->delete();
        }
    }

    public function get_evidences_by_user_and_status(Authenticatable $user, string $status){

        $evidences = Evidence::where([
            'user_id' => $user->id,
            'status' => $status
        ])->get();

        if($status == 'PENDING' || $status == 'ACCEPTED' || $status == 'REJECTED'){
            $guest_evidences = Evidence::where([
                'guest_id' => $user->id,
                'status' => $status,
                'temp' => false,
                'last' => true
            ])->get();
            $evidences = $evidences->concat($guest_evidences);
        }

        return $evidences->sortByDesc('updated_at');
    }

    /**
     * @throws ReflectionException
     */
    public function get_json_evidences_by_user_and_status(Authenticatable $user, string $status) : JsonResource{
        return $this->transform_to_resource_collection($this->get_evidences_by_user_and_status($user, $status));
    }

    public function get_evidences_by_user(Authenticatable $user){

        return Evidence::where([
            'user_id' => $user->id,
            'temp' => false,
            'last' => true
        ])->get()->sortByDesc('updated_at');
    }

    /**
     * @throws ReflectionException
     */
    public function get_json_evidences_by_user(Authenticatable $user): JsonResource
    {
        return $this->transform_to_resource_collection($this->get_evidences_by_user($user));
    }

    /**
     * @throws ReflectionException
     */
    public function evidences_in_draft_by_user(Authenticatable $user): JsonResource
    {
        $evidences = Evidence::where([
            'user_id' => $user->id,
            'status' => 'DRAFT',
            'temp' => false,
            'last' => true
        ])->get()->sortByDesc('updated_at');

        return $this->transform_to_resource_collection($evidences);
    }

    public function count_evidences_in_draft_by_user(Authenticatable $user): int
    {
        return Evidence::where([
            'user_id' => $user->id,
            'status' => 'DRAFT',
            'temp' => false,
            'last' => true
        ])->count();
    }

    /**
     * @throws ReflectionException
     */
    public function evidences_pending_by_user(Authenticatable $user): JsonResource
    {
        $evidences = $this->get_evidences_by_user_and_status(Auth::user(), 'PENDING');
        return $this->transform_to_resource_collection($evidences);
    }

    public function count_evidences_pending_by_user(Authenticatable $user): int
    {
        return count($this->get_evidences_by_user_and_status(Auth::user(), 'PENDING'));
    }

    /**
     * @throws ReflectionException
     */
    public function evidences_accepted_by_user(Authenticatable $user): JsonResource
    {
        $evidences = $this->get_evidences_by_user_and_status(Auth::user(), 'ACCEPTED');
        return $this->transform_to_resource_collection($evidences);
    }

    public function count_evidences_accepted_by_user(Authenticatable $user): int
    {
        return count($this->get_evidences_by_user_and_status(Auth::user(), 'ACCEPTED'));
    }

    /**
     * @throws ReflectionException
     */
    public function evidences_rejected_by_user(Authenticatable $user): JsonResource
    {
        $evidences = $this->get_evidences_by_user_and_status(Auth::user(), 'REJECTED');
        return $this->transform_to_resource_collection($evidences);
    }

    public function count_evidences_rejected_by_user(Authenticatable $user): int
    {
        return count($this->get_evidences_by_user_and_status(Auth::user(), 'REJECTED'));
    }

    private function collection_hours($collection)
    {
        $hours =  $collection->map(function ($item, $key) {
            return $item->hours;
        });
        return $hours->sum();
    }

    public function evidences_hours_by_user(Authenticatable $user) : float
    {
        $evidences = $this->get_evidences_by_user_and_status($user, 'ACCEPTED');
        return $this->collection_hours($evidences);
    }

    public function zip_evidence(Evidence $evidence)
    {

        $username = $evidence->user->username;
        $instance = \Instantiation::instance();

        $rootPath = realpath(storage_path('app').'/'.$instance.'/proofs/'.$username.'/evidence_'.$evidence->id);
        $zipcreated = storage_path('app').'/'.$instance.'/proofs/'.$username.'/evidence_'.$evidence->id.'.zip';

        $zip = new ZipArchive();
        $zip->open($zipcreated, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Add root folder
        $zip->addEmptyDir('evidence_'.$evidence->id);

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, 'evidence_'.$evidence->id.'/'.$relativePath);
            }
        }

        $zip->close();

    }

    public function zip_evidences($evidences)
    {
        $username = Auth::user()->username;
        $instance = \Instantiation::instance();

        $now = Carbon::now();
        $zip_name = $instance.'/proofs/'.$username.'/ ' .$username . '_evidences_' . $now . '.zip';
        $zipcreated = storage_path('app').'/'. $zip_name;

        $zip = new ZipArchive();
        $zip->open($zipcreated, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach($evidences as $evidence)
        {

            $rootPath = realpath(storage_path('app').'/'.$instance.'/proofs/'.$username.'/evidence_'.$evidence->id);

            // Add root folder
            $zip->addEmptyDir('evidence_'.$evidence->id);

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file)
            {
                // Skip directories (they would be added automatically)
                if (!$file->isDir())
                {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);

                    // Add current file to archive
                    $zip->addFile($filePath, 'evidence_'.$evidence->id.'/'.$relativePath);
                }
            }
        }

        $zip->close();

        return $zip_name;
    }

    public function download_zip(Evidence $evidence)
    {
        $username = Auth::user()->username;
        $instance = \Instantiation::instance();

        $response = Storage::download($instance.'/proofs/'.$username.'/evidence_'.$evidence->id.'.zip');
        ob_end_clean();
        return $response;
    }

    public function download_zip_by_name(string $zip_name)
    {

        $response = Storage::download($zip_name);
        ob_end_clean();
        return $response;
    }

}