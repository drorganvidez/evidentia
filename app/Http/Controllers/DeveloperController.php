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
        return view('developer.createandeditapitoken', [
            'action' => 'developer.createapitoken_p'
        ]);
    }

    public function list_api_tokens()
    {
        $api_tokens = Auth::user()->api_tokens->sortByDesc('created_at');

        return view('developer.listapitokens', ['api_tokens' => $api_tokens]);
    }

    public function create_api_token_p(Request $request)
    {

        $api_token = ApiToken::where('name', $request->input('name'))->first();

        if($api_token != null) {
            return redirect()->route('developer.createapitoken', \Instantiation::instance())
                ->with('error', "'".   $request->input('name')."' ya ha sido usado para un token. Prueba con otro nombre.");
        }

        $request->validate([
            'name' => 'required|min:5|max:255',
        ]);

        $token_name = $request->input('name');
        $token = \Random::getRandomApiToken();

        ApiToken::create([
            'user_id' => Auth::id(),
            'name' => $token_name,
            'token' => Hash::make($token)
        ]);

        $request->session()->flash('token', $token);

        return redirect()->route('developer.apitokens', \Instantiation::instance());

    }

    public function edit_api_token($instance, $id)
    {

        $api_token = ApiToken::findOrFail($id);

        return view('developer.createandeditapitoken', [
            'action' => 'developer.editapitoken_p',
            'item' => $api_token
        ]);
    }

    public function edit_api_token_p(Request $request)
    {
        $request->validate([
            'name' => 'required|min:5|max:255',
        ]);

        $api_token = ApiToken::findOrFail($request->input('_id'));

        $api_token->name = $request->input('name');
        $api_token->save();

        return redirect()->route('developer.apitokens', \Instantiation::instance())->with('success', 'El token se ha editado correctamente');


    }

    public function delete_api_token_p(Request $request)
    {
        $id = $request->input("_id");

        $api_token = ApiToken::find($id)->first();

        if($api_token != null) {
            $api_token->delete();
        }

        return redirect()->route('developer.apitokens', \Instantiation::instance())->with('success', 'El token fue borrado con éxito');

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
