<?php

namespace App\Http\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class Service
{

    protected $model;
    protected array $validation_rules;
    protected Request $request;

    public function __construct($model)
    {
        $this->model = $model;
        $this->request = Request::capture();
        $this->validation_rules = [];
    }

    public function validate($data): JsonResponse
    {
        $validator = Validator::make($data, $this->validation_rules);

        if ($validator->fails()) {
            return response()->json([
                'oh' => 'no',
                'error' => $validator->messages()
            ]);
        }

        return response()->json([
            'oh' => 'yes'
        ]);
    }

    protected function set_validation_rules($array): void
    {
        $this->validation_rules = $array;
    }

    public function validation_rules(): array
    {
        return $this->validation_rules;
    }

    /**
     *  Entity
     *
     */

    public function entity($json_response)
    {

        try {
            $id = $json_response->getData()->id;
            return $this->find($id);
        }catch (\Throwable $exception){
            return null;
        }

    }

    /**
     *  Create
     *
     */

    public function create($data)
    {
        $json_response = $this->create_json_response($data);
        return $this->entity($json_response);
    }

    public function create_json_response($data)
    {
        $json_response = $this->validate($data);

        if($json_response->getData()->oh == "no") {
            return $json_response;
        }

        $entity = $this->model::create($data);

        return response()->json($entity);

    }

    /**
     *  Update
     */

    public function update($id, $new_data)
    {
        $json_response = $this->update_json_response($id, $new_data);
        return $this->entity($json_response);
    }

    public function update_json_response($id,$new_data): JsonResponse
    {

        $entity_res = null;

        $json_response = $this->validate($new_data);

        if($json_response->getData()->oh == "no") {
            return $json_response;
        }

        $bool = $this->model::where('id', $id)->update($new_data);

        if($bool)
            $entity_res = $this->find($id);

        return response()->json($entity_res);
    }

    public function delete($id)
    {
        $entity = $this->model::find($id);
        $entity->delete();
    }

    public function find($id)
    {
        return $this->model::find($id);
    }

    public function find_or_fail($id)
    {
        return $this->model::findOrFail($id);
    }

    public function find_by($array)
    {
        return $this->model::where($array)->first();
    }

    public function find_all_by($array)
    {
        return $this->model::where($array)->get();
    }

    public function all()
    {
        return $this->model::all();
    }

    public function all_sorted_by($field)
    {
        return $this->model::all()->sortBy($field);
    }

    public function all_sorted_by_desc($field)
    {
        return $this->model::all()->sortByDesc($field);
    }

}
