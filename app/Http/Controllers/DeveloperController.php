<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DeveloperController extends Controller
{
    public function api_docs()
    {
        return view('developer.apidocs');
    }

    public function create_api_token()
    {
        return view('developer.createapitoken');
    }

    public function list_api_tokens()
    {
        $api_tokens = Auth::user()->api_tokens;

        return view('developer.listapitokens', ['api_tokens' => $api_tokens]);
    }

    public function create_api_token_p(Request $request)
    {

        $request->validate([
            'token_name' => 'required|min:5|max:255',
        ]);

        $token_name = $request->input('token_name');
        $token = \Random::getRandomApiToken();

        ApiToken::create([
            'user_id' => Auth::id(),
            'name' => $token_name,
            'token' => Hash::make($token)
        ]);

        $request->session()->flash('token', $token);

        return redirect()->route('developer.apitokens', \Instantiation::instance());

    }
}
