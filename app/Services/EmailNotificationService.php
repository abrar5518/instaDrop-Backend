<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Pod;
use App\Models\Inquiry;
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

    public function sendAdminQuoteReceivedEmail(QuoteRequest $quote): bool
    {
        $admin = \App\Models\SystemSetting::first()?->admin_notification_email;
        if (!$admin) {
            Log::warning('Admin quote email skipped because admin email is missing.', ['quote' => $quote->quote_number]);
            return false;
        }

        $body = "NEW QUOTATION REQUEST\n\n"
            . "Quote reference: {$quote->quote_number}\n"
            . "Customer: {$quote->full_name}\n"
            . "Email: {$quote->email}\n"
            . "Phone: {$quote->phone}\n"
            . "Preferred contact: {$quote->contact_preference}\n"
            . "Route: {$quote->collection_postcode} to {$quote->delivery_postcode}\n"
            . "Vehicle: {$quote->vehicle_type}\n"
            . "Timescale: {$quote->timescale}\n"
            . "Enquiry type: {$quote->enquiry_type}\n"
            . "Additional information: " . ($quote->additional_info ?: 'None') . "\n\n"
            . "Review quotation: " . rtrim(config('app.url'), '/') . "/admin/quotes/{$quote->id}";

        return $this->send($admin, "New quotation {$quote->quote_number} - {$quote->full_name}", $body);
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

    /** Send a detailed verified-payment alert to the dispatch/admin mailbox. */
    public function sendAdminPaymentReceivedEmail(Invoice $invoice): bool
    {
        $invoice->loadMissing('order.quoteRequest');
        $order = $invoice->order;
        $quoteNumber = $order?->quoteRequest?->quote_number ?? 'N/A';
        $admin = \App\Models\SystemSetting::first()?->admin_notification_email;

        if (!$admin || !$order) {
            Log::warning('Admin payment email skipped because admin email or order is missing.', ['invoice' => $invoice->invoice_number]);
            return false;
        }

        $body = "PAYMENT RECEIVED\n\n"
            . "Customer: {$order->customer_name}\n"
            . "Customer email: {$order->customer_email}\n"
            . "Customer phone: {$order->customer_phone}\n"
            . "Quote reference: {$quoteNumber}\n"
            . "Order/tracking reference: {$order->tracking_number}\n"
            . "Invoice: {$invoice->invoice_number}\n"
            . "Amount paid: GBP {$invoice->total_amount}\n"
            . "Payment method: PayPal\n"
            . "PayPal transaction: {$invoice->payment_transaction_id}\n"
            . "Paid at: {$invoice->paid_at}\n"
            . "Route: {$order->pickup_address} to {$order->delivery_address}\n\n"
            . "Review the booking: " . rtrim(config('app.url'), '/') . "/admin/orders";

        return $this->send($admin, "Payment received: {$invoice->invoice_number} - {$order->customer_name}", $body);
    }

    /**
     * Send POD Attachment & Confirmation Email to Customer
     */
    public function sendPodEmail(Order $order, Pod $pod): bool
    {
        return $this->send($order->customer_email, "Proof of delivery - {$order->tracking_number}", "Your delivery was received by {$pod->recipient_name} at {$pod->delivered_at}.");
    }

    public function sendAdminInquiryEmail(Inquiry $inquiry): bool
    {
        $admin = \App\Models\SystemSetting::first()?->admin_notification_email;
        if (!$admin) {
            Log::warning('Admin inquiry email skipped because admin email is missing.', ['inquiry' => $inquiry->id]);
            return false;
        }

        $type = str_replace('_', ' ', $inquiry->inquiry_type);
        $reference = 'INQ-' . str_pad((string) $inquiry->id, 6, '0', STR_PAD_LEFT);
        $body = "NEW " . strtoupper($type) . " INQUIRY\n\n"
            . "Reference: {$reference}\n"
            . "Name: {$inquiry->name}\n"
            . "Email: {$inquiry->email}\n"
            . "Phone: {$inquiry->phone}\n"
            . "Company: " . ($inquiry->company_name ?: 'N/A') . "\n"
            . "Company registration: " . ($inquiry->company_registration ?: 'N/A') . "\n"
            . "Monthly deliveries: " . ($inquiry->monthly_deliveries ?: 'N/A') . "\n"
            . "Subject: " . ($inquiry->subject ?: 'N/A') . "\n"
            . "Message: " . ($inquiry->message ?: 'N/A') . "\n\n"
            . "Review inquiry: " . rtrim(config('app.url'), '/') . "/admin/inquiries";

        return $this->send($admin, "New InstaDrop {$type}: {$reference} - {$inquiry->name}", $body);
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
