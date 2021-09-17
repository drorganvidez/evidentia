<?php

namespace App\Http\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class Service
{

    protected string $model;
    protected array $validation_rules;
    protected Request $request;

    public function __construct($model)
    {
        $this->model = $model;
        $this->request = Request::capture();
    }

    public function validate()
    {
        $this->request->validate($this->validation_rules);
    }

    protected function set_validation_rules($array)
    {
        $this->validation_rules = $array;
    }

    public function validation_rules()
    {
        return $this->validation_rules;
    }

    public function create($array)
    {
        $this->validate();
        return $this->model::create($array);
    }

    public function update($id,$array)
    {
        $this->validate();
        return $this->model::where('id', $id)->update($array);
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
