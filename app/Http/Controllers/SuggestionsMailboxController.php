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

        $route = route('suggestionsmailbox_p');

        return view('suggestionsmailbox', ['route' => $route]);
    }

    public function suggestionsmailbox_p(Request $request)
    {

        $request->validate([
            'subject' => 'required',
            'comment' => ['required', new MinCharacters(10), new MaxCharacters(20000)],
        ]);

        $subject_form = $request->input('subject');
        $comment = $request->input('comment');

        Mail::to('drorganvidez@us.es')->send(new SuggestionsMailbox($subject_form, $comment));

        return redirect()->route('suggestionsmailbox')->with('success', 'Mensaje enviado con éxito. ¡Muchas gracias!');
    }
}
