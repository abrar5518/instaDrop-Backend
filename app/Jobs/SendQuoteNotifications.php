<?php

namespace App\Jobs;

use App\Models\QuoteRequest;
use App\Services\EmailNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendQuoteNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;

    public function __construct(public int $quoteId) {}

    public function handle(EmailNotificationService $email, WhatsAppService $whatsApp): void
    {
        $quote = QuoteRequest::find($this->quoteId);
        if (!$quote) return;
        $email->sendQuoteReceiptEmail($quote);
        $whatsApp->sendQuoteAcknowledgment($quote);
        $adminEmailSent = $email->sendAdminQuoteReceivedEmail($quote);
        $adminWhatsAppSent = $whatsApp->sendAdminQuoteReceivedAlert($quote);

        if (!$adminEmailSent || (config('services.whatsapp.enabled') && !$adminWhatsAppSent)) {
            throw new \RuntimeException("Admin quotation notifications failed for {$quote->quote_number}.");
        }
    }
}
