<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegisterUserMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $userDetails = array(
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'referral_code' => ''
    );

    protected $tokenDetails = array(
        'token_code' => '',
        'token_exipry' => ''
    );

    public function __construct($user, $token)
    {
        $this->userDetails = $user;
        $this->tokenDetails = $token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Register User Mail',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.authMails.signupMail',
            with: [
                'userDetails' => $this->userDetails,
                'tokenDetails' => $this->tokenDetails
            ]
        );
    }
}
