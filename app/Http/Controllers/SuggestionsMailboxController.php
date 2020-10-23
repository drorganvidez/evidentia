<?php

namespace App\Http\Controllers;

use App\Mail\SuggestionsMailbox;
use App\Rules\MaxCharacters;
use App\Rules\MinCharacters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SuggestionsMailboxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function suggestionsmailbox()
    {

        $instance = \Instantiation::instance();
        $route = route('suggestionsmailbox_p',$instance);

        return view('suggestionsmailbox',['instance' => $instance, 'route' => $route]);
    }

    public function suggestionsmailbox_p(Request $request)
    {

        $request->validate([
            'subject' => 'required',
            'comment' => ['required',new MinCharacters(10),new MaxCharacters(20000)],
        ]);

        $subject_form = $request->input('subject');
        $comment = $request->input('comment');

        Mail::to('evidentia.cloud@gmail.com')->send(new SuggestionsMailbox($subject_form,$comment));

        return redirect()->route('suggestionsmailbox',\Instantiation::instance())->with('success',"Mensaje enviado con éxito. ¡Muchas gracias!");
    }
}
