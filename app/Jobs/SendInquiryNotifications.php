<?php

namespace App\Jobs;

use App\Models\Inquiry;
use App\Services\EmailNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInquiryNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;

    public function __construct(public int $inquiryId) {}

    public function handle(EmailNotificationService $email, WhatsAppService $whatsApp): void
    {
        $inquiry = Inquiry::find($this->inquiryId);
        if (!$inquiry) return;
        $emailSent = $email->sendAdminInquiryEmail($inquiry);
        $whatsAppSent = $whatsApp->sendAdminInquiryAlert($inquiry);

        if (!$emailSent || (config('services.whatsapp.enabled') && !$whatsAppSent)) {
            throw new \RuntimeException("Admin inquiry notifications failed for inquiry {$inquiry->id}.");
        }
    }
}
