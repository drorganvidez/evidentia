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

    public function delete_mass_api_token_p(Request $request)
    {
        $items_selected = $request->input("items_selected");

        foreach (explode(',', $items_selected) as $item){

            $api_token = ApiToken::find($item);

            if($api_token != null) {

                if($api_token->user_id == Auth::id()){

                    $api_token->delete();

                }

            }

        }

        return redirect()->route('developer.apitokens', \Instantiation::instance())->with('success', 'Los tokens seleccionados fueron borrados con éxito');
    }
}
