<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\ReasonRejection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CertificateCoordinatorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkroles:COORDINATOR|SECRETARY');
    }

    /****************************************************************************
     * MANAGE EVIDENCES
     ****************************************************************************/

    public function all()
    {
        $instance = \Instantiation::instance();

        $certificates = Certificate::all();

        return view('certificate.create',
            ['instance' => $instance,
            'certificates' => $certificates,
            'route_publish' => route('coordinator.certificate.publish',$instance)]);
    }

    public function create_template_init()
    {
        $instance = \Instantiation::instance();

        return view('certificate.create_template',
            ['route' => route('coordinator.certificate.create_template',$instance),
            'instance' => $instance]);
    }

    public function create_template(Request $request)
    {
        $instance = \Instantiation::instance();
        
        $request->validate([
            'title' => 'max:30|min:6',
            'html' => 'regex:/^(<!DOCTYPE html>\s*<html>\s*<head>[\s\S]*<\/body>\s*<\/html>)$/'
        ]);

        #Se le quitan los tabuladores para evitar problemas al generar el html
        $html = $request->input('html');
        $new_html = str_replace("   ","",$html);

        $certificate = Certificate::create([
            'title' => $request->input('title'),
            'html' => $new_html
        ]);

        $certificate->save();
        return redirect()->route('coordinator.certificate.create_template_init',$instance)->with('success', 'Plantilla de diploma creada con éxito.');
    }

    public function publish(Request $request)
    {
        print('holahola');
        $instance = \Instantiation::instance();
        $response = Http::get('http://02e3-185-171-167-102.ngrok.io/diploma', [
            'nombreDiploma' => $request->input('nombreDiploma'),
            'name' => $request->input('name'),
            'mailto' => $request->input('mailto'),
            'course' => $request->input('course'),
            'diplomaGenerar' => $request->input('diplomaGenerar'),
            'score' => $request->input('score'),
            'date' => $request->input('date'),
        ]);
        print($response->status());
        return redirect()->route('coordinator.certificate.generate',$instance)->with('success', 'Diploma generado con éxito.');
    }
}
