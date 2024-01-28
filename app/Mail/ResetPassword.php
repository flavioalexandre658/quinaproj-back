<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    protected $uuid;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    public function build()
    {
        return $this->subject('Solicitação de recuperação de senha!')
            ->view('emails.reset_password', ['uuid' => $this->uuid]);
    }

    public function attachments(): array
    {
        return [];
    }
}
