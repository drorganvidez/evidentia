<?php

namespace App\Http\Services;

use App\Models\User;

class UserService
{
    public function all_except_logged()
    {
        return User::where('id', '!=', auth()->id())->orderBy('surname')->get();
    }

    public function delete($id)
    {
        User::find($id)?->delete();
    }
}
