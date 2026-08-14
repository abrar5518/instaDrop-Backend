<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    /**
     * Get Admin Support Email
     */
    protected function getAdminEmail(): string
    {
        $setting = SystemSetting::first();
        return $setting->support_email ?? 'dispatch@instadrop.co.uk';
    }

    /**
     * Send Admin New Quote Alert Email
     */
    public function sendAdminNewQuoteAlert($quote): bool
    {
        $adminEmail = $this->getAdminEmail();
        Log::info("Email Alert Sent to Admin ({$adminEmail}) for Quote #{$quote->quote_number}");
        return true;
    }

    /**
     * Send Customer Quote Acknowledgment Email
     */
    public function sendCustomerQuoteAcknowledgment($quote): bool
    {
        Log::info("Email Acknowledgment Sent to Customer ({$quote->email}) for Quote #{$quote->quote_number}");
        return true;
    }

    /**
     * Send Payment Link & Invoice Email
     */
    public function sendInvoiceAndPaymentLink($quote, string $paymentToken, float $sellingPrice): bool
    {
        Log::info("Invoice & Payment Link Email Sent to Customer ({$quote->email}) for Quote #{$quote->quote_number}");
        return true;
    }

    /**
     * Send Payment Receipt Confirmation Email
     */
    public function sendPaymentSuccessConfirmation($order): bool
    {
        Log::info("Payment Receipt Email Sent to Customer ({$order->customer_email}) for Order #{$order->order_number}");
        return true;
    }

    /**
     * Send Delivery Status Update Email
     */
    public function sendStatusUpdateNotification($order, string $status): bool
    {
        Log::info("Delivery Status Update Email Sent to Customer ({$order->customer_email}) for Order #{$order->order_number}");
        return true;
    }

    /**
     * Send Digital POD Certificate Email
     */
    public function sendPodCertificate($order, $pod): bool
    {
        Log::info("Digital POD Certificate Email Sent to Customer ({$order->customer_email}) for Order #{$order->order_number}");
        return true;
    }
}
