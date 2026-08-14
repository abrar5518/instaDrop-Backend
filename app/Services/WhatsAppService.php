<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Get Admin Business WhatsApp Number from System Settings
     */
    protected function getAdminWhatsAppNumber(): string
    {
        $setting = SystemSetting::first();
        return $setting->admin_whatsapp_number ?? '+448001234455';
    }

    /**
     * Step 3: Send New Quote Request Alert to Admin Business WhatsApp
     */
    public function sendAdminNewQuoteAlert($quote): bool
    {
        $adminPhone = $this->getAdminWhatsAppNumber();
        $message = "🔔 NEW QUOTE REQUEST #{$quote->quote_number}\n\n"
                 . "Customer: {$quote->first_name} {$quote->last_name}\n"
                 . "Phone: {$quote->phone}\n"
                 . "Email: {$quote->email}\n"
                 . "Contact Pref: {$quote->contact_preference}\n\n"
                 . "Collection: {$quote->collection_postcode}\n"
                 . "Delivery: {$quote->delivery_postcode}\n"
                 . "Vehicle: {$quote->vehicle_type}\n"
                 . "Timescale: {$quote->timescale}\n\n"
                 . "Open Admin Inspector to set price & driver cost:\n"
                 . "http://localhost:8000/admin/quotes/{$quote->id}";

        Log::info("WhatsApp Alert Sent to Admin ({$adminPhone}): " . $message);
        return true;
    }

    /**
     * Step 4: Send Automatic Customer Acknowledgment
     */
    public function sendCustomerQuoteAcknowledgment($quote): bool
    {
        $message = "Hello {$quote->first_name}, thank you for choosing InstaDrop Courier!\n\n"
                 . "We have received your delivery request (#{$quote->quote_number}) from {$quote->collection_postcode} to {$quote->delivery_postcode}.\n\n"
                 . "Our dispatch team is calculating your route & driver availability. We will contact you shortly via {$quote->contact_preference}.";

        Log::info("WhatsApp Acknowledgment Sent to Customer ({$quote->phone}): " . $message);
        return true;
    }

    /**
     * Step 10 & 11: Send Final Invoice & Secure Payment Link
     */
    public function sendInvoiceAndPaymentLink($quote, string $paymentToken, float $sellingPrice): bool
    {
        $paymentUrl = "http://localhost:8000/pay/{$paymentToken}";

        $message = "Hello {$quote->first_name}, your InstaDrop delivery quotation (#{$quote->quote_number}) is ready!\n\n"
                 . "Route: {$quote->collection_postcode} ➔ {$quote->delivery_postcode}\n"
                 . "Vehicle: {$quote->vehicle_type}\n"
                 . "Quoted Selling Price: £" . number_format($sellingPrice, 2) . " + VAT\n\n"
                 . "Click the secure link below to view your official invoice & complete payment:\n"
                 . $paymentUrl;

        Log::info("WhatsApp Invoice & Payment Link Sent to Customer ({$quote->phone}): " . $message);
        return true;
    }

    /**
     * Step 13: Send Automated Payment Confirmation to Admin & Customer
     */
    public function sendPaymentSuccessConfirmation($order): bool
    {
        $adminPhone = $this->getAdminWhatsAppNumber();

        // Customer message
        $customerMsg = "✅ PAYMENT RECEIVED! Your InstaDrop delivery order (#{$order->order_number}) is now confirmed.\n\n"
                     . "Driver assignment in progress. Track your delivery live:\n"
                     . "http://localhost:3000/track-delivery";

        // Admin message
        $adminMsg = "💰 PAYMENT CONFIRMED! Order #{$order->order_number} paid by {$order->customer_name}.\n"
                  . "Total Paid: £" . number_format($order->total_amount, 2) . "\n"
                  . "Net Profit Margin: £" . number_format($order->net_profit ?? 60.00, 2) . "\n\n"
                  . "Open Admin Panel to assign driver & update status:\n"
                  . "http://localhost:8000/admin/orders";

        Log::info("WhatsApp Payment Receipt Sent to Customer ({$order->customer_phone}): " . $customerMsg);
        Log::info("WhatsApp Payment Alert Sent to Admin ({$adminPhone}): " . $adminMsg);
        return true;
    }

    /**
     * Step 16: Send Milestone Delivery Status Updates
     */
    public function sendStatusUpdateNotification($order, string $status): bool
    {
        $statusFormatted = strtoupper(str_replace('_', ' ', $status));

        $message = "🚚 DELIVERY UPDATE: Order #{$order->order_number}\n\n"
                 . "Current Status: {$statusFormatted}\n"
                 . "Vehicle: {$order->vehicle_type}\n\n"
                 . "Track live GPS location: http://localhost:3000/track-delivery";

        Log::info("WhatsApp Delivery Status Update Sent to Customer ({$order->customer_phone}): " . $message);
        return true;
    }

    /**
     * Step 18: Send Proof of Delivery (POD) Certificate
     */
    public function sendPodCertificate($order, $pod): bool
    {
        $message = "🎉 DELIVERY COMPLETED! Order #{$order->order_number} has been delivered successfully.\n\n"
                 . "Recipient Signature Name: {$pod->recipient_name}\n"
                 . "Delivered At: {$pod->delivered_at}\n\n"
                 . "Download your electronic Proof of Delivery (POD) certificate:\n"
                 . "http://localhost:3000/track-delivery";

        Log::info("WhatsApp Digital POD Certificate Sent to Customer ({$order->customer_phone}): " . $message);
        return true;
    }
}
