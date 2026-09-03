<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Pod;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    /**
     * Send Quote Receipt Confirmation Email to Customer
     */
    public function sendQuoteReceiptEmail(QuoteRequest $quote): bool
    {
        return $this->send($quote->email, 'We received your InstaDrop quote request', "Hello {$quote->full_name},\n\nWe received quote request {$quote->quote_number}. Our dispatch team will contact you shortly.");
    }

    /**
     * Send Invoice & Secure Payment Link Email to Customer
     */
    public function sendInvoicePaymentEmail(Invoice $invoice): bool
    {
        $order = $invoice->order;
        $url = rtrim(config('app.frontend_url'), '/') . '/pay/' . $invoice->payment_token;
        return $this->send($order->customer_email, "Invoice {$invoice->invoice_number} ready", "Hello {$order->customer_name},\n\nYour InstaDrop delivery invoice is ready. Total: GBP {$invoice->total_amount}.\n\nPay securely: {$url}");
    }

    /**
     * Send Payment Receipt Confirmation Email to Customer
     */
    public function sendPaymentConfirmationEmail(Invoice $invoice): bool
    {
        $order = $invoice->order;
        return $this->send($order->customer_email, "Payment received - {$invoice->invoice_number}", "Hello {$order->customer_name},\n\nWe received your payment for invoice {$invoice->invoice_number}. Tracking reference: {$order->tracking_number}.");
    }

    /**
     * Send POD Attachment & Confirmation Email to Customer
     */
    public function sendPodEmail(Order $order, Pod $pod): bool
    {
        return $this->send($order->customer_email, "Proof of delivery - {$order->tracking_number}", "Your delivery was received by {$pod->recipient_name} at {$pod->delivered_at}.");
    }

    public function sendAdminInquiryEmail(string $name, string $email, string $type): bool
    {
        $admin = \App\Models\SystemSetting::first()?->admin_notification_email;
        return $admin ? $this->send($admin, "New InstaDrop {$type} submission", "New {$type} submission from {$name} ({$email}). Review it in the admin panel.") : false;
    }

    private function send(string $to, string $subject, string $body): bool
    {
        if (config('mail.default') === 'smtp' && blank(config('mail.mailers.smtp.password'))) {
            Log::error('Email send failed: SMTP password is not configured', [
                'to' => $to,
                'subject' => $subject,
            ]);

            return false;
        }

        try {
            Mail::raw($body, fn ($message) => $message->to($to)->subject($subject));
            Log::info('Email sent', ['to' => $to, 'subject' => $subject]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Email send failed', ['to' => $to, 'message' => $e->getMessage()]);
            return false;
        }
    }
}
