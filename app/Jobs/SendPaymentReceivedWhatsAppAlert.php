<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\WhatsAppNotificationLog;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentReceivedWhatsAppAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $afterCommit = true;
    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct(public int|string $orderId)
    {
    }

    public function handle(WhatsAppCloudApiService $apiService): void
    {
        $order = Order::find($this->orderId);

        if (!$order) {
            Log::warning("[SendPaymentReceivedWhatsAppAlert] Order ID {$this->orderId} not found, skipping alert.");
            return;
        }

        $adminNumber = (string) config('whatsapp.admin_number', '447852502775');
        $recipient = WhatsAppCloudApiService::formatE164($adminNumber);
        $templateName = (string) config('whatsapp.payment_template', 'payment_received_admin_alert');
        $language = (string) config('whatsapp.template_language', 'en');

        if (WhatsAppNotificationLog::isAlreadySent('payment', (string) $order->id, $recipient)) {
            Log::info("[SendPaymentReceivedWhatsAppAlert] Order Payment ID {$order->id} alert already sent to {$recipient}, skipping.");
            return;
        }

        $logRecord = WhatsAppNotificationLog::firstOrCreate(
            [
                'notification_type' => 'payment',
                'source_record_id' => (string) $order->id,
                'recipient' => $recipient,
            ],
            [
                'template_name' => $templateName,
                'status' => 'pending',
                'attempt_count' => 0,
            ]
        );

        if ($logRecord->status === 'sent') {
            return;
        }

        $logRecord->increment('attempt_count');

        $formattedAmount = '£' . number_format((float) $order->total_amount, 2);

        $parameters = [
            (string) $order->order_number,
            (string) ($order->customer_name ?: 'Customer'),
            (string) $formattedAmount,
            (string) ($order->payment_method ?: 'Online Payment'),
            (string) ucfirst((string) ($order->payment_status ?: 'Completed')),
        ];

        $result = $apiService->sendTemplate(
            $recipient,
            $templateName,
            $parameters,
            $language
        );

        if ($result['success']) {
            $logRecord->update([
                'status' => 'sent',
                'meta_message_id' => $result['message_id'],
                'sent_at' => now(),
                'last_error' => null,
            ]);
        } else {
            $logRecord->update([
                'status' => 'failed',
                'last_error' => $result['error'],
            ]);

            Log::error("[SendPaymentReceivedWhatsAppAlert] Failed sending payment notification for Order #{$order->order_number}: {$result['error']}");
        }
    }
}
