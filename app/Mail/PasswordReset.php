<?php

namespace App\Mail;

use App\Models\Token;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** The link to choose a new password, in the theme of the app and with the address of the route. */
class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Token $token, public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Restablecimiento de contraseña');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.password-reset');
    }
}
