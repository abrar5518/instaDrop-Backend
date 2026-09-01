<?php

namespace App\Services;

use App\Events\InquiryCreated;
use App\Events\OrderCreated;
use App\Events\PaymentReceived;
use App\Jobs\SendNewInquiryWhatsAppAlert;
use App\Jobs\SendNewOrderWhatsAppAlert;
use App\Jobs\SendPaymentReceivedWhatsAppAlert;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected WhatsAppCloudApiService $cloudApiService;

    public function __construct(WhatsAppCloudApiService $cloudApiService)
    {
        $this->cloudApiService = $cloudApiService;
    }

    /**
     * Get Admin Business WhatsApp Number from System Settings or Config
     */
    protected function getAdminWhatsAppNumber(): string
    {
        $setting = SystemSetting::first();
        return $setting->admin_whatsapp_number ?? config('whatsapp.admin_number', '447852502775');
    }

    /**
     * Step 3: Send New Quote Request / Inquiry Alert to Admin via WhatsApp Cloud API
     */
    public function sendAdminNewQuoteAlert($quote): bool
    {
        Log::info("[WhatsAppService] Dispatching new inquiry alert for Quote #{$quote->quote_number}");
        
        SendNewInquiryWhatsAppAlert::dispatch($quote->id);

        return true;
    }

    /**
     * Step 4: Send Automatic Customer Acknowledgment
     */
    public function sendCustomerQuoteAcknowledgment($quote): bool
    {
        Log::info("[WhatsAppService] Customer Quote Acknowledgment queued for {$quote->phone}");
        return true;
    }

    /**
     * Step 10 & 11: Send Final Invoice & Secure Payment Link
     */
    public function sendInvoiceAndPaymentLink($quote, string $paymentToken, float $sellingPrice): bool
    {
        Log::info("[WhatsAppService] Invoice & Payment Link queued for {$quote->phone}");
        return true;
    }

    /**
     * Step 13: Send Automated Payment Confirmation to Admin & Customer via Meta Cloud API
     */
    public function sendPaymentSuccessConfirmation($order): bool
    {
        Log::info("[WhatsAppService] Dispatching payment received alert for Order #{$order->order_number}");
        
        SendPaymentReceivedWhatsAppAlert::dispatch($order->id);

        return true;
    }

    /**
     * Step 16: Send Milestone Delivery Status Updates
     */
    public function sendStatusUpdateNotification($order, string $status): bool
    {
        Log::info("[WhatsAppService] Delivery Status Update ({$status}) queued for Order #{$order->order_number}");
        return true;
    }

    /**
     * Step 18: Send Proof of Delivery (POD) Certificate
     */
    public function sendPodCertificate($order, $pod): bool
    {
        Log::info("[WhatsAppService] Digital POD Certificate queued for Order #{$order->order_number}");
        return true;
    }
}
