<?php

namespace App\Services;

use App\Mail\ApprovalMail;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailService
{
    public function sendOtpEmail(string $toEmail, string $toName, string $otp): bool
    {
        try {
            Mail::to($toEmail, $toName)->send(new OtpMail($toName, $otp));
            return true;
        } catch (Throwable $e) {
            Log::error('Mailer error (sendOtpEmail): ' . $e->getMessage());
            return false;
        }
    }

    public function sendApprovalEmail(string $toEmail, string $toName, string $result): bool
    {
        try {
            Mail::to($toEmail, $toName)->send(new ApprovalMail($toName, $result));
            return true;
        } catch (Throwable $e) {
            Log::error('Mailer error (sendApprovalEmail): ' . $e->getMessage());
            return false;
        }
    }
}
