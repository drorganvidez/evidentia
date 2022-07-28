<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'size' => $this->size,
            'size_for:humans' => $this->sizeForHuman(),
            'type' => $this->type,
            'download_route' => route('download.file', ['instance' => \Instantiation::instance(), 'file_id' => $this->id]),
            'stamp' => $this->stamp,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
