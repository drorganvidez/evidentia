<?php

namespace App\Http\Services;

use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;

class ReviewService extends Service
{

    public function __construct()
    {
        parent::__construct(Review::class, ReviewResource::class);

        $this->rules = [
            'score' => 'required|min:0|max:10',
            'comment' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
        ];

    }

    public function create_review($data, $evidence, $status)
    {

        $evidence->status = $status;
        $evidence->save();

        return $this->create($data);
    }

    public function update_review($id, $data, $evidence, $status)
    {
        $evidence->status = $status;
        $evidence->save();

        return $this->update($id, $data);
    }

}