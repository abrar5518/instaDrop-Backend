<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\EmailNotificationService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(public int $invoiceId) {}

    public function handle(EmailNotificationService $email, WhatsAppService $whatsApp): void
    {
        $invoice = Invoice::with('order.quoteRequest')->find($this->invoiceId);
        if (!$invoice || $invoice->status !== 'paid') {
            return;
        }

        $email->sendPaymentConfirmationEmail($invoice);
        $whatsApp->sendPaymentConfirmation($invoice);
        $email->sendAdminPaymentReceivedEmail($invoice);
        $whatsApp->sendAdminPaymentReceivedAlert($invoice);
    }
}
