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

class SendInvoiceNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;

    public function __construct(public int $invoiceId) {}

    public function handle(EmailNotificationService $email, WhatsAppService $whatsApp): void
    {
        $invoice = Invoice::with('order')->find($this->invoiceId);
        if (!$invoice) return;
        $email->sendInvoicePaymentEmail($invoice);
        $whatsApp->sendQuotationAndPaymentLink($invoice);
    }
}
