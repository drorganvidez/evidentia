<?php

namespace App\Http\Controllers;

use App\Models\EmpresaColaborativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmpresasColaborativasController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {

        return view('empresasColaborativas.createandedit', ['route' => route('admin.empresasColaborativas.new')]);
    }

    public function new(Request $request)
    {

        $validatedData = $request->validate([
            'name' => 'required|unique:empresacolaborativa',
            'telephone' => 'required',
            'email' => 'required',
        ]);

        $empresaColaborativa = new EmpresaColaborativa();

        $empresaColaborativa->name = $request->name;
        $empresaColaborativa->telephone = $request->telephone;
        $empresaColaborativa->email = $request->email;

        $empresaColaborativa->save();

        $message = 'Empresa colaborativa guarda con éxito';
        return redirect()->route('admin.empresasColaborativas.manage')->with('success', $message)->setStatusCode(200);
    }

    public function edit($id)
    {
        $empresaColaborativa = EmpresaColaborativa::find($id);
        return view('empresasColaborativas.createandedit', ['empresacolaborativa' => $empresaColaborativa, 'edit' => true, 'route' => route('admin.empresasColaborativas.manage.save')]);
    }

    public function save(Request $request)
    {

        $validatedData = $request->validate([
            'name' => ['required', Rule::unique('empresacolaborativa')->ignore($request->_id)],
            'telephone' => 'required',
            'email' => 'required',
        ]);

        $empresaColaborativa = EmpresaColaborativa::where('id', $request->_id)->first();
        $empresaColaborativa->name = $request->name;
        $empresaColaborativa->telephone = $request->telephone;
        $empresaColaborativa->email = $request->email;
        
        $empresaColaborativa->save();

        return redirect()->route('admin.empresasColaborativas.manage')->with('success', 'Empresa Colaborativa actualizada con éxito.');
    }

    public function manage()
    {

        $empresaColaborativa = EmpresaColaborativa::all();

        return view('empresasColaborativas.manage', ['empresacolaborativa' => $empresaColaborativa]);
    }

    public function delete($id)
    {
        $empresaColaborativa = EmpresaColaborativa::where('id', $id)->first();
        return view('empresasColaborativas.delete', ['empresacolaborativa' => $empresaColaborativa]);
    }

    public function remove(Request $request)
    {
        $empresaColaborativa = EmpresaColaborativa::where('id', $request->id)->first();

        $request->validate([
            'name' => 'in:' . $empresaColaborativa->name
        ]);


        $empresaColaborativa->delete();

        return redirect()->route('admin.empresasColaborativas.manage')->with('success', 'Empresa Colaborativa eliminada con éxito.');
    }

}
