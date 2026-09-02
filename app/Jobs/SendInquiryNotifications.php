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
        $type = str_replace('_', ' ', $inquiry->inquiry_type);
        $email->sendAdminInquiryEmail($inquiry->name, $inquiry->email, $type);
        $whatsApp->sendAdminInquiryAlert($inquiry->name, $type);
    }
}
