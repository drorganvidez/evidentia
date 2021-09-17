<?php

namespace App\Http\Services;

use App\Models\User;

class UserService extends Service
{

    public function __construct()
    {
        parent::__construct(User::class);

        parent::set_validation_rules([
        ]);

    }

    public function all_except_logged()
    {
        return User::where('id', '!=', auth()->id())->get();
    }

}