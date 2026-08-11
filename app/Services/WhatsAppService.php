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

    public function __construct()
    {
        $setting = SystemSetting::first();
        $this->adminNumber = $setting ? $setting->admin_whatsapp_number : env('ADMIN_WHATSAPP_NUMBER', '+448001234455');
        $this->apiToken = $setting ? $setting->whatsapp_api_token : env('WHATSAPP_API_TOKEN', null);
    }

    /**
     * 1. Send Automatic Acknowledgment to Customer upon Quote Request
     * Template: "We have received your delivery request and will contact you shortly."
     */
    public function sendQuoteAcknowledgment(QuoteRequest $quote): bool
    {
        $message = "Hello {$quote->contact_name},\n\n"
                 . "Thank you for choosing InstaDrop Same-Day Courier! 🚚\n\n"
                 . "We have received your delivery request (#{$quote->quote_number}) from {$quote->pickup_postcode} to {$quote->delivery_postcode}.\n\n"
                 . "Our dispatch team is reviewing carrier rates and will contact you shortly via {$quote->preferred_contact_method}.\n\n"
                 . "InstaDrop 24/7 Hotline: {$this->adminNumber}";

        // Send to Customer
        $this->dispatchMessage($quote->contact_phone, $message);

        // Also Notify Admin Business WhatsApp
        $adminMessage = "🔔 NEW QUOTE REQUEST (#{$quote->quote_number})\n"
                      . "Customer: {$quote->contact_name} ({$quote->contact_phone})\n"
                      . "Route: {$quote->pickup_postcode} -> {$quote->delivery_postcode}\n"
                      . "Vehicle: {$quote->vehicle_type}\n"
                      . "Preferred Contact: {$quote->preferred_contact_method}";
        
        $this->dispatchMessage($this->adminNumber, $adminMessage);

        return true;
    }

    /**
     * 2. Send Quotation & Secure Payment Link to Customer
     */
    public function sendQuotationAndPaymentLink(Invoice $invoice): bool
    {
        $order = $invoice->order;
        $paymentUrl = config('app.url') . "/pay/" . $invoice->payment_token;

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

    /**
     * Dispatch WhatsApp Message via API (With Fallback Log Mode when API token is empty)
     */
    protected function dispatchMessage(string $recipientPhone, string $message): bool
    {
        Log::info("WhatsApp Dispatch to [{$recipientPhone}]: " . $message);

        // If third-party WhatsApp API token is provided, execute HTTP POST
        if ($this->apiToken) {
            try {
                // Generic WhatsApp Gateway HTTP POST call (Twilio / UltraMsg / Meta)
                Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ])->post('https://api.whatsapp-gateway.com/send', [
                    'to' => $recipientPhone,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                Log::error("WhatsApp Gateway API Error: " . $e->getMessage());
            }
        }

        return true;
    }
}
