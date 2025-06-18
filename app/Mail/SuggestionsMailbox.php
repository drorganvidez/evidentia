<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuggestionsMailbox extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $subject_form;

    public $comment_form;

    public function __construct($subject_form, $comment_form)
    {
        $this->subject_form = $subject_form;
        $this->comment_form = $comment_form;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.suggestionsmailbox')->subject('Buzón de sugerencias');
    }
}
