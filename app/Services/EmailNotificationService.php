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
        return $this->send($quote->email, 'We received your InstaDrop quote request', "Hello {$quote->full_name},\n\nWe received quote request {$quote->quote_number}. Our dispatch team will contact you shortly.", [
            'eyebrow' => 'Quote request received', 'title' => 'Your delivery starts here.',
            'intro' => "Hello {$quote->full_name}, thank you for choosing InstaDrop. Our dispatch team will review your request and contact you shortly.",
            'details' => ['Quote reference' => $quote->quote_number, 'Collection' => $quote->collection_postcode, 'Delivery' => $quote->delivery_postcode, 'Vehicle' => ucwords(str_replace('_', ' ', $quote->vehicle_type)), 'Timescale' => $quote->timescale, 'Requested collection (UK time)' => $quote->collection_schedule],
            'note' => 'This acknowledges your request. Your booking is not yet confirmed.',
        ]);
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
            . "Requested collection: {$quote->collection_schedule}\n"
            . "Additional information: " . ($quote->additional_info ?: 'None') . "\n\n"
            . "Review quotation: " . rtrim(config('app.url'), '/') . "/admin/quotes/{$quote->id}";

        return $this->send($admin, "New quotation {$quote->quote_number} - {$quote->full_name}", $body, [
            'eyebrow' => 'Admin · New quotation', 'title' => 'A new delivery request.',
            'intro' => 'Review the request below and contact the customer with a quotation.',
            'details' => ['Quote reference' => $quote->quote_number, 'Customer' => $quote->full_name, 'Email' => $quote->email, 'Phone' => $quote->phone, 'Preferred contact' => ucwords(str_replace('_', ' ', $quote->contact_preference)), 'Collection' => $quote->collection_postcode, 'Delivery' => $quote->delivery_postcode, 'Vehicle' => ucwords(str_replace('_', ' ', $quote->vehicle_type)), 'Timescale' => $quote->timescale, 'Requested collection (UK time)' => $quote->collection_schedule],
            'note' => $quote->additional_info,
            'actionLabel' => 'Review quotation', 'actionUrl' => rtrim(config('app.url'), '/') . "/admin/quotes/{$quote->id}",
        ]);
    }

    /**
     * Send Invoice & Secure Payment Link Email to Customer
     */
    public function sendInvoicePaymentEmail(Invoice $invoice): bool
    {
        $order = $invoice->order;
        $url = rtrim(config('app.frontend_url'), '/') . '/pay/' . $invoice->payment_token;
        return $this->send($order->customer_email, "Invoice {$invoice->invoice_number} ready", "Hello {$order->customer_name},\n\nYour InstaDrop delivery invoice is ready. Total: GBP {$invoice->total_amount}.\n\nPay securely: {$url}", [
            'eyebrow' => 'Your invoice is ready', 'title' => 'One step closer to collection.',
            'intro' => "Hello {$order->customer_name}, your delivery invoice is ready. Review the details and use the secure payment link below.",
            'amount' => 'GBP ' . number_format((float) $invoice->total_amount, 2), 'amountLabel' => 'Amount due',
            'details' => ['Invoice' => $invoice->invoice_number, 'Tracking reference' => $order->tracking_number, 'Collection' => $order->pickup_address, 'Delivery' => $order->delivery_address, 'Subtotal' => 'GBP ' . number_format((float) $invoice->subtotal, 2), 'VAT' => 'GBP ' . number_format((float) $invoice->vat_amount, 2)],
            'actionLabel' => 'View invoice & pay securely', 'actionUrl' => $url,
            'note' => 'Keep this payment link private. Contact our dispatch team if any booking details need changing.',
        ]);
    }

    /**
     * Send Payment Receipt Confirmation Email to Customer
     */
    public function sendPaymentConfirmationEmail(Invoice $invoice): bool
    {
        $order = $invoice->order;
        return $this->send($order->customer_email, "Payment received - {$invoice->invoice_number}", "Hello {$order->customer_name},\n\nWe received your payment for invoice {$invoice->invoice_number}. Tracking reference: {$order->tracking_number}.", [
            'eyebrow' => 'Payment confirmation', 'title' => 'Payment received. Thank you.',
            'intro' => "Hello {$order->customer_name}, we have received your payment. Please keep this email for your records.",
            'amount' => 'GBP ' . number_format((float) $invoice->total_amount, 2), 'amountLabel' => 'Amount paid',
            'details' => ['Invoice' => $invoice->invoice_number, 'Tracking reference' => $order->tracking_number, 'Collection' => $order->pickup_address, 'Delivery' => $order->delivery_address, 'Payment method' => $invoice->payment_method ?: 'PayPal', 'Transaction reference' => $invoice->payment_transaction_id, 'Paid at' => $invoice->paid_at],
            'actionLabel' => 'Track your delivery', 'actionUrl' => rtrim(config('app.frontend_url'), '/') . '/track-delivery',
            'note' => "Use tracking reference {$order->tracking_number} on the tracking page.",
        ]);
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

        return $this->send($admin, "Payment received: {$invoice->invoice_number} - {$order->customer_name}", $body, [
            'eyebrow' => 'Admin · Verified payment', 'title' => 'A booking has been paid.',
            'intro' => 'Payment has been recorded. Review the booking in the admin panel for the next operational step.',
            'amount' => 'GBP ' . number_format((float) $invoice->total_amount, 2), 'amountLabel' => 'Amount received',
            'details' => ['Customer' => $order->customer_name, 'Email' => $order->customer_email, 'Phone' => $order->customer_phone, 'Quote reference' => $quoteNumber, 'Tracking reference' => $order->tracking_number, 'Invoice' => $invoice->invoice_number, 'Payment method' => $invoice->payment_method ?: 'PayPal', 'Transaction reference' => $invoice->payment_transaction_id, 'Paid at' => $invoice->paid_at, 'Collection' => $order->pickup_address, 'Delivery' => $order->delivery_address],
            'actionLabel' => 'Review booking', 'actionUrl' => rtrim(config('app.url'), '/') . '/admin/orders',
        ]);
    }

    /**
     * Send POD Attachment & Confirmation Email to Customer
     */
    public function sendPodEmail(Order $order, Pod $pod): bool
    {
        return $this->send($order->customer_email, "Proof of delivery - {$order->tracking_number}", "Your delivery was received by {$pod->recipient_name} at {$pod->delivered_at}.", [
            'eyebrow' => 'Delivery completed', 'title' => 'Your delivery has arrived.',
            'intro' => 'Thank you for choosing InstaDrop. Your delivery details are below.',
            'details' => ['Tracking reference' => $order->tracking_number, 'Received by' => $pod->recipient_name, 'Delivered at' => $pod->delivered_at],
            'actionLabel' => 'View delivery status', 'actionUrl' => rtrim(config('app.frontend_url'), '/') . '/track-delivery',
        ]);
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

        return $this->send($admin, "New InstaDrop {$type}: {$reference} - {$inquiry->name}", $body, [
            'eyebrow' => 'Admin · New inquiry', 'title' => 'Someone wants to get in touch.',
            'intro' => 'A new ' . $type . ' inquiry has arrived. Review the details and respond to the customer.',
            'details' => ['Reference' => $reference, 'Name' => $inquiry->name, 'Email' => $inquiry->email, 'Phone' => $inquiry->phone, 'Company' => $inquiry->company_name, 'Company registration' => $inquiry->company_registration, 'Monthly deliveries' => $inquiry->monthly_deliveries, 'Subject' => $inquiry->subject],
            'note' => $inquiry->message,
            'actionLabel' => 'Review inquiry', 'actionUrl' => rtrim(config('app.url'), '/') . '/admin/inquiries',
        ]);
    }

    private function send(string $to, string $subject, string $body, array $presentation = []): bool
    {
        if (config('mail.default') === 'smtp' && blank(config('mail.mailers.smtp.password'))) {
            Log::error('Email send failed: SMTP password is not configured', [
                'to' => $to,
                'subject' => $subject,
            ]);

            return false;
        }

        try {
            $setting = \App\Models\SystemSetting::first();
            $data = array_merge([
                'title' => $subject,
                'eyebrow' => 'InstaDrop notification',
                'intro' => '',
                'details' => [],
                'note' => null,
                'amount' => null,
                'amountLabel' => 'Total',
                'actionLabel' => null,
                'actionUrl' => null,
            ], $presentation, [
                'plainBody' => $body,
                'businessName' => $setting?->business_name ?: 'InstaDrop Courier Services Ltd',
                'supportEmail' => $setting?->admin_notification_email,
                'supportPhone' => $setting?->public_phone,
                'websiteUrl' => rtrim(config('app.frontend_url'), '/'),
            ]);
            Mail::send(['html' => 'emails.notification', 'text' => 'emails.notification-text'], $data,
                fn ($message) => $message->to($to)->subject($subject));
            Log::info('Email sent', ['to' => $to, 'subject' => $subject]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Email send failed', ['to' => $to, 'message' => $e->getMessage()]);
            return false;
        }
    }
}
