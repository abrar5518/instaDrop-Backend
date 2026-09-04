<?php

namespace App\Services;

use App\Models\QuoteRequest;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Pod;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected string $adminNumber;
    protected ?string $apiToken;
    protected ?string $phoneNumberId;
    protected string $graphVersion;

    public function __construct()
    {
        $setting = SystemSetting::first();
        $this->adminNumber = $setting?->admin_whatsapp_number ?: (string) config('services.whatsapp.admin_number');
        $this->apiToken = $setting?->whatsapp_api_token ?: config('services.whatsapp.access_token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->graphVersion = config('services.whatsapp.graph_version', 'v25.0');
    }

    /**
     * 1. Send Automatic Acknowledgment to Customer upon Quote Request
     * Template: "We have received your delivery request and will contact you shortly."
     */
    public function sendQuoteAcknowledgment(QuoteRequest $quote): bool
    {
        $message = "Hello {$quote->full_name},\n\n"
                 . "Thank you for choosing InstaDrop Same-Day Courier! 🚚\n\n"
                 . "We have received your delivery request (#{$quote->quote_number}) from {$quote->collection_postcode} to {$quote->delivery_postcode}.\n\n"
                 . "Our dispatch team is reviewing carrier rates and will contact you shortly via {$quote->contact_preference}.\n\n"
                 . "InstaDrop 24/7 Hotline: {$this->adminNumber}";

        // Send to Customer
        $this->dispatchMessage($quote->phone, $message);

        // Also Notify Admin Business WhatsApp
        $adminMessage = "🔔 NEW QUOTE REQUEST (#{$quote->quote_number})\n"
                      . "Customer: {$quote->full_name} ({$quote->phone})\n"
                      . "Route: {$quote->collection_postcode} -> {$quote->delivery_postcode}\n"
                      . "Vehicle: {$quote->vehicle_type}\n"
                      . "Preferred Contact: {$quote->contact_preference}";
        
        $this->dispatchMessage($this->adminNumber, $adminMessage);

        return true;
    }

    /**
     * 2. Send Quotation & Secure Payment Link to Customer
     */
    public function sendQuotationAndPaymentLink(Invoice $invoice): bool
    {
        $order = $invoice->order;
        $paymentUrl = rtrim(config('app.frontend_url'), '/') . "/pay/" . $invoice->payment_token;

        $message = "Hello {$order->customer_name},\n\n"
                 . "Your delivery quotation for Order #{$order->tracking_number} is ready! 📦\n\n"
                 . "Total Selling Price: £" . number_format($invoice->total_amount, 2) . " (Inc. VAT)\n\n"
                 . "Please click the link below to view your invoice and complete payment securely online:\n"
                 . "👉 {$paymentUrl}\n\n"
                 . "Once paid, your driver will be dispatched immediately!";

        return $this->dispatchMessage($order->customer_phone, $message);
    }

    /**
     * 3. Send Automated Delivery Status Update to Customer
     */
    public function sendStatusUpdateNotification(Order $order): bool
    {
        $statusLabels = [
            'dispatched' => '🚀 Driver Dispatched to Pickup Location',
            'collected'  => '📦 Parcel Collected & Sealed in Dedicated Vehicle',
            'in_transit' => '🛣️ En Route / In Transit to Destination',
            'delivered'  => '✅ Delivered Successfully to Recipient',
        ];

        $statusText = $statusLabels[$order->status] ?? $order->status;
        $trackingUrl = config('app.url') . "/track/" . $order->tracking_number;

        $message = "Delivery Status Update (#{$order->tracking_number})\n\n"
                 . "Status: {$statusText}\n\n"
                 . "Track Live Satellite GPS: {$trackingUrl}\n"
                 . "Vehicle: {$order->vehicle_type}";

        return $this->dispatchMessage($order->customer_phone, $message);
    }

    /**
     * 4. Send Proof of Delivery (POD) Notification & Download Link to Customer
     */
    public function sendPodNotification(Order $order, Pod $pod): bool
    {
        $message = "Proof of Delivery Confirmation (#{$order->tracking_number}) 🎉\n\n"
                 . "Your parcel was delivered successfully to {$pod->recipient_name} at {$pod->delivered_at}.\n\n"
                 . "Digital Proof of Delivery (POD) signature is available on your tracking page.";

        return $this->dispatchMessage($order->customer_phone, $message);
    }

    public function sendPaymentConfirmation(Invoice $invoice): bool
    {
        $order = $invoice->order;
        return $this->dispatchMessage($order->customer_phone, "Payment received for invoice {$invoice->invoice_number}. Your booking {$order->tracking_number} is confirmed.");
    }

    public function sendAdminPaymentReceivedAlert(Invoice $invoice): bool
    {
        $invoice->loadMissing('order.quoteRequest');
        $order = $invoice->order;
        if (!$order) {
            Log::warning('Admin payment WhatsApp skipped because the order is missing.', ['invoice' => $invoice->invoice_number]);
            return false;
        }

        $quoteNumber = $order->quoteRequest?->quote_number ?? 'N/A';
        $message = "✅ PAYMENT RECEIVED\n\n"
            . "Customer: {$order->customer_name}\n"
            . "Quote: {$quoteNumber}\n"
            . "Order: {$order->tracking_number}\n"
            . "Invoice: {$invoice->invoice_number}\n"
            . "Amount: £" . number_format((float) $invoice->total_amount, 2) . " GBP\n"
            . "PayPal transaction: {$invoice->payment_transaction_id}\n"
            . "Route: {$order->pickup_address} → {$order->delivery_address}\n\n"
            . "The verified payment is recorded in the InstaDrop admin panel.";

        $template = config('services.whatsapp.payment_template');
        if ($template && $this->dispatchTemplate($this->adminNumber, $template, [
            $invoice->invoice_number,
            $order->customer_name,
            $quoteNumber,
            $order->tracking_number,
            number_format((float) $invoice->total_amount, 2, '.', ''),
            (string) $invoice->payment_transaction_id,
        ])) {
            return true;
        }

        // Keep the in-session fallback while a newly submitted Meta template is pending approval.
        return $this->dispatchMessage($this->adminNumber, $message);
    }

    public function sendAdminInquiryAlert(string $name, string $type): bool
    {
        return $this->dispatchMessage($this->adminNumber, "New {$type} submission from {$name}. Please review the InstaDrop admin panel.");
    }

    /**
     * Dispatch WhatsApp Message via API (With Fallback Log Mode when API token is empty)
     */
    protected function dispatchMessage(string $recipientPhone, string $message): bool
    {
        if (!config('services.whatsapp.enabled') || !$this->apiToken || !$this->phoneNumberId) {
            Log::warning('WhatsApp message skipped because Meta credentials are incomplete.');
            return false;
        }
        try {
            $response = Http::withToken($this->apiToken)->acceptJson()
                ->post("https://graph.facebook.com/{$this->graphVersion}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => preg_replace('/\D+/', '', $recipientPhone),
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $message],
                ]);
            if ($response->failed()) {
                Log::error('Meta WhatsApp API error', ['status' => $response->status(), 'response' => $response->json()]);
                return false;
            }
            Log::info('WhatsApp message accepted by Meta', ['to' => $recipientPhone, 'message_id' => $response->json('messages.0.id')]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Meta WhatsApp API exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    protected function dispatchTemplate(string $recipientPhone, string $template, array $parameters): bool
    {
        if (!config('services.whatsapp.enabled') || !$this->apiToken || !$this->phoneNumberId) {
            return false;
        }

        try {
            $response = Http::withToken($this->apiToken)->acceptJson()
                ->post("https://graph.facebook.com/{$this->graphVersion}/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => preg_replace('/\D+/', '', $recipientPhone),
                    'type' => 'template',
                    'template' => [
                        'name' => $template,
                        'language' => ['code' => config('services.whatsapp.template_language', 'en')],
                        'components' => [[
                            'type' => 'body',
                            'parameters' => array_map(
                                fn ($value) => ['type' => 'text', 'text' => (string) $value],
                                $parameters
                            ),
                        ]],
                    ],
                ]);

            if ($response->failed()) {
                Log::warning('Meta WhatsApp template unavailable; using text fallback.', [
                    'template' => $template,
                    'status' => $response->status(),
                ]);
                return false;
            }

            Log::info('WhatsApp template accepted by Meta', [
                'to' => $recipientPhone,
                'template' => $template,
                'message_id' => $response->json('messages.0.id'),
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Meta WhatsApp template exception; using text fallback.', [
                'template' => $template,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
