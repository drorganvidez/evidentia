<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\ReasonRejection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'certificates' => $certificates]);
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
}
