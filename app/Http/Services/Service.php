<?php

namespace App\Http\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use ReflectionClass;

abstract class Service
{

    protected $model;
    protected $resource;
    protected array $rules;
    protected Request $request;

    public function __construct($model, $resource)
    {
        $this->model = $model;
        $this->resource = $resource;
        $this->request = Request::capture();
        $this->rules = [];
    }

    public function validate(): void
    {
        $this->request->validate($this->rules);
    }

    private function validation($data): bool
    {
        $validator = $this->validator($data);
        return !$validator->fails();
    }

    private function validator($data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, $this->rules);
    }

    private function fails($messages) : JsonResponse
    {
        return response()->json([
            'errors' => $messages
        ]);
    }

    public function rules(): array
    {
        return $this->rules;
    }

    protected function set__rules($array): void
    {
        $this->rules = $array;
    }

    /**
     *  Get JSON resource from entity
     *
     * @throws \ReflectionException
     */

    public function transform_to_resource($entity): object
    {
        $resource = new ReflectionClass($this->resource);
        return $resource->newInstance($entity);
    }

    /**
     *  Get JSON collection resource from entities
     *
     * @throws \ReflectionException
     */

    public function transform_to_resource_collection($entities): object
    {
        return $this->resource::collection($entities);
    }


    /**
     * @throws \ReflectionException
     */
    public function create($data): object
    {

        if(!$this->validation($data)){
            return $this->fails($this->validator($data)->messages());
        }

        $entity = $this->model::create($data);

        return $this->transform_to_resource($entity);
    }


    /**
     *  Update
     * @throws \ReflectionException
     */

    public function update($id, $new_data): object
    {

        if(!$this->validation($new_data)){
            return $this->fails($this->validator($new_data)->messages());
        }

        $updated = $this->model::find($id)?->update($new_data);

        if($updated){
            $entity = $this->model::find($id);
            return $this->transform_to_resource($entity);
        } else {
            return response()->json([
                'errors' => ['fatal' => $this->model. ' cannot be updated']
            ]);
        }

    }

    public function delete($id): void
    {
        $entity = $this->model::find($id);
        $entity->delete();
    }

    /**
     *  Find
     * @throws \ReflectionException
     */
    public function find($id): object
    {
        $entity = $this->model::find($id);
        return $this->transform_to_resource($entity);
    }

    /**
     * @throws \ReflectionException
     */
    public function find_by($array): object
    {
        $entity =  $this->model::where($array)->first();
        return $this->transform_to_resource($entity);
    }

    /**
     * @throws \ReflectionException
     */
    public function find_all_by($array): object
    {
        $entities =  $this->model::where($array)->get();
        return $this->transform_to_resource_collection($entities);
    }

    /**
     * @throws \ReflectionException
     */
    public function all(): object
    {
        $entities =  $this->model::all();
        return $this->transform_to_resource_collection($entities);
    }

    public function entity($entity_json)
    {
        $id = json_decode(json_encode($entity_json), false)->id;
        return $this->model::find($id);
    }

    /**
     * @throws \ReflectionException
     */
    public function stringify_entity($entity): string
    {
        $json = $this->transform_to_resource($entity);
        return json_encode($json, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @throws \ReflectionException
     */
    public function stringify_collection($collection): string
    {
        $json = $this->transform_to_resource_collection($collection);
        return json_encode($json, JSON_UNESCAPED_UNICODE);
    }


}