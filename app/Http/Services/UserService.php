<?php

namespace App\Http\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use ReflectionException;

class UserService extends Service
{

    public function __construct()
    {
        parent::__construct(User::class, UserResource::class);
    }

    /****************************************************************************
     * GET STUDENTS
     ****************************************************************************/

    public function get_all_students_except_me() : Collection
    {
        return User::all()->filter(function ($item) {
            return $item->hasRole('STUDENT') && $item->id !== Auth::id();
        })->values();
    }

    /**
     * @throws ReflectionException
     */
    public function get_json_all_students_except_me() : JsonResource
    {
        return $this->transform_to_resource_collection($this->get_all_students_except_me());
    }

    public function get_all_students() : Collection
    {
        return User::all()->filter(function ($item) {
            return $item->hasRole('STUDENT');
        })->values();
    }

    /**
     * @throws ReflectionException
     */
    public function get_json_all_students() : JsonResource
    {
        return $this->transform_to_resource_collection($this->get_all_students());
    }

    /****************************************************************************
     * GET SECRETARIES
     ****************************************************************************/

    public function get_all_secretaries() : Collection
    {
        return User::all()->filter(function ($item) {
            return $item->hasRole('SECRETARY');
        })->values();
    }

    /**
     * @throws ReflectionException
     */
    public function get_json_all_secretaries() : JsonResource
    {
        return $this->transform_to_resource_collection($this->get_all_secretaries());
    }

    /****************************************************************************
     * GET COORDINATORS
     ****************************************************************************/

    public function get_all_coordinators() : Collection
    {
        return User::all()->filter(function ($item) {
            return $item->hasRole('COORDINATOR');
        })->values();
    }

    /**
     * @throws ReflectionException
     */
    public function get_json_all_coordinators() : JsonResource
    {
        return $this->transform_to_resource_collection($this->get_all_coordinators());
    }

    /****************************************************************************
     * GET REGISTER COORDINATORS
     ****************************************************************************/

    public function get_all_register_coordinators() : Collection
    {
        return User::all()->filter(function ($item) {
            return $item->hasRole('REGISTER_COORDINATOR');
        })->values();
    }

    /**
     * @throws ReflectionException
     */
    public function get_json_all_register_coordinators() : JsonResource
    {
        return $this->transform_to_resource_collection($this->get_all_register_coordinators());
    }

    /****************************************************************************
     * GET PRESIDENTS
     ****************************************************************************/

    public function get_all_presidents() : Collection
    {
        return User::all()->filter(function ($item) {
            return $item->hasRole('PRESIDENT');
        })->values();
    }

    /**
     * @throws ReflectionException
     */
    public function get_json_all_presidents() : JsonResource
    {
        return $this->transform_to_resource_collection($this->get_all_presidents());
    }

    /****************************************************************************
     * GET LECTURES
     ****************************************************************************/

    public function get_all_lectures() : Collection
    {
        return User::all()->filter(function ($item) {
            return $item->hasRole('LECTURE');
        })->values();
    }

    /**
     * @throws ReflectionException
     */
    public function get_json_all_lectures() : JsonResource
    {
        return $this->transform_to_resource_collection($this->get_all_lectures());
    }

}