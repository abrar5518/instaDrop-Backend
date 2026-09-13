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
use Illuminate\Support\Facades\Log;

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

        $results = [
            'customer_email' => $email->sendPaymentConfirmationEmail($invoice),
            'customer_whatsapp' => $whatsApp->sendPaymentConfirmation($invoice),
            'admin_email' => $email->sendAdminPaymentReceivedEmail($invoice),
            'admin_whatsapp' => $whatsApp->sendAdminPaymentReceivedAlert($invoice),
        ];

        Log::info('Payment notification delivery completed', [
            'invoice' => $invoice->invoice_number,
            'results' => $results,
        ]);
    }
}
