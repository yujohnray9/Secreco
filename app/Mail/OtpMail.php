<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $toName;
    public string $otp;

    public function __construct(string $toName, string $otp)
    {
        $this->toName = $toName;
        $this->otp    = $otp;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your SecReCo Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family:sans-serif;max-width:480px;margin:auto;padding:32px;border:1px solid #e5e7eb;border-radius:8px;'>
                    <h2 style='color:#1e3a5f;'>SecReCo Email Verification</h2>
                    <p>Hi <strong>{$this->toName}</strong>,</p>
                    <p>Your verification code is valid for <strong>10 minutes</strong>:</p>
                    <div style='font-size:36px;font-weight:bold;letter-spacing:12px;text-align:center;background:#f0f4ff;padding:20px;border-radius:6px;margin:24px 0;color:#1e3a5f;'>
                        {$this->otp}
                    </div>
                    <p style='color:#6b7280;font-size:13px;'>
                        If you did not request this, ignore this email.
                    </p>
                </div>
            ",
        );
    }
}
