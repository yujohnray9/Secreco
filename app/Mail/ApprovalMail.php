<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $toName;
    public string $result; // 'approved' or 'rejected'

    public function __construct(string $toName, string $result)
    {
        $this->toName = $toName;
        $this->result = $result;
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->result) {
            'approved', 'activate' => 'Your SecReCo Account Has Been Activated',
            'deactivated'         => 'Your SecReCo Account Has Been Deactivated',
            default               => 'Your SecReCo Account Was Not Approved',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $isActivated   = in_array($this->result, ['approved', 'activate'], true);
        $isDeactivated = $this->result === 'deactivated';

        $statusLabel = $isActivated ? 'ACTIVATED' : ($isDeactivated ? 'DEACTIVATED' : 'NOT APPROVED');
        $statusColor = $isActivated ? '#166534' : '#991b1b';
        $statusBg    = $isActivated ? '#dcfce7' : '#fee2e2';

        if ($isActivated) {
            $bodyMessage = "Your account has been reviewed and <strong>activated</strong> by the PTA. You may now sign in to SecReCo.";
        } elseif ($isDeactivated) {
            $bodyMessage = "Your Account is Deactivated. Please contact the Project technical assistant ii for assistance.";
        } else {
            $bodyMessage = "After review, your account registration was <strong>not approved</strong>. Please contact the PTA office for more information.";
        }

        $ctaBlock = $isActivated
            ? "<div style='text-align:center;margin-top:24px;'>
                 <a href='" . config('app.url') . "/login'
                    style='display:inline-block;padding:11px 28px;background:#2d6a30;color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;'>
                   Sign In to SecReCo
                 </a>
               </div>"
            : '';

        $nameEscaped = e($this->toName);

        return new Content(
            htmlString: "
                <div style='font-family:sans-serif;max-width:520px;margin:auto;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;'>
                    <div style='background:linear-gradient(135deg,#2d6a30,#3d7a3f);padding:28px 36px;text-align:center;'>
                        <div style='font-size:22px;font-weight:800;color:#fff;letter-spacing:0.04em;'>SecReCo</div>
                        <div style='font-size:11px;color:rgba(255,255,255,0.75);margin-top:4px;letter-spacing:0.08em;text-transform:uppercase;'>CVAARRD Secure Reporting &amp; Consolidation</div>
                    </div>
                    <div style='text-align:center;padding:24px 36px 0;'>
                        <span style='display:inline-block;background:{$statusBg};color:{$statusColor};font-size:11px;font-weight:800;letter-spacing:0.1em;padding:6px 18px;border-radius:20px;'>{$statusLabel}</span>
                    </div>
                    <div style='padding:20px 36px 32px;'>
                        <p style='font-size:15px;color:#1a2e1a;margin:0 0 12px;'>Hi <strong>{$nameEscaped}</strong>,</p>
                        <p style='font-size:14px;color:#374151;line-height:1.7;margin:0;'>{$bodyMessage}</p>
                        {$ctaBlock}
                    </div>
                    <div style='background:#f9fafb;padding:16px 36px;border-top:1px solid #e5e7eb;text-align:center;'>
                        <p style='font-size:11px;color:#9ca3af;margin:0;'>&copy; " . date('Y') . " CVAARRD Consortium Office &middot; SecReCo System</p>
                    </div>
                </div>
            ",
        );
    }
}
