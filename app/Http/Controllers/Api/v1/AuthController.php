<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string'],
            'password' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->messages()
            ], 200);
        }

        $attemptedUser = User::where('email', '=', $request['email'])->first();

        if (!$attemptedUser || !Hash::check($request['password'], $attemptedUser->password)) {
            return response()->json([
                'status' => false,
                'error' => 'Invalid credentials',
            ]);
        }

        if (!$token = auth('api')->login($attemptedUser)) {
            return response()->json([
                'status' => false,
                'error' => 'Could not login. Please try later'
            ], 401);
        }

        return response()->json([
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        auth('api')->logout();
        return response()->json([
            'success' => true
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => auth("api")->user()
        ]);
    }

    public function refresh(Request $request)
    {
        return response()->json([
            'token' => auth('api')->refresh()
        ]);
    }
}
