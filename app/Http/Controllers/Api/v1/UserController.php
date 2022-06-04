<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getAll() {
        return response()->json(User::paginate());
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'surname' => ['required', 'string'],
            'name' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'email' => ['required', 'email'],
            'block' => ['required', 'boolean'],
            'biography' => ['required', 'string'],
            'clean_name' => ['required', 'string'],
            'clean_surname' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ]);
        }

        $user = User::create([
            'surname' => $request['surname'],
            'name' => $request['name'],
            'username' => $request['username'],
            'password' => $request['password'],
            'email' => $request['email'],
            'block' => $request['block'],
            'biography' => $request['biography'],
            'clean_name' => $request['clean_name'],
            'clean_surname' => $request['clean_surname']
        ]);

        return response()->json($user);
    }

    public function getById(Request $request) {
        return response()->json(User::find($request['id']));
    }

    public function updateById(Request $request) {
        $validator = Validator::make($request->all(), [
            'surname' => ['required', 'string'],
            'name' => ['required', 'string'],
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'email' => ['required', 'email'],
            'block' => ['required', 'boolean'],
            'biography' => ['required', 'string'],
            'clean_name' => ['required', 'string'],
            'clean_surname' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->messages()
            ]);
        }

        $user = User::find($request['id']);
        if (!$user) {
            return response()->json([
                'error' => ['id' => 'Invalid id']
            ]);
        }

        $user->update([
            'surname' => $request['surname'],
            'name' => $request['name'],
            'username' => $request['username'],
            'password' => $request['password'],
            'email' => $request['email'],
            'block' => $request['block'],
            'biography' => $request['biography'],
            'clean_name' => $request['clean_name'],
            'clean_surname' => $request['clean_surname']
        ]);

        return response()->json($user);
    }

    public function deleteById(Request $request) {
        $user = User::find($request['id']);
        if (!$user) {
            return response()->json([
                'error' => ['id' => 'Invalid id']
            ]);
        }

        $user->delete();
        return response()->json($user);
    }
}