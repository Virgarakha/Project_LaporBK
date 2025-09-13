<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $otp;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $otp
     */
    public function __construct($user, $otp)
    {
        $this->user = $user; // data user
        $this->otp = $otp;   // kode OTP
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Kode Verifikasi Akun') // judul email
                    ->view('emails.otp');           // view blade untuk isi email
    }
}
