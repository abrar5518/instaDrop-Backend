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
        Log::info("Email Dispatch [Quote Receipt] to {$quote->contact_email} for Quote #{$quote->quote_number}");
        // Mail::to($quote->contact_email)->send(new QuoteReceiptMailable($quote));
        return true;
    }

    /**
     * Send Invoice & Secure Payment Link Email to Customer
     */
    public function sendInvoicePaymentEmail(Invoice $invoice): bool
    {
        $order = $invoice->order;
        Log::info("Email Dispatch [Invoice Payment Link] to {$order->customer_email} for Order #{$order->tracking_number}");
        // Mail::to($order->customer_email)->send(new InvoicePaymentMailable($invoice));
        return true;
    }

    /**
     * Send Payment Receipt Confirmation Email to Customer
     */
    public function sendPaymentConfirmationEmail(Invoice $invoice): bool
    {
        $order = $invoice->order;
        Log::info("Email Dispatch [Payment Receipt] to {$order->customer_email} for Invoice #{$invoice->invoice_number}");
        // Mail::to($order->customer_email)->send(new PaymentReceiptMailable($invoice));
        return true;
    }

    /**
     * Send POD Attachment & Confirmation Email to Customer
     */
    public function sendPodEmail(Order $order, Pod $pod): bool
    {
        Log::info("Email Dispatch [POD Attachment] to {$order->customer_email} for Order #{$order->tracking_number}");
        // Mail::to($order->customer_email)->send(new PodMailable($order, $pod));
        return true;
    }
}
